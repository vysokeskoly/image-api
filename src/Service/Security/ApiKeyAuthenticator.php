<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Service\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class ApiKeyAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
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

    public function authenticate(Request $request): Passport
    {
        $apiKey = $this->getCredentials($request);

        return new Passport(
            new UserBadge($apiKey),
            new CustomCredentials(
                fn ($credentials) => !empty($credentials),
                $apiKey,
            ),
        );
    }

    public function getCredentials(Request $request): string
    {
        $apiKey = $request->request->has(self::API_KEY)
            ? $request->request->get(self::API_KEY)
            : $request->query->get(self::API_KEY);

        return (string) $apiKey;
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
}
