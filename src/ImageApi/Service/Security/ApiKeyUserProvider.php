<?php

namespace VysokeSkoly\ImageApi\Service\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use VysokeSkoly\ImageApi\Entity\Security\User;

class ApiKeyUserProvider implements UserProviderInterface
{
    /** @var string */
    private $apiKey;

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

    public function loadUserByUsername($username): ?UserInterface
    {
        if ($username !== User::USERNAME_API) {
            throw new UsernameNotFoundException();
        }

        return new User();
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class): bool
    {
        return $class === User::class;
    }
}
