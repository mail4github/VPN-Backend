<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\RegistrationRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

final class RegistrationRequestTest extends MappedRequestTestCase
{
    public function testLoginCannotBeBlank(): void
    {
        $request = new RegistrationRequest(login: '', password: 'Strong123');

        $errors = $this->validator->validate($request);

        $this->assertGreaterThanOrEqual(1, $errors->count());
    }

    #[DataProvider('loginLengthOptionsProvider')]
    #[TestDox('Login that is $_dataName raises $expected violations')]
    public function testLoginLengthIsValidated(string $login, int $expected): void
    {
        $request = new RegistrationRequest(login: $login, password: 'Strong123');

        $errors = $this->validator->validate($request);

        $this->assertEquals($expected,  $errors->count());
    }

    #[DataProvider('loginUniqueOptionsProvider')]
    #[TestDox('Assert $_dataName raises $expected violations')]
    public function testLoginMustBeUnique(string $login, int $expected): void
    {
        $request = new RegistrationRequest(login: $login, password: 'Strong123');

        $errors = $this->validator->validate($request);

        $this->assertEquals($expected, $errors->count());
    }

    public function testPasswordCannotBeBlank(): void
    {
        $request = new RegistrationRequest(login: 'unique', password: '');

        $errors = $this->validator->validateProperty($request, 'password');

        $this->assertGreaterThanOrEqual(1, $errors->count());
    }

    #[DataProvider('passwordLengthOptionsProvider')]
    #[TestDox('Password that is $_dataName raises $expected violations')]
    public function testPasswordLengthIsValidated(string $password, int $expected): void
    {
        $request = new RegistrationRequest(login: 'unique', password: $password);

        $errors = $this->validator->validateProperty($request, 'password');

        $this->assertEquals($expected, $errors->count());
    }

    #[DataProvider('passwordRegexOptionsProvider')]
    #[TestDox('Password $_dataName raises $expected violations')]
    public function testPasswordStrengthIsValidatedByRegex(string $password, int $expected): void
    {
        $request = new RegistrationRequest(login: 'unique', password: $password);

        $errors = $this->validator->validateProperty($request, 'password');

        $this->assertEquals($expected, $errors->count());
    }

    #[DataProvider('emailOptionsProvider')]
    #[TestDox('Assert that $_dataName raises $expected violations')]
    public function testEmailIsValidated(string $email, int $expected): void
    {
        $request = new RegistrationRequest(login: 'unique', password: 'Strong123', email: $email);

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertEquals($expected, $errors->count(), "$email did not pass validation as expected");
    }

    #[DataProvider('emailAvailableOptionsProvider')]
    #[TestDox('Assert that $_dataName raises $expected violations')]
    public function testEmailMustBeAvailable(string $email, int $expected): void
    {
        $request = new RegistrationRequest(login: 'unique', password: 'Strong123', email: $email);

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertEquals($expected, $errors->count(), "$email did not pass validation as expected");
    }

    #[DataProvider('enableTwoFactorFlagOptionsProvider')]
    #[TestDox('$_dataName for enable2fa flag raises $expected violations')]
    public function testEnable2faFlagIsValidated(mixed $value, int $expected): void
    {
        $request = new RegistrationRequest(login: 'unique', password: 'Strong123', enable2fa: $value);

        $errors = $this->validator->validateProperty($request, 'enable2fa');

        $this->assertEquals($expected, $errors->count(), "$value did not pass validation as expected");
    }

    protected function requestClass(): string
    {
        return RegistrationRequest::class;
    }

    protected function requiredProperties(): array
    {
        return [
            'login',
            'password',
        ];
    }
}
