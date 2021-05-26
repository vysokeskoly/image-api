<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Entity\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    public const USERNAME_API = 'api-user';
    public const ROLE_API = 'ROLE_API';

    public function getRoles(): array
    {
        return [self::ROLE_API];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return self::USERNAME_API;
    }

    public function eraseCredentials(): void
    {
    }
}
