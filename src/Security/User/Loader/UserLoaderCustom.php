<?php

declare(strict_types=1);

namespace App\Security\User\Loader;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class UserLoaderCustom implements UserLoaderInterface
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function __invoke(string $identifier, array $roles = []): ?UserInterface
    {
        /** @var User|null $user */
        $user = $this->loadUserByIdentifier($identifier);
        if (!$user) {
            return null;
        }

        if ($roles) {
            $user->setRoles($roles);
        }

        return $user;
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->repository->loadUserByIdentifier($identifier);
    }
}
