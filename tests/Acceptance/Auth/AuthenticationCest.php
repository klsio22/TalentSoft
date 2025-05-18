<?php

namespace Tests\Acceptance\Auth;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class AuthenticationCest extends BaseAcceptanceCest
{
    /**
     * Testa o acesso à página de login
     */
    public function testLoginPageAccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->see('Login');
        $I->seeElement('input[name=email]');
        $I->seeElement('input[name=password]');
        $I->seeElement('button[type=submit]');
    }

    /**
     * Testa o login com usuário admin
     */
    public function testAdminLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'klesio@admin.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        // Verifica redirecionamento para área de admin
        $I->seeInCurrentUrl('/admin');
        $I->see('Olá, Klesio Nascimento');
        $I->see('Sair');
    }

    /**
     * Testa o login com usuário RH
     */
    public function testHRLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'caio@rh.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        // Verifica redirecionamento para área de RH
        $I->seeInCurrentUrl('/hr');
        $I->see('Olá, Caio Silva');
        $I->see('Sair');
    }

    /**
     * Testa o login com usuário comum
     */
    public function testUserLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        // Verifica redirecionamento para área de usuário
        $I->seeInCurrentUrl('/user');
        $I->see('Olá, Flavio Santos');
        $I->see('Sair');
    }

    /**
     * Testa o login com credenciais inválidas
     */
    public function testInvalidLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'usuario@invalido.com');
        $I->fillField('password', 'senhaerrada');
        $I->click('Entrar');

        // Verifica que continua na página de login e mostra mensagem de erro
        $I->seeInCurrentUrl('/login');
        $I->see('Email ou senha incorretos');
    }

    /**
     * Testa o processo de logout
     */
    public function testLogout(AcceptanceTester $I): void
    {
        // Login
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/user');

        // Logout
        $I->click('Sair');
        $I->seeInCurrentUrl('/login');
        $I->see('Logout realizado com sucesso');
    }
}
