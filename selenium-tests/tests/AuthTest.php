<?php declare(strict_types=1);

namespace VysokeSkoly\Selenium;

/**
 * @group prod-safe
 */
class AuthTest extends AbstractVysokeSkolyTestCase
{
    public function testShouldRequireAuthentication(): void
    {
        $this->imageApi->goToAuth();
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
        $this->imageApi->goToAuth($apiKey);
        $content = $this->imageApi->getJsonContent();

        $this->assertSame(
            [
                'auth' => 'OK',
            ],
            $content,
        );
    }
}
