<?php

namespace Tests\Acceptance\Access;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class AccessRestrictionCest extends BaseAcceptanceCest
{
    public function testUnauthenticatedAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/admin');
        $tester->seeInCurrentUrl('/login');
        $tester->see('Login', 'h2');

        $tester->amOnPage('/hr');
        $tester->seeInCurrentUrl('/login');
        $tester->see('Login', 'h2');

        $tester->amOnPage('/user');
        $tester->seeInCurrentUrl('/login');
        $tester->see('Login', 'h2');
    }

    public function testUserRestrictedAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');
        $tester->seeInCurrentUrl('/user');

        $tester->amOnPage('/admin');
        $tester->dontSeeInCurrentUrl('/admin');
        $tester->see('Acesso negado');

        $tester->amOnPage('/hr');
        $tester->dontSeeInCurrentUrl('/hr');
        $tester->see('Acesso negado');

        $tester->wait(2.5);
        $tester->click('Sair');
        $tester->seeInCurrentUrl('/login');
    }

    public function testHRRestrictedAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'caio@rh.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');
        $tester->seeInCurrentUrl('/hr');

        $tester->amOnPage('/admin');
        $tester->dontSeeInCurrentUrl('/admin');
        $tester->see('Acesso negado');

        $tester->amOnPage('/hr');
        $tester->seeInCurrentUrl('/hr');

        $tester->wait(2.5);
        $tester->click('Sair');
        $tester->seeInCurrentUrl('/login');
    }

    public function testAdminFullAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'klesio@admin.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');
        $tester->seeInCurrentUrl('/admin');

        $tester->amOnPage('/admin');
        $tester->seeInCurrentUrl('/admin');

        $tester->amOnPage('/hr');
        $tester->seeInCurrentUrl('/hr');

        $tester->amOnPage('/user');
        $tester->seeInCurrentUrl('/user');

        $tester->wait(2.5);
        $tester->click('Sair');
        $tester->seeInCurrentUrl('/login');
    }
}
