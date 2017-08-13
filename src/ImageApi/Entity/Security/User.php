<?php

namespace VysokeSkoly\ImageApi\Entity\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    public const USERNAME_API = 'api-user';
    public const ROLE_API = 'ROLE_API';

    public function getRoles()
    {
        return [self::ROLE_API];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return self::USERNAME_API;
    }

    public function eraseCredentials()
    {
    }
}
