<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\AddEmailRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

final class AddEmailRequestTest extends MappedRequestTestCase
{
    public function testEmailCannotBeBlank(): void
    {
        $request = new AddEmailRequest('');

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertGreaterThanOrEqual(1, $errors->count());
    }

    #[DataProvider('emailOptionsProvider')]
    #[TestDox('Assert that $_dataName raises $expected violations')]
    public function testEmailIsValidated(string $email, int $expected): void
    {
        $request = new AddEmailRequest($email);

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertEquals($expected, $errors->count());
    }

    #[DataProvider('emailAvailableOptionsProvider')]
    #[TestDox('Assert that $_dataName raises $expected violations')]
    public function testEmailMustBeAvailable(string $email, int $expected): void
    {
        $request = new AddEmailRequest($email);

        $errors = $this->validator->validateProperty($request, 'email');

        $this->assertEquals($expected, $errors->count());
    }

    protected function requiredProperties(): array
    {
        return ['email'];
    }

    protected function requestClass(): string
    {
        return AddEmailRequest::class;
    }
}
