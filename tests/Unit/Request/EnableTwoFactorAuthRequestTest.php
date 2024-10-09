<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\EnableTwoFactorAuthRequest;

final class EnableTwoFactorAuthRequestTest extends MappedRequestTestCase
{
    public function testAuthCodeCannotBeBlank(): void
    {
        $request = new EnableTwoFactorAuthRequest('');

        $errors = $this->validator->validateProperty($request, '_auth_code');

        $this->assertGreaterThan(0, $errors->count());
    }

    protected function requiredProperties(): array
    {
        return ['_auth_code'];
    }

    protected function requestClass(): string
    {
        return EnableTwoFactorAuthRequest::class;
    }
}
