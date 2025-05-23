<?php

namespace Tests\Acceptance\home;

use Tests\Acceptance\BaseAcceptanceTeste;
use Tests\Support\AcceptanceTester;

class HomeIndexTeste extends BaseAcceptanceTeste
{
    public function seeHomePage(AcceptanceTester $page): void
    {
        $page->amOnPage('/');
        $page->see('Home Page', '//h1');
    }
}
