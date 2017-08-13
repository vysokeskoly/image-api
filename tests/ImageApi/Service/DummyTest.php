<?php

namespace VysokeSkoly\Tests\ImageApi\Service;

use VysokeSkoly\ImageApi\Service\Dummy;
use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    public function testShouldFooBar()
    {
        $dummy = new Dummy();

        $this->assertSame('bar', $dummy->foo());
    }
}
