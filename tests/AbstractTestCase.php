<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;
}
