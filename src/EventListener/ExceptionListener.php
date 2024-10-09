<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        // Get Exception.
        $exception = $event->getThrowable();

        // Check if HttpException, then code is status_code.
        if ($exception instanceof HttpException) {
            $code = (int) $exception->getStatusCode();
        } else {
            $code = $exception->getCode();
        }

        // Check if previous - Validation Error - compact errors.
        if ($exception->getPrevious() instanceof ValidationFailedException) {
            $errors = [];
            /** @var ValidationFailedException $validationException */
            $validationException = $exception->getPrevious();
            foreach ($validationException->getViolations() as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
        } else {
            $errors = explode("\n", $exception->getMessage());
        }

        // Проверка кода ошибки HTTP.
        if (isset(JsonResponse::$statusTexts[$code])) {
            $message = JsonResponse::$statusTexts[$code] ?? null;
        } else {
            $message = 'Undefined Error';
            $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        $responseData = [
            'status' => $code,
            'message' => $message,
            'errors' => $errors,
        ];

        // Добавить трейс для ошибок уровня 500.
        if (JsonResponse::HTTP_INTERNAL_SERVER_ERROR === $code) {
            $responseData['trace'] = $exception->getTrace();
        }

        // Return JSON.
        $event->setResponse(
            new JsonResponse($responseData, $code)
        );
    }
}
