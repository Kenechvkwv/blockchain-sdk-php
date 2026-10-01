<?php

namespace BlockchainSdk\Services;

use BlockchainSdk\Contracts\NetworkDriverInterface;
use BlockchainSdk\DTOs\SweepResult;
use RuntimeException;

class SweepExecutor
{
    /**
     * Execute an end-to-end wallet sweep with fee accounting and gas sponsorship orchestration.
     */
    public function execute(
        NetworkDriverInterface $driver,
        string $network,
        string $fromAddress,
        string $fromPrivateKey,
        string $toVaultAddress,
        ?string $tokenContract = null,
        ?string $amount = null,
        ?string $masterGasPrivateKey = null,
        bool $isEvmNetwork = true
    ): SweepResult {
        $sponsorshipTxHash = null;
        $sponsorshipFee = '0';

        // 1. Non-EVM tokens with gas sponsorship (e.g. Solana, TRON)
        if ($tokenContract && $masterGasPrivateKey && ! $isEvmNetwork) {
            $transaction = $driver->sweepTokenWithGasSponsorship(
                $fromPrivateKey,
                $masterGasPrivateKey,
                $toVaultAddress,
                $tokenContract,
                $amount
            );

            if (! $transaction->success || ! $transaction->txHash) {
                return new SweepResult(
                    transaction: $transaction,
                    sweepFeeSpent: '0',
                    sponsorshipFeeSpent: '0',
                    sponsorshipTxHash: null,
                    totalFeeSpent: '0'
                );
            }

            $sweepFee = $this->resolveFee($driver, $transaction->txHash);

            return new SweepResult(
                transaction: $transaction,
                sweepFeeSpent: $sweepFee,
                sponsorshipFeeSpent: '0',
                sponsorshipTxHash: null,
                totalFeeSpent: $sweepFee
            );
        }

        // 2. EVM tokens: 2-step fueling sub-wallet first
        if ($tokenContract && $masterGasPrivateKey && $isEvmNetwork) {
            $fuelResult = $driver->fuelSubWallet($masterGasPrivateKey, $fromAddress, $tokenContract);

            if (! $fuelResult->success && empty($fuelResult->txHash)) {
                throw new RuntimeException('Failed to fund sub-wallet for gas: '.($fuelResult->errorMessage ?? 'Unknown error.'));
            }

            if ($fuelResult->txHash) {
                $sponsorshipTxHash = $fuelResult->txHash;
                // Wait for fuel receipt before executing token sweep
                $receipt = $driver->waitForTransactionReceipt($fuelResult->txHash, 30);
                $sponsorshipFee = $receipt?->feeSpent() ?? $this->resolveFee($driver, $fuelResult->txHash);
            }
        }

        // 3. Execute sweep
        $transaction = $driver->sweep($fromPrivateKey, $toVaultAddress, $tokenContract, $amount);

        if (! $transaction->success || ! $transaction->txHash) {
            return new SweepResult(
                transaction: $transaction,
                sweepFeeSpent: '0',
                sponsorshipFeeSpent: $sponsorshipFee,
                sponsorshipTxHash: $sponsorshipTxHash,
                totalFeeSpent: $sponsorshipFee
            );
        }

        // 4. Resolve on-chain sweep fee
        $sweepFee = $this->resolveFee($driver, $transaction->txHash);

        return new SweepResult(
            transaction: $transaction,
            sweepFeeSpent: $sweepFee,
            sponsorshipFeeSpent: $sponsorshipFee,
            sponsorshipTxHash: $sponsorshipTxHash,
            totalFeeSpent: bcadd($sponsorshipFee, $sweepFee, 18)
        );
    }

    private function resolveFee(NetworkDriverInterface $driver, string $txHash): string
    {
        try {
            $receipt = $driver->getTransactionReceipt($txHash) ?? $driver->waitForTransactionReceipt($txHash, 15);
            if ($receipt) {
                return $receipt->feeSpent();
            }
        } catch (\Throwable) {
            // Receipt lookup fallback
        }

        return '0';
    }
}
