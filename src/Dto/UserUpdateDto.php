<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateDto
{
    #[Assert\Email(message: 'The value for \'email\' should be valid email')]
    public ?string $email = null;

    #[Assert\Regex(pattern: '/^[A-Za-z0-9]{3,128}+$/', message: 'The value for \'login\' must obey the regex pattern: {{ pattern }}')]
    public ?string $login = null;

    #[Assert\Length(min: 6, max: 128, minMessage: 'password too short', maxMessage: 'password too big')]
    public ?string $password = null;

    #[Assert\Regex(pattern: '/^data:image(.*)/i', message: 'The value for \'picture\' must obey the regex pattern: {{ pattern }}')]
    public ?string $picture = null;

    public function __construct(
        ?string $email = null,
        ?string $login = null,
        ?string $password = null,
        ?string $picture = null
    ) {
        $this->email = $email;
        $this->login = $login;
        $this->password = $password;
        $max_size_user_picture = 1024 * 1024 * 10;
        if (isset($_ENV['MAX_SIZE_USER_PICTURE'])) {
            $max_size_user_picture = $_ENV['MAX_SIZE_USER_PICTURE'];
        }

        if (null !== $picture && \mb_strlen($picture) > $max_size_user_picture) {
            throw new \Exception("Maximum picture size is: $max_size_user_picture bytes");
        }
        $this->picture = $picture;
    }
}
