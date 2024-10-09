<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Ipdata\IpdataService;
use App\Service\Ipdata\IpdataUnknownResult;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IpdataController extends AbstractController
{
    public function __construct(
        protected IpdataService $ipdataService
    ) {}

    #[Route('/api/ipdata/geolocation/{ip}', name: 'specific_ip_geolocation', methods: ['GET'])]
    #[OA\Get(
        path: '/api/ipdata/geolocation/{ip}',
        tags: ['Ipdata'],
        parameters: [
            new OA\Parameter(name: 'ip', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Geolocation successfully received',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'Correct Ip',
                            summary: 'Correct Ip',
                            value: [
                                'country_code' => 'RU',
                                'ip' => '91.218.114.206',
                                'city' => 'Moscow',
                                'region' => 'Moscow (City)',
                                'region_code' => 'MOW',
                                'country_name' => 'Russia',
                                'latitude' => 55.752220153809,
                                'longitude' => 37.615558624268,
                            ]
                        ),
                    ],
                    properties: [
                        new OA\Property(property: 'ip', type: 'string'),
                        new OA\Property(property: 'city', type: 'string'),
                        new OA\Property(property: 'region', type: 'string'),
                        new OA\Property(property: 'region_code', type: 'string'),
                        new OA\Property(property: 'country_name', type: 'string'),
                        new OA\Property(property: 'country_code', type: 'string'),
                        new OA\Property(property: 'latitude', type: 'number'),
                        new OA\Property(property: 'longitude', type: 'number'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function specificIpGeolocation(string $ip): Response
    {
        if (!(($result = $this->ipdataService->getGeolocation($ip)) instanceof IpdataUnknownResult)) {
            return $this->json($result);
        }
        throw new NotFoundHttpException('ip not recognized');
    }
}
