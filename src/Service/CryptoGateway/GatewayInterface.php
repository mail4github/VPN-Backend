<?php

declare(strict_types=1);

namespace App\Service\CryptoGateway;

interface GatewayInterface
{
    public function getExchangeRate(string $currencyFrom, string $currencyTo): ?float;
}
