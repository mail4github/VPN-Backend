<?php

//declare(strict_types=1);

namespace App\Dto;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;

class ProfileControllerDeactivateSessionsDto
{

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[A-Z]{2,2}+$/')]
    public readonly string $country;

    public function __construct(
        string $country

    ) {
        $this->country = $country;
    }
}
