<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\VerifyEmailRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

class VerifyEmailRequestTest extends MappedRequestTestCase
{
    public function testEmailCannotBeBlank(): void
    {
        $request = new VerifyEmailRequest('', '1234');

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertGreaterThanOrEqual(1, $errors->count());
    }

    #[DataProvider('emailOptionsProvider')]
    #[TestDox('Assert that $_dataName raises $expected violations')]
    public function testEmailIsValidated(string $email, int $expected): void
    {
        $request = new VerifyEmailRequest($email, '1234');

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertEquals($expected, $errors->count());
    }

    #[DataProvider('emailNotVerifiedOptionsProvider')]
    #[TestDox('Assert that $_dataName raises $expected violations')]
    public function testEmailMustBeNotVerified(string $email, int $expected): void
    {
        $request = new VerifyEmailRequest($email, '1234');

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertEquals($expected, $errors->count());
    }

    public function testCodeCannotBeBlank(): void
    {
        $request = new VerifyEmailRequest('email@email.org', '');

        $errors = $this->validator->validateProperty($request, 'code');

        $this->assertGreaterThan(0, $errors->count());
    }

    #[DataProvider('verificationCodeOptionsProvider')]
    #[TestDox('Code with $_dataName raises $expected violations')]
    public function testCodeLengthIsValidated(string $code, int $expected): void
    {
        $request = new VerifyEmailRequest('email@email.org', $code);

        $errors = $this->validator->validateProperty($request, 'code');

        $this->assertEquals($expected, $errors->count());
    }

    protected function requiredProperties(): array
    {
        return [
            'email',
            'code',
        ];
    }

    protected function requestClass(): string
    {
        return VerifyEmailRequest::class;
    }
}
