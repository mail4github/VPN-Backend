<?php

declare(strict_types=1);

namespace App\Service\Ipdata;

interface IpdataResultInterface
{
    public const DEFAULT_CODE = 'n/a';

    public function getCountryCode(): string;
}
