<?php

namespace BlockchainSdk\Services\AddressIndex;

use BlockchainSdk\Contracts\AddressIndexInterface;
use Illuminate\Support\Facades\Redis;

class RedisAddressIndex implements AddressIndexInterface
{
    private string $key;

    public function __construct(string $network)
    {
        $this->key = 'blockchainsdk:addresses:'.strtolower($network);
    }

    public function sync(iterable $addresses): void
    {
        $redis = Redis::connection();
        $redis->del($this->key);

        $batch = [];
        foreach ($addresses as $walletId => $address) {
            if (is_string($address) && ! empty($address)) {
                $batch[strtolower(trim($address))] = (string) $walletId;

                if (count($batch) >= 1000) {
                    $redis->hMSet($this->key, $batch);
                    $batch = [];
                }
            }
        }

        if (! empty($batch)) {
            $redis->hMSet($this->key, $batch);
        }
    }

    public function contains(string $address): bool
    {
        if (empty($address)) {
            return false;
        }

        return (bool) Redis::connection()->hExists($this->key, strtolower(trim($address)));
    }

    public function resolve(string $address): ?int
    {
        if (empty($address)) {
            return null;
        }

        $id = Redis::connection()->hGet($this->key, strtolower(trim($address)));

        return ($id !== false && $id !== null) ? (int) $id : null;
    }
}
