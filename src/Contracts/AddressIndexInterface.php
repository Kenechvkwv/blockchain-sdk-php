<?php

namespace BlockchainSdk\Contracts;

interface AddressIndexInterface
{
    /**
     * Synchronize the index with the current set of watched addresses.
     *
     * @param  iterable<int|string, string>  $addresses  Map of wallet ID/key to address string, or list of addresses.
     */
    public function sync(iterable $addresses): void;

    /**
     * Check if an address exists within the watched address index (case-insensitive).
     */
    public function contains(string $address): bool;

    /**
     * Resolve the internal wallet model ID or key associated with the address.
     */
    public function resolve(string $address): ?int;
}
