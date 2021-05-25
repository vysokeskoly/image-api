<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiKeyAuthenticatorGuard extends AbstractGuardAuthenticator
{
    public const API_KEY = 'apikey';

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request): bool
    {
        return $request->request->has(self::API_KEY) || $request->query->has(self::API_KEY);
    }

    public function getCredentials(Request $request): ?string
    {
        return $request->request->has(self::API_KEY)
            ? $request->request->get(self::API_KEY)
            : $request->query->get(self::API_KEY);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if ($userProvider instanceof ApiKeyUserProvider) {
            $username = $userProvider->getUsernameForApiKey($credentials);

            if (!$username) {
                throw new CustomUserMessageAuthenticationException(
                    sprintf('API Key "%s" does not exist.', $credentials)
                );
            }

            return $userProvider->loadUserByUsername($username);
        }

        throw new CustomUserMessageAuthenticationException(
            sprintf('Unexpected user provider given - %s.', get_class($userProvider))
        );
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?JsonResponse
    {
        return null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
