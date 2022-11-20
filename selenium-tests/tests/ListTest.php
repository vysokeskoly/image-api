<?php declare(strict_types=1);

namespace VysokeSkoly\Selenium;

/**
 * @group prod-safe
 */
class ListTest extends AbstractVysokeSkolyTestCase
{
    public function testShouldRequireAuthentication(): void
    {
        $this->imageApi->goToList();
        $content = $this->imageApi->getJsonContent();

        $this->assertSame(
            [
                'message' => 'Authentication Required',
            ],
            $content,
        );
    }

    public function testShouldAuthenticate(): void
    {
        $apiKey = $this->imageApi->tryGetEnvApiKey();
        $this->imageApi->goToList($apiKey);
        $content = $this->imageApi->getJsonContent();

        $this->assertNotEmpty($content);

        $first = reset($content);
        $this->log('first item in the list is: %s', $first);
    }
}
