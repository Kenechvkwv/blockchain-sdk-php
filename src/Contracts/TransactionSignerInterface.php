<?php

namespace BlockchainSdk\Contracts;

interface TransactionSignerInterface
{
    public function signTransaction(array $params): string;
}
