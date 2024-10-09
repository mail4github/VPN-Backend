<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\SendResetPasswordCodeRequest;

class SendResetPasswordCodeRequestTest extends MappedRequestTestCase
{
    public function testLoginCannotBeBlank(): void
    {
        $request = new SendResetPasswordCodeRequest('');

        $errors = $this->validator->validateProperty($request, 'login');

        $this->assertGreaterThan(0, $errors->count());
    }

    protected function requiredProperties(): array
    {
        return ['login'];
    }

    protected function requestClass(): string
    {
        return SendResetPasswordCodeRequest::class;
    }
}
