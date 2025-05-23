<?php

namespace Tests\Acceptance\UI;

use Tests\Acceptance\BaseAcceptanceTeste;
use Tests\Support\AcceptanceTester;

class FlashMessagesTeste extends BaseAcceptanceTeste
{
    public function testSuccessLoginMessage(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        $tester->see('Login realizado com sucesso');
        $tester->seeElement('.flash-message.success');
    }

    public function testErrorLoginMessage(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'email@invalido.com');
        $tester->fillField('password', 'senhaerrada');
        $tester->click('Entrar');

        $tester->see('Email ou senha incorretos');
        $tester->seeElement('.flash-message.danger');
    }

    public function testLogoutMessage(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');
        $tester->seeInCurrentUrl('/user');

        $tester->wait(2.5);
        $tester->click('Sair');

        $tester->see('Logout realizado com sucesso');
        $tester->seeElement('.flash-message.success');
    }

    public function testAccessDeniedMessage(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        $tester->amOnPage('/admin');

        $tester->see('Acesso negado');
        $tester->seeElement('.flash-message.danger');
    }

    public function testFlashMessageAutoFade(AcceptanceTester $tester): void
    {
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        $tester->seeElement('.flash-message.auto-fade');
        $tester->seeElement('.flash-message .close-btn');
    }
}
