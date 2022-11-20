<?php declare(strict_types=1);

namespace VysokeSkoly\Selenium;

/**
 * @group prod-safe
 */
class IndexTest extends AbstractVysokeSkolyTestCase
{
    public function testShouldGetAppName(): void
    {
        $this->imageApi->goToIndex();
        $content = $this->imageApi->getJsonContent();

        $this->assertSame(
            [
                'app' => 'VysokeSkoly/ImageApi',
            ],
            $content,
        );
    }
}
