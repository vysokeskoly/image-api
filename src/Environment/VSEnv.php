<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Environment;

use VysokeSkoly\ImageApi\ValueObject\ClientIp;
use function Safe\file_get_contents;
use Symfony\Component\HttpFoundation\Request;
use VysokeSkoly\UtilsBundle\Environment\EnvironmentInterface;
use VysokeSkoly\UtilsBundle\Service\DebugLevel;
use VysokeSkoly\UtilsBundle\Service\StringUtils;
use VysokeSkoly\UtilsBundle\Service\XmlHelper;

class VSEnv implements EnvironmentInterface
{
    public const VS_ENVIRONMENT_PROD = 'prod';
    public const VS_ENVIRONMENT_DEV = 'devel';

    public const COOKIE_KEY_DEBUG = 'debug';
    public const SECRET_KEY = 'e6aaa1c3-01d2-4324-967f-a5aa52538e42';

    public const VPN_IP = '172.27.128.25';
    public const INTERNAL_IP_PREFIX = '10.';

    public const VAGRANT_IP_PREFIX = '192.168.100.';

    public const RUN_IN_DOCKER_FILE = '/vysokeskoly-in-docker';

    public const UNKNOWN_ENV = 'unknown';
    /**
     * LMC environment names in which symfony app is in its PROD mode.
     *
     * @var array
     */
    public const SYMFONY_PROD_ENVIRONMENTS = [
        self::PROD_ENV,
    ];

    /**
     * One of DEVX, DEVEL, DEPLOY, PROD.
     */
    private ?string $environment = null;

    /**
     * Absolute path to vysokeskoly.xml configuration file (defaults to /etc/vysokeskoly.xml).
     */
    private string $lmcEnvPath;

    public function __construct(string $lmcEnvPath = '/etc/vysokeskoly.xml')
    {
        $this->lmcEnvPath = $lmcEnvPath;
    }

    public function isInternalRequest(Request $request): bool
    {
        $httpXForwardedFor = ClientIp::parseClientIpFromXForwardedFor(
            $request->server->get('HTTP_X_FORWARDED_FOR', '')
        )->getValue();
        // on vagrant environment there is no proxy so HTTP_X_FORWARDED_FOR is not set => we must check REMOTE_ADDR
        $remoteAddr = $request->server->get('REMOTE_ADDR', '');

        if ($httpXForwardedFor === self::VPN_IP
            || StringUtils::startsWith($httpXForwardedFor, self::INTERNAL_IP_PREFIX)
            || StringUtils::startsWith($remoteAddr, self::VAGRANT_IP_PREFIX)
            || is_file(self::RUN_IN_DOCKER_FILE)
        ) {
            return true;
        }

        return $this->hasSecretKey($request);
    }

    private function hasSecretKey(Request $request): bool
    {
        return $request->query->get(self::COOKIE_KEY_DEBUG, '') === self::SECRET_KEY
            || $request->cookies->get(self::COOKIE_KEY_DEBUG, '') === self::SECRET_KEY;
    }

    public function isDevEnvironment(): bool
    {
        return ($this->getEnvironment() !== self::PROD_ENV);
    }

    public function isProdEnvironment(): bool
    {
        return ($this->getEnvironment() === self::PROD_ENV);
    }

    public function getEnvironment(): string
    {
        if ($this->environment === null) {
            $environment = self::UNKNOWN_ENV;

            if (file_exists($this->lmcEnvPath)) {
                try {
                    $lmcEnvXml = XmlHelper::stringToXml(file_get_contents($this->lmcEnvPath));
                    $environment = (string) ($lmcEnvXml->attributes()->name ?? $environment);
                } catch (\Throwable $e) {
                    return self::UNKNOWN_ENV;
                }
            }

            // don't cache unknown environment
            if ($environment === self::UNKNOWN_ENV) {
                return $environment;
            }

            $this->environment = mb_strtolower($environment);
        }

        return $this->environment;
    }

    public function getSymfonyEnvironment(DebugLevel $debugLevel): string
    {
        $isSymfonyProdEnvironment = in_array($this->getEnvironment(), self::SYMFONY_PROD_ENVIRONMENTS, true);

        return ($isSymfonyProdEnvironment && !$debugLevel->isDebug()) ? self::SYMFONY_PROD_ENV : self::SYMFONY_DEV_ENV;
    }
}
