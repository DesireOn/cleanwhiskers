<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use PHPUnit\Framework\TestCase;

if (!class_exists(PantherTestCase::class)) {
    class HowItWorksKeyboardNavigationTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

use Facebook\WebDriver\WebDriverKeys;
use Symfony\Component\Panther\PantherTestCase;

final class HowItWorksKeyboardNavigationTest extends PantherTestCase
{
    public function testCardsActivateWithKeyboard(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/');

        // first card
        $client->executeScript("document.querySelectorAll('.how-it-works__card')[0].focus();");
        $client->getWebDriver()->getKeyboard()->pressKey(WebDriverKeys::ENTER);
        self::assertStringContainsString('#search-form', $client->getCurrentURL());

        // book card
        $client->back();
        $client->executeScript("document.querySelectorAll('.how-it-works__card')[1].focus();");
        $client->getWebDriver()->getKeyboard()->pressKey(WebDriverKeys::ENTER);
        self::assertStringContainsString('/blog/how-to-book', $client->getCurrentURL());

        // relax card
        $client->back();
        $client->executeScript("document.querySelectorAll('.how-it-works__card')[2].focus();");
        $client->getWebDriver()->getKeyboard()->pressKey(WebDriverKeys::ENTER);
        self::assertStringContainsString('#featured-groomers', $client->getCurrentURL());
    }
}
