<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Environment;

use Symfony\Component\HttpFoundation\Request;
use VysokeSkoly\UtilsBundle\Environment\EnvironmentInterface;
use VysokeSkoly\UtilsBundle\Service\DebugLevel;

class VysokeSkoly implements EnvironmentInterface
{
    public const COOKIE_VYSOKE_SKOLY_DBG = 'vysoke_skoly_dbg';

    private EnvironmentInterface $env;

    public function __construct(EnvironmentInterface $lmc)
    {
        $this->env = $lmc;
    }

    /**
     * Check if request comes from LMC internal network (including VPN and vagrant).
     */
    public function isInternalRequest(Request $request): bool
    {
        return $this->env->isInternalRequest($request);
    }

    /**
     * Try to detect environment from /etc/vysokeskoly.xml. If name of environment equals to prod
     * than return false otherwise always return true.
     */
    public function isDevEnvironment(): bool
    {
        return $this->env->isDevEnvironment();
    }

    /**
     * Return name of environment as lowercase string (i.e. 'dev', 'prod' etc.).
     */
    public function getEnvironment(): string
    {
        return $this->stripNumbers($this->env->getEnvironment());
    }

    private function stripNumbers(string $string): string
    {
        return (string) preg_replace('/[0-9]+/', '', $string);
    }

    /**
     * Return symfony environment which is PROD if LMC environment is 'deploy' or 'prod' and debug is off (i.e. 0).
     * Otherwise symfony environment is DEV.
     */
    public function getSymfonyEnvironment(DebugLevel $debugLevel): string
    {
        return $this->env->getSymfonyEnvironment($debugLevel);
    }

    public function setDebugCookie(DebugLevel $debugLevel): void
    {
        $cookieLifeTime = $this->isDevEnvironment() ? 0 : time() + 60 * 60;

        setcookie(self::COOKIE_VYSOKE_SKOLY_DBG, (string) $debugLevel->getDebugLevel(), $cookieLifeTime, '/');
    }
}
