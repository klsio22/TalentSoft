<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Module;

class LoginHelper extends Module
{
    public function login(string $username, string $password): void
    {
        /** @var \Tests\Support\AcceptanceTester $page */
        $page = $this->getModule('WebDriver');
        $page->amOnPage('/login');
        $page->fillField('email', $username);
        $page->fillField('password', $password);
        $page->click('Entrar');
    }

    public function logout(): void
    {
        /** @var \Tests\Support\AcceptanceTester $page */
        $page = $this->getModule('WebDriver');
        $page->click('Sair');
    }
}
