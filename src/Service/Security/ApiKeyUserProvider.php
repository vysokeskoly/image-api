<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use VysokeSkoly\ImageApi\Entity\Security\User;

class ApiKeyUserProvider implements UserProviderInterface
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getUsernameForApiKey(string $apiKey): ?string
    {
        return $apiKey === $this->apiKey
            ? User::USERNAME_API
            : null;
    }

    public function loadUserByUsername(string $username): ?UserInterface
    {
        if ($username !== User::USERNAME_API) {
            throw new UsernameNotFoundException();
        }

        return new User();
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }
}
