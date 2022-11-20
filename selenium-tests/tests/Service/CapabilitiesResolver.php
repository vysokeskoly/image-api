<?php declare(strict_types=1);

namespace VysokeSkoly\Selenium\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Lmc\Steward\ConfigProvider;
use Lmc\Steward\Selenium\CustomCapabilitiesResolverInterface;
use Lmc\Steward\Test\AbstractTestCase;

class CapabilitiesResolver implements CustomCapabilitiesResolverInterface
{
    private const ENV_PROD = ['prod', 'vs-prod'];
    private const ENV_DEV = ['dev', 'vs-dev'];

    public function __construct(private ConfigProvider $config)
    {
    }

    public function resolveDesiredCapabilities(
        AbstractTestCase $test,
        DesiredCapabilities $capabilities,
    ): DesiredCapabilities {
        if (in_array($this->config->env, self::ENV_PROD, true)) {
            $capabilities->setCapability(
                WebDriverCapabilityType::PROXY,
                [
                    'proxyType' => 'manual',
                    'httpProxy' => 'proxy-1.prod:8118',
                    'sslProxy' => 'proxy-1.prod:8118',
                    'noProxy' => 'localhost',
                ],
            );
        } elseif (in_array($this->config->env, self::ENV_DEV, true)) {
            $capabilities->setCapability(
                WebDriverCapabilityType::PROXY,
                [
                    'proxyType' => 'manual',
                    'httpProxy' => 'proxy-1.devel:8118',
                    'sslProxy' => 'proxy-1.devel:8118',
                    'noProxy' => 'localhost',
                ],
            );
        }

        return $capabilities;
    }

    public function resolveRequiredCapabilities(
        AbstractTestCase $test,
        DesiredCapabilities $capabilities,
    ): DesiredCapabilities {
        return $capabilities;
    }
}
