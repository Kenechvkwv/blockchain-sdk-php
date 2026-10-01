<?php

namespace BlockchainSdk\Services\AddressIndex;

use BlockchainSdk\Contracts\AddressIndexInterface;
use Illuminate\Support\Facades\Redis;

class AddressIndexFactory
{
    /**
     * Build an AddressIndex instance according to environment availability and configuration.
     *
     * @param  string  $network  Blockchain network identifier
     * @param array{
     *     strategy?: 'auto'|'array'|'database'|'redis',
     *     table?: string,
     *     address_column?: string,
     *     id_column?: string,
     *     threshold?: int,
     * } $config Configuration options
     */
    public static function make(string $network, array $config = []): AddressIndexInterface
    {
        $strategy = strtolower($config['strategy'] ?? 'auto');

        if ($strategy === 'redis' || ($strategy === 'auto' && self::redisAvailable())) {
            return new RedisAddressIndex($network);
        }

        if ($strategy === 'database') {
            return new DatabaseAddressIndex(
                table: $config['table'] ?? 'blockchain_sdk_wallets',
                addressColumn: $config['address_column'] ?? 'address',
                idColumn: $config['id_column'] ?? 'id'
            );
        }

        // Default: universal in-memory array strategy (works everywhere including shared hosting)
        return new ArrayAddressIndex;
    }

    /**
     * Check if Redis is configured and reachable without throwing exceptions.
     */
    public static function redisAvailable(): bool
    {
        try {
            if (! class_exists(Redis::class)) {
                return false;
            }

            Redis::connection()->ping();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
