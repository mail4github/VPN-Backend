<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Concern\JsonResponseTrait;
use App\Service\CryptoService;
use GuzzleHttp\Exception\GuzzleException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

#[AsController]
#[OA\Tag('Coingate Integration')]
class CryptoController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * Получить рейт обмена криптовалют.
     *
     * @param Request       $request
     * @param CryptoService $cryptoService
     * @param string        $from
     * @param string        $to
     *
     * @throws GuzzleException
     *
     * @return JsonResponse
     */
    #[OA\Get(summary: 'Get current exchange rate for any two currencies')]
    #[OA\Parameter(
        name: 'from',
        description: 'From currency',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string'
        ),
        example: 'EUR'
    )]
    #[OA\Parameter(
        name: 'to',
        description: 'To currency',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string'
        ),
        example: 'BTC'
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid request parameters'
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            properties: [
                new OA\Property('rate', description: 'rate', type: 'number'),
            ]
        )
    )]
    #[Route('/api/crypto/rate/{from}/{to}', 'crypto_rate', methods: [Request::METHOD_GET])]
    public function getRate(
        CryptoService $cryptoService,
        string $from,
        string $to
    ): JsonResponse {
        $validator = Validation::createValidator();

        $constraint = new Assert\Collection([
            'from' => [new Assert\NotBlank(), new Assert\Type('string')],
            'to' => [new Assert\NotBlank(), new Assert\Type('string')],
        ]);

        $errors = $validator->validate([
            'from' => $from,
            'to' => $to,
        ], $constraint);

        if (\count($errors) > 0) {
            $errorArray = [];
            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $errorArray[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return new JsonResponse(['errors' => $errorArray], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'rate' => $cryptoService->getExchangeRate($from, $to),
        ]);
    }
}
