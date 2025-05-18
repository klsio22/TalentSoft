<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Module;

class LoginHelper extends Module
{
    /**
     * @return \Codeception\Module\WebDriver
     */
    protected function getWebDriver(): \Codeception\Module\WebDriver
    {
        /** @var \Codeception\Module\WebDriver */
        return $this->getModule('WebDriver');
    }

    public function login(string $username, string $password): void
    {
        $page = $this->getWebDriver();
        $page->amOnPage('/login');
        $page->fillField('email', $username);
        $page->fillField('password', $password);
        $page->click('Entrar');
    }

    public function logout(): void
    {
        $page = $this->getWebDriver();
        $page->click('Sair');
    }
}
