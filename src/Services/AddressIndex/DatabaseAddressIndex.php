<?php

namespace BlockchainSdk\Services\AddressIndex;

use BlockchainSdk\Contracts\AddressIndexInterface;
use Illuminate\Support\Facades\DB;

class DatabaseAddressIndex implements AddressIndexInterface
{
    public function __construct(
        private string $table = 'blockchain_sdk_wallets',
        private string $addressColumn = 'address',
        private string $idColumn = 'id'
    ) {}

    public function sync(iterable $addresses): void
    {
        // No-op: queries are executed directly against the database table
    }

    public function contains(string $address): bool
    {
        if (empty($address)) {
            return false;
        }

        return DB::table($this->table)
            ->whereRaw('LOWER('.$this->addressColumn.') = ?', [strtolower(trim($address))])
            ->exists();
    }

    public function resolve(string $address): ?int
    {
        if (empty($address)) {
            return null;
        }

        $id = DB::table($this->table)
            ->whereRaw('LOWER('.$this->addressColumn.') = ?', [strtolower(trim($address))])
            ->value($this->idColumn);

        return $id !== null ? (int) $id : null;
    }
}
