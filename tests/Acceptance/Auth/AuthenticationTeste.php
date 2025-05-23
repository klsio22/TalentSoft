<?php

namespace Tests\Acceptance\Auth;

use Tests\Acceptance\BaseAcceptanceTeste;
use Tests\Support\AcceptanceTester;

class AuthenticationTeste extends BaseAcceptanceTeste
{
    public function testLoginPageAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->see('Login');
        $tester->seeElement('input[name=email]');
        $tester->seeElement('input[name=password]');
        $tester->seeElement('button[type=submit]');
    }

    public function testAdminLogin(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'klesio@admin.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        $tester->seeInCurrentUrl('/admin');
        $tester->see('Olá, Klesio Nascimento');
        $tester->see('Sair');
    }

    public function testHRLogin(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'caio@rh.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        $tester->seeInCurrentUrl('/hr');
        $tester->see('Olá, Caio Silva');
        $tester->see('Sair');
    }

    public function testUserLogin(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        $tester->seeInCurrentUrl('/user');
        $tester->see('Olá, Flavio Santos');
        $tester->see('Sair');
    }

    public function testInvalidLogin(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'usuario@invalido.com');
        $tester->fillField('password', 'senhaerrada');
        $tester->click('Entrar');

        $tester->seeInCurrentUrl('/login');
        $tester->see('Email ou senha incorretos');
    }

    public function testLogout(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');
        $tester->seeInCurrentUrl('/user');

        $tester->wait(2.5);
        $tester->click('Sair');
        $tester->seeInCurrentUrl('/login');
        $tester->see('Logout realizado com sucesso');
    }
}
