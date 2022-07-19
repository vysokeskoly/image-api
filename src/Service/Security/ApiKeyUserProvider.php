<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use VysokeSkoly\ImageApi\Entity\Security\User;

class ApiKeyUserProvider implements UserProviderInterface
{
    public function __construct(private string $apiKey)
    {
    }

    /** @deprecated todo remove on symfony 6 */
    public function loadUserByUsername(string $usernameOrApiKey): ?UserInterface
    {
        return $this->loadUserByIdentifier($usernameOrApiKey);
    }

    public function loadUserByIdentifier(string $usernameOrApiKey): ?UserInterface
    {
        $username = $this->getUsernameForApiKey($usernameOrApiKey) ?? $usernameOrApiKey;

        if ($username !== User::USERNAME_API) {
            throw new UserNotFoundException();
        }

        return new User();
    }

    public function getUsernameForApiKey(string $apiKey): ?string
    {
        return $apiKey === $this->apiKey
            ? User::USERNAME_API
            : null;
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
