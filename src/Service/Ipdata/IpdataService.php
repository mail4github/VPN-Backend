<?php

declare(strict_types=1);

namespace App\Service\Ipdata;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Сервис для взаимодействия с IPData.
 *
 * @see https://ipdata.co/
 */
class IpdataService
{
    protected static ?string $apiBaseUrl = null;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected SerializerInterface $serializer,
        protected ParameterBagInterface $params,
    ) {
        static::$apiBaseUrl = $this->params->get('ipdata.api_base_url');
    }

    /**
     * Получить данные геолокации по IP.
     *
     * @param string $ip
     *
     * @return IpdataResultInterface
     */
    public function getGeolocation(string $ip): IpdataResultInterface
    {
        try {
            $url = str_replace('%ip', $ip, static::$apiBaseUrl);
            $response = $this->httpClient->request(Request::METHOD_GET, $url);
            $result = $response->getContent();

            return $this->serializer->deserialize($result, IpdataResult::class, 'json');
        } catch (\Throwable $e) {
            return new IpdataUnknownResult();
        }
    }
}
