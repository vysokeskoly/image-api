<?php declare(strict_types=1);

namespace VysokeSkoly\Selenium\Component;

class ImageApiComponent extends AbstractVysokeSkolyComponent
{
    private function goToPage(string $page, ?string $token = null): void
    {
        $apiKey = $token === null
            ? ''
            : sprintf('?apikey=%s', $token);

        $this->wd->get(
            sprintf(
                '%s/%s%s',
                rtrim($this->imageApiUrl, '/'),
                ltrim($page, '/'),
                $apiKey,
            ),
        );
    }

    public function goToIndex(): void
    {
        $this->goToPage('/');
    }

    public function goToAuth(?string $token = null): void
    {
        $this->goToPage('/auth', $token);
    }

    public function goToList(?string $token = null): void
    {
        $this->goToPage('/list', $token);
    }

    public function getJsonContent(): array
    {
        try {
            $content = $this->findByCss('body>pre')->getText();
        } catch (\Throwable $e) {
            $content = $this->wd->getPageSource();
        }

        return \Safe\json_decode($content, true);
    }

    public function tryGetEnvApiKey(): ?string
    {
        return is_string($value = getenv('API_KEY'))
            ? $value
            : null;
    }
}
