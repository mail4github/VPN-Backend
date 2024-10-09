<?php

declare(strict_types=1);

namespace App\Exception;

class VerifyEmailException extends \Exception
{
    public function getReason(): string
    {
        return $this->message;
    }
}
