<?php declare(strict_types=1);

namespace VysokeSkoly\Selenium;

use Assert\Assertion;
use Lmc\Steward\Test\AbstractTestCase;
use VysokeSkoly\Selenium\Component\ImageApiComponent;

/**
 * Default test case for team EDU
 */
abstract class AbstractVysokeSkolyTestCase extends AbstractTestCase
{
    public const ADMIN_URL = 'image-api.vysokeskoly.cz/';
    public const LOCAL_URL = '127.0.0.1:8080/';

    public string $baseUrl;
    protected ImageApiComponent $imageApi;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->baseUrl = 'http://' . self::ADMIN_URL;
    }

    /** @before */
    protected function initCommon(): void
    {
        $this->imageApi = new ImageApiComponent($this);
    }

    /** @before */
    protected function initCapabilities(): void
    {
        $capabilities = $this->wd->getCapabilities();
        Assertion::notNull($capabilities);
        $proxy = $capabilities->getCapability('proxy');

        if (empty($proxy)) {
            $this->debug('Set baseUrl to local url for no-proxy.');
            $this->baseUrl = 'http://' . self::LOCAL_URL;
        }
    }

    /**
     * @param int[] $expectedRange
     */
    protected function assertBetween(array $expectedRange, int $value): void
    {
        $min = min($expectedRange);
        $max = max($expectedRange);
        $message = sprintf('Failed asserting that %d is between %d and %d.', $value, $min, $max);

        $this->assertGreaterThanOrEqual($min, $value, $message);
        $this->assertLessThanOrEqual($max, $value, $message);
    }

    protected function getRandomIndex(int $count): int
    {
        return random_int(0, $count - 1);
    }

    protected function assertHasStringParts(array $expectedParts, string $actual): void
    {
        foreach ($expectedParts as $expectedPart) {
            $this->assertStringContainsString($expectedPart, $actual);
        }
    }
}
