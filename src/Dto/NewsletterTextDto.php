<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class NewsletterTextDto
{
    /**
     * @param string $text
     * @param string $subject
     * @param string $recipient
     */
    public function __construct(
        #[Assert\NotBlank]
        public string $text,
        #[Assert\NotBlank]
        public string $subject,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $recipient,
    ) {}
}
