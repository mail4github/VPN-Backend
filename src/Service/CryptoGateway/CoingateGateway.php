<?php

declare(strict_types=1);

namespace App\Service\CryptoGateway;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CoingateGateway implements GatewayInterface
{
    private Client $httpClient;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiBaseUrl = 'https://api-sandbox.coingate.com/v2/'
    ) {
        $this->httpClient = new Client([
            'base_uri' => $this->apiBaseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-CoinGate-API-Key' => $this->apiKey,
            ],
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getExchangeRate(string $currencyFrom, string $currencyTo): ?float
    {
        $response = $this->httpClient->get("rates/merchant/{$currencyFrom}/{$currencyTo}");

        return json_decode($response->getBody()->getContents(), true);
    }
}
