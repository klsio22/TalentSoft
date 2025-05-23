<?php

namespace Tests\Acceptance\Access;

use Tests\Acceptance\BaseAcceptanceTeste;
use Tests\Support\AcceptanceTester;

class AccessRestrictionTeste extends BaseAcceptanceTeste
{
    public function testUnauthenticatedAccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/admin');
        $I->seeInCurrentUrl('/login');
        $I->see('Login', 'h2');

        $I->amOnPage('/hr');
        $I->seeInCurrentUrl('/login');
        $I->see('Login', 'h2');

        $I->amOnPage('/user');
        $I->seeInCurrentUrl('/login');
        $I->see('Login', 'h2');
    }

    public function testUserRestrictedAccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/user');

        $I->amOnPage('/admin');
        $I->dontSeeInCurrentUrl('/admin');
        $I->see('Acesso negado');

        $I->amOnPage('/hr');
        $I->dontSeeInCurrentUrl('/hr');
        $I->see('Acesso negado');

        $I->wait(2.5);
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
    }

    public function testHRRestrictedAccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'caio@rh.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/hr');

        $I->amOnPage('/admin');
        $I->dontSeeInCurrentUrl('/admin');
        $I->see('Acesso negado');

        $I->amOnPage('/hr');
        $I->seeInCurrentUrl('/hr');

        $I->wait(2.5);
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
    }

    public function testAdminFullAccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'klesio@admin.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/admin');

        $I->amOnPage('/admin');
        $I->seeInCurrentUrl('/admin');

        $I->amOnPage('/hr');
        $I->seeInCurrentUrl('/hr');

        $I->amOnPage('/user');
        $I->seeInCurrentUrl('/user');

        $I->wait(2.5);
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
    }
}
