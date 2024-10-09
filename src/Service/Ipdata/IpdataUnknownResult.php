<?php

declare(strict_types=1);

namespace App\Service\Ipdata;

class IpdataUnknownResult implements IpdataResultInterface
{
    public function getCountryCode(): string
    {
        return IpdataResultInterface::DEFAULT_CODE;
    }
}
