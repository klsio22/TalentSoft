<?php

namespace Tests\Acceptance\UI;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class FlashMessagesCest extends BaseAcceptanceCest
{
    /**
     * Testa mensagem flash de sucesso no login
     */
    public function testSuccessLoginMessage(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        // Verifica mensagem de sucesso
        $I->see('Login realizado com sucesso');
        // Verifica classe de sucesso
        $I->seeElement('.flash-message.success');
    }

    /**
     * Testa mensagem flash de erro em login inválido
     */
    public function testErrorLoginMessage(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'email@invalido.com');
        $I->fillField('password', 'senhaerrada');
        $I->click('Entrar');

        // Verifica mensagem de erro
        $I->see('Email ou senha incorretos');
        // Verifica classe de erro
        $I->seeElement('.flash-message.danger');
    }

    /**
     * Testa mensagem flash de logout bem sucedido
     */
    public function testLogoutMessage(AcceptanceTester $I): void
    {
        // Fazer login primeiro
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/user');

        // Espera a mensagem flash desaparecer antes de clicar em Sair
        $I->wait(2.5); // Espera 2.5 segundos para a mensagem flash desaparecer
        // Fazer logout
        $I->click('Sair');

        // Verifica mensagem de sucesso no logout
        $I->see('Logout realizado com sucesso');
        $I->seeElement('.flash-message.success');
    }

    /**
     * Testa mensagem flash de acesso negado
     */
    public function testAccessDeniedMessage(AcceptanceTester $I): void
    {
        // Login como usuário comum
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        // Tenta acessar área restrita
        $I->amOnPage('/admin');

        // Verifica mensagem de erro
        $I->see('Acesso negado');
        $I->seeElement('.flash-message.danger');
    }

    /**
     * Testa desaparecimento automático da mensagem flash
     * Obs: Este teste precisa de uma verificação visual ou JavaScript
     * para ser totalmente automatizado, aqui verificamos apenas se o elemento
     * tem as classes corretas para o comportamento
     */
    public function testFlashMessageAutoFade(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        // Verifica se a mensagem flash tem a classe para auto-fade
        $I->seeElement('.flash-message.auto-fade');

        // Verifica se existe o botão de fechamento manual
        $I->seeElement('.flash-message .close-btn');
    }
}
