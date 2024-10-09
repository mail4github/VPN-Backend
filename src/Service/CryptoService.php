<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\CryptoGateway\CoingateGateway;
use GuzzleHttp\Exception\GuzzleException;

class CryptoService
{
    public function __construct(
        private readonly CoingateGateway $gateway,
    ) {}

    /**
     * @throws GuzzleException
     */
    public function getExchangeRate(string $currencyFrom, string $currencyTo): ?float
    {
        return $this->gateway->getExchangeRate($currencyFrom, $currencyTo);
    }
}
