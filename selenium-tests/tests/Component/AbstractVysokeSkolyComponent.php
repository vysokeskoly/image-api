<?php declare(strict_types=1);

namespace VysokeSkoly\Selenium\Component;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Lmc\Steward\Component\AbstractComponent;
use VysokeSkoly\Selenium\AbstractVysokeSkolyTestCase;

abstract class AbstractVysokeSkolyComponent extends AbstractComponent
{
    protected string $imageApiUrl;

    public function __construct(AbstractVysokeSkolyTestCase $tc)
    {
        parent::__construct($tc);

        $this->imageApiUrl = $tc->baseUrl;
    }

    public function getTitle(): string
    {
        return $this->wd->getTitle();
    }

    public function getH1(): string
    {
        return $this->findByCss('h1')->getText();
    }

    public function getDescription(): string
    {
        try {
            return (string) $this->findByCss('meta[name="description"]')->getAttribute('content');
        } catch (NoSuchElementException $e) {
            return '';
        }
    }

    public function generateTestTitle(string $base): string
    {
        return sprintf('%s %s', $base, (new \DateTime())->format(\DateTime::W3C));
    }

    protected function getTextByCss(string $cssSelector): string
    {
        return trim($this->findByCss($cssSelector)->getText());
    }

    protected function getTextsByCss(string $cssSelector): array
    {
        $elements = $this->findMultipleByCss($cssSelector);

        return $this->mapElementsToText($elements);
    }

    protected function mapElementsToText(array $elements): array
    {
        return array_map(function (RemoteWebElement $element) {
            return trim($element->getText());
        }, $elements);
    }

    protected function countByCss(string $cssSelector): int
    {
        $items = $this->findMultipleByCss($cssSelector);

        return count($items);
    }

    protected function waitForCssAfterRefresh(string $selector): mixed
    {
        return $this->wd->wait()->until(
            WebDriverExpectedCondition::refreshed(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector($selector)),
            ),
        );
    }

    protected function waitUntilPartialTextInCss(string $selector, string $text): void
    {
        $this->wd->wait()->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::cssSelector($selector), $text),
        );
    }

    protected function findByCssAndIndex(string $selector, int $index): ?RemoteWebElement
    {
        $elements = $this->findMultipleByCss($selector);

        return $elements[$index] ?? null;
    }

    protected function getByCssAndIndex(string $selector, int $index): RemoteWebElement
    {
        $element = $this->findByCssAndIndex($selector, $index);
        if (!$element) {
            throw new NoSuchElementException(sprintf('Element by "%s[%d]" was not found.', $selector, $index));
        }

        return $element;
    }

    protected function clickOnLink(string $linkText, string $expectedTitle): void
    {
        try {
            $this
                ->findByLinkText($linkText)
                ->click();
        } catch (TimeoutException $e) {
            $this->tc->fail(
                sprintf(
                    'Click on link with text "%s" failed on timeout.',
                    $linkText,
                ),
            );
        }

        try {
            $this->waitForTitle($expectedTitle);
        } catch (TimeoutException $e) {
            $this->tc->fail(
                sprintf(
                    'Click on link with text "%s" failed on timeout expecting a title "%s".',
                    $linkText,
                    $expectedTitle,
                ),
            );
        }
    }

    protected function scrollAndClickByCss(string $selector): void
    {
        $element = $this->waitForCss($selector, true);
        //$element->getLocationOnScreenOnceScrolledIntoView();
        $element->click();
    }

    protected function assertHasStringParts(array $expectedParts, string $actual): void
    {
        foreach ($expectedParts as $expectedPart) {
            $this->tc->assertStringContainsString($expectedPart, $actual);
        }
    }

    protected function findChildByCss(RemoteWebElement $element, string $css): RemoteWebElement
    {
        return $element->findElement(WebDriverBy::cssSelector($css));
    }

    /** @return RemoteWebElement[] */
    protected function findChildrenByCss(RemoteWebElement $element, string $css): array
    {
        return $element->findElements(WebDriverBy::cssSelector($css));
    }

    protected function findChildByPartialLinkText(RemoteWebElement $element, string $linkText): RemoteWebElement
    {
        return $element->findElement(WebDriverBy::partialLinkText($linkText));
    }

    protected function getChildTextByCss(RemoteWebElement $element, string $css): string
    {
        return trim($element->findElement(WebDriverBy::cssSelector($css))->getText());
    }

    /**
     * @param int[] $expectedRange
     */
    protected function assertBetween(array $expectedRange, int $value): void
    {
        $min = min($expectedRange);
        $max = max($expectedRange);
        $message = sprintf('Failed asserting that %d is between %d and %d.', $value, $min, $max);

        $this->tc->assertGreaterThanOrEqual($min, $value, $message);
        $this->tc->assertLessThanOrEqual($max, $value, $message);
    }

    protected function assertAround(int $expectedValue, int $tolerance, int $value): void
    {
        $this->assertBetween([$expectedValue + $tolerance, $expectedValue - $tolerance], $value);
    }

    protected function confirmAlert(string $expectedPartialText): void
    {
        $this->wd->wait()->until(WebDriverExpectedCondition::alertIsPresent());
        $alert = $this->wd->switchTo()->alert();

        $this->tc->assertStringContainsString($expectedPartialText, $alert->getText());

        $alert->accept();
    }

    protected function rejectAlert(string $expectedText): void
    {
        $this->wd->wait()->until(WebDriverExpectedCondition::alertIsPresent());
        $alert = $this->wd->switchTo()->alert();

        $this->tc->assertSame($expectedText, $alert->getText());

        $alert->dismiss();
    }

    protected function assertAlertContainsMessages(array $expectedMessages): void
    {
        foreach ($expectedMessages as $type => $messages) {
            $this->assertAlertContains($type, $messages);
        }
    }

    protected function assertAlertContains(string $type, array $expectedMessages): void
    {
        if (empty($expectedMessages)) {
            return;
        }

        $alertContent = $this->getTextByCss(sprintf('.alert-%s', $type));

        foreach ($expectedMessages as $expected) {
            $this->tc->assertStringContainsString($expected, $alertContent);
        }
    }
}
