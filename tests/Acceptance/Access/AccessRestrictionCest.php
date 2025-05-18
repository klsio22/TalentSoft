<?php

namespace Tests\Acceptance\Access;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class AccessRestrictionCest extends BaseAcceptanceCest
{
    /**
     * Teste de acesso a páginas restritas sem estar autenticado
     */
    public function testUnauthenticatedAccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/admin');
        $I->seeInCurrentUrl('/login');
        $I->see('Você deve estar logado para acessar essa página');

        $I->amOnPage('/hr');
        $I->seeInCurrentUrl('/login');
        $I->see('Você deve estar logado para acessar essa página');

        $I->amOnPage('/user');
        $I->seeInCurrentUrl('/login');
        $I->see('Você deve estar logado para acessar essa página');
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

        // Logout
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
    }

    /**
     * Testa acesso de RH às áreas restritas
     */
    public function testHRRestrictedAccess(AcceptanceTester $I): void
    {
        // Login como RH
        $I->amOnPage('/login');
        $I->fillField('email', 'caio@rh.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/hr');

        // Tenta acessar área de admin
        $I->amOnPage('/admin');
        $I->dontSeeInCurrentUrl('/admin');
        $I->see('Acesso negado');

        // Pode acessar área de RH
        $I->amOnPage('/hr');
        $I->seeInCurrentUrl('/hr');

        // Logout
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
    }

    /**
     * Testa acesso de admin às áreas restritas
     */
    public function testAdminFullAccess(AcceptanceTester $I): void
    {
        // Login como admin
        $I->amOnPage('/login');
        $I->fillField('email', 'klesio@admin.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/admin');

        // Pode acessar área de admin
        $I->amOnPage('/admin');
        $I->seeInCurrentUrl('/admin');

        // Tenta acessar área de RH (admin tem acesso completo)
        $I->amOnPage('/hr');
        $I->seeInCurrentUrl('/hr');

        // Tenta acessar área de usuário (admin tem acesso completo)
        $I->amOnPage('/user');
        $I->seeInCurrentUrl('/user');

        // Logout
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
    }
}
