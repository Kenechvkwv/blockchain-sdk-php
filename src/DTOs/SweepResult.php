<?php

namespace BlockchainSdk\DTOs;

class SweepResult
{
    public function __construct(
        public readonly TransactionResult $transaction,
        public readonly string $sweepFeeSpent = '0',
        public readonly string $sponsorshipFeeSpent = '0',
        public readonly ?string $sponsorshipTxHash = null,
        public readonly string $totalFeeSpent = '0'
    ) {}

    public function succeeded(): bool
    {
        return $this->transaction->success;
    }

    public function txHash(): ?string
    {
        return $this->transaction->txHash;
    }
}
