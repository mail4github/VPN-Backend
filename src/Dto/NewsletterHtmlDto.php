<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class NewsletterHtmlDto
{
    /**
     * @param string                     $templateName
     * @param string                     $subject
     * @param string                     $recipient
     * @param array<string, string>|null $params
     */
    public function __construct(
        #[Assert\NotBlank]
        public string $templateName,
        #[Assert\NotBlank]
        public string $subject,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $recipient,
        public ?array $params
    ) {
        $this->params = [];
    }

    /**
     * @return array<string, string>
     */
    public function getParams(): array
    {
        if (null === $this->params) {
            $this->params = [];
        }

        return $this->params;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addParam(string $key, string $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }
}
