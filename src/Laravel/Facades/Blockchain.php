<?php

namespace BlockchainSdk\Laravel\Facades;

use BlockchainSdk\BlockchainManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \BlockchainSdk\Contracts\NetworkDriverInterface driver(string|null $network = null)
 * @method static array getSupportedTokens(string $network, bool $onlyEnabled = false)
 * @method static array|null findToken(string $network, string $symbolOrContract, bool $onlyEnabled = false)
 * @method static bool isTokenEnabled(string $network, string $symbolOrContract)
 *
 * @see BlockchainManager
 */
class Blockchain extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'blockchainsdk';
    }
}
