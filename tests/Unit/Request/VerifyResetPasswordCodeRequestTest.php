<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\VerifyResetPasswordCodeRequest;

class VerifyResetPasswordCodeRequestTest extends MappedRequestTestCase
{
    public function testLoginCannotBeBlank(): void
    {
        $request = new VerifyResetPasswordCodeRequest('', '1234');

        $errors = $this->validator->validateProperty($request, 'login');

        $this->assertGreaterThan(0, $errors->count());
    }

    public function testCodeCannotBeBlank(): void
    {
        $request = new VerifyResetPasswordCodeRequest('username', '');

        $errors = $this->validator->validateProperty($request, 'code');

        $this->assertGreaterThan(0, $errors->count());
    }

    protected function requiredProperties(): array
    {
        return [
            'login',
            'code',
        ];
    }

    protected function requestClass(): string
    {
        return VerifyResetPasswordCodeRequest::class;
    }
}
