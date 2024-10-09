<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\ResetPasswordRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

class ResetPasswordRequestTest extends MappedRequestTestCase
{
    public function testTokenCannotBeBlank(): void
    {
        $request = new ResetPasswordRequest('', 'Strong123');

        $errors = $this->validator->validateProperty($request, 'token');

        $this->assertGreaterThan(0, $errors->count());
    }

    public function testPasswordCannotBeBlank(): void
    {
        $request = new ResetPasswordRequest('qwerty123', '');

        $errors = $this->validator->validateProperty($request, 'password');

        $this->assertGreaterThan(0, $errors->count());
    }

    #[DataProvider('passwordLengthOptionsProvider')]
    #[TestDox('Password that is $_dataName raises $expected violations')]
    public function testPasswordLengthIsValidated(string $password, int $expected): void
    {
        $request = new ResetPasswordRequest('qwerty123', $password);

        $errors = $this->validator->validateProperty($request, 'password');

        $this->assertEquals($expected, $errors->count());
    }

    #[DataProvider('passwordRegexOptionsProvider')]
    #[TestDox('Password $_dataName raises $expected violations')]
    public function testPasswordStrengthIsValidatedByRegex(string $password, int $expected): void
    {
        $request = new ResetPasswordRequest('qwerty123', $password);

        $errors = $this->validator->validateProperty($request, 'password');

        $this->assertEquals($expected, $errors->count());
    }

    protected function requiredProperties(): array
    {
        return [
            'token',
            'password',
        ];
    }

    protected function requestClass(): string
    {
        return ResetPasswordRequest::class;
    }
}
