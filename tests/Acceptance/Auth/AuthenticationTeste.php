<?php

namespace Tests\Acceptance\Auth;

use Tests\Acceptance\BaseAcceptanceTeste;
use Tests\Support\AcceptanceTester;

class AuthenticationTeste extends BaseAcceptanceTeste
{
    public function testLoginPageAccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->see('Login');
        $I->seeElement('input[name=email]');
        $I->seeElement('input[name=password]');
        $I->seeElement('button[type=submit]');
    }

    public function testAdminLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'klesio@admin.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        $I->seeInCurrentUrl('/admin');
        $I->see('Olá, Klesio Nascimento');
        $I->see('Sair');
    }

    public function testHRLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'caio@rh.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        $I->seeInCurrentUrl('/hr');
        $I->see('Olá, Caio Silva');
        $I->see('Sair');
    }

    public function testUserLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        $I->seeInCurrentUrl('/user');
        $I->see('Olá, Flavio Santos');
        $I->see('Sair');
    }

    public function testInvalidLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'usuario@invalido.com');
        $I->fillField('password', 'senhaerrada');
        $I->click('Entrar');

        $I->seeInCurrentUrl('/login');
        $I->see('Email ou senha incorretos');
    }

    public function testLogout(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/user');

        $I->wait(2.5);
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
        $I->see('Logout realizado com sucesso');
    }
}
