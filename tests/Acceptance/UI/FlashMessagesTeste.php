<?php

namespace Tests\Acceptance\UI;

use Tests\Acceptance\BaseAcceptanceTeste;
use Tests\Support\AcceptanceTester;

class FlashMessagesTeste extends BaseAcceptanceTeste
{
    public function testSuccessLoginMessage(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        $I->see('Login realizado com sucesso');
        $I->seeElement('.flash-message.success');
    }

    public function testErrorLoginMessage(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'email@invalido.com');
        $I->fillField('password', 'senhaerrada');
        $I->click('Entrar');

        $I->see('Email ou senha incorretos');
        $I->seeElement('.flash-message.danger');
    }

    public function testLogoutMessage(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');
        $I->seeInCurrentUrl('/user');

        $I->wait(2.5);
        $I->click('Sair');

        $I->see('Logout realizado com sucesso');
        $I->seeElement('.flash-message.success');
    }

    public function testAccessDeniedMessage(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        $I->amOnPage('/admin');

        $I->see('Acesso negado');
        $I->seeElement('.flash-message.danger');
    }

    public function testFlashMessageAutoFade(AcceptanceTester $I): void
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'flavio@user.com');
        $I->fillField('password', '123456');
        $I->click('Entrar');

        $I->seeElement('.flash-message.auto-fade');
        $I->seeElement('.flash-message .close-btn');
    }
}
