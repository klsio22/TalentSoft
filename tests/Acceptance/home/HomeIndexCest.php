<?php

namespace Tests\Acceptance\home;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class HomeIndexCest extends BaseAcceptanceCest
{
    public function seeHomePage(AcceptanceTester $page): void
    {
        $page->amOnPage('/');
        $page->see('TalentSoft', '//h1');
        $page->see('Sistema integrado para gestão de talentos e recursos humanos');
        $page->seeElement('//a[contains(., "Fazer Login")]');
    }

    public function seeFeatures(AcceptanceTester $page): void
    {
        $page->amOnPage('/');
        $page->see('Gestão de Funcionários');
        $page->see('Gerenciamento de Projetos');
        $page->see('Relatórios Analíticos');
    }
}
