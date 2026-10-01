<?php

namespace BlockchainSdk\Services\AddressIndex;

use BlockchainSdk\Contracts\AddressIndexInterface;

class ArrayAddressIndex implements AddressIndexInterface
{
    /**
     * @var array<string, int> Lowercase address => wallet ID
     */
    private array $map = [];

    public function sync(iterable $addresses): void
    {
        $this->map = [];
        foreach ($addresses as $walletId => $address) {
            if (is_string($address) && ! empty($address)) {
                $this->map[strtolower(trim($address))] = is_numeric($walletId) ? (int) $walletId : 0;
            }
        }
    }

    public function contains(string $address): bool
    {
        return isset($this->map[strtolower(trim($address))]);
    }

    public function resolve(string $address): ?int
    {
        return $this->map[strtolower(trim($address))] ?? null;
    }
}
