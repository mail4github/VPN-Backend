<?php

declare(strict_types=1);

namespace App\Controller\Concern;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait JsonResponseTrait
{
    private const MESSAGE = 'Missing credentials';

    private function unauthorized(string $message = self::MESSAGE): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    private function error(string $message, int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse([
            'error' => $message,
        ], $code);
    }

    private function success(string $message): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
        ]);
    }
}
