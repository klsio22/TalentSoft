<?php

declare(strict_types=1);

namespace Tests\Acceptance\UI;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

/**
 * Testes de Aceitação para Mensagens Flash
 *
 * Esta classe testa o comportamento das mensagens flash (notificações temporárias)
 * exibidas na interface do usuário em diferentes cenários de interação.
 *
 * @author TalentSoft Team
 * @package Tests\Acceptance\UI
 */
class FlashMessagesCest extends BaseAcceptanceCest
{
    /**
     * Testa a exibição de mensagem de sucesso no login
     *
     * Verifica se uma mensagem de sucesso é exibida quando o usuário
     * realiza login com credenciais válidas e se o elemento CSS
     * correspondente está presente na página.
     *
     * @param AcceptanceTester $tester Instância do testador de aceitação
     * @return void
     */
    public function testSuccessLoginMessage(AcceptanceTester $tester): void
    {
        // Navega para a página de login
        $tester->amOnPage('/login');

        // Preenche os campos de email e senha com credenciais válidas
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');

        // Clica no botão de login
        $tester->click('Entrar');

        // Verifica se a mensagem de sucesso é exibida
        $tester->see('Login realizado com sucesso');

        // Verifica se o elemento CSS da mensagem de sucesso está presente
        $tester->seeElement('.flash-message.success');
    }

    /**
     * Testa a exibição de mensagem de erro no login
     *
     * Verifica se uma mensagem de erro é exibida quando o usuário
     * tenta fazer login com credenciais inválidas e se o elemento CSS
     * correspondente está presente na página.
     *
     * @param AcceptanceTester $tester Instância do testador de aceitação
     * @return void
     */
    public function testErrorLoginMessage(AcceptanceTester $tester): void
    {
        // Navega para a página de login
        $tester->amOnPage('/login');

        // Preenche os campos com credenciais inválidas
        $tester->fillField('email', 'email@invalido.com');
        $tester->fillField('password', 'senhaerrada');

        // Clica no botão de login
        $tester->click('Entrar');

        // Verifica se a mensagem de erro é exibida
        $tester->see('Email ou senha incorretos');

        // Verifica se o elemento CSS da mensagem de erro está presente
        $tester->seeElement('.flash-message.danger');
    }

    /**
     * Testa a exibição de mensagem de sucesso no logout
     *
     * Verifica se uma mensagem de sucesso é exibida quando o usuário
     * realiza logout do sistema após estar autenticado.
     *
     * @param AcceptanceTester $tester Instância do testador de aceitação
     * @return void
     */
    public function testLogoutMessage(AcceptanceTester $tester): void
    {
        // Realiza login completo
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        // Verifica se foi redirecionado para a área do usuário
        $tester->seeInCurrentUrl('/user');

        // Aguarda um tempo para garantir carregamento completo da página
        $tester->wait(2.5);

        // Realiza logout
        $tester->click('Sair');

        // Verifica se a mensagem de sucesso do logout é exibida
        $tester->see('Logout realizado com sucesso');

        // Verifica se o elemento CSS da mensagem de sucesso está presente
        $tester->seeElement('.flash-message.success');
    }

    /**
     * Testa a exibição de mensagem de acesso negado
     *
     * Verifica se uma mensagem de acesso negado é exibida quando um usuário
     * comum tenta acessar uma área restrita do sistema.
     *
     * @param AcceptanceTester $tester Instância do testador de aceitação
     * @return void
     */
    public function testAccessDeniedMessage(AcceptanceTester $tester): void
    {
        // Realiza login com usuário comum
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        // Tenta acessar área administrativa (restrita)
        $tester->amOnPage('/admin');

        // Verifica se a mensagem de acesso negado é exibida
        $tester->see('Acesso negado');

        // Verifica se o elemento CSS da mensagem de erro está presente
        $tester->seeElement('.flash-message.danger');
    }

    /**
     * Testa funcionalidades de auto-fade das mensagens flash
     *
     * Verifica se as mensagens flash possuem as classes CSS necessárias
     * para auto-fade (desaparecimento automático) e botão de fechamento manual.
     *
     * @param AcceptanceTester $tester Instância do testador de aceitação
     * @return void
     */
    public function testFlashMessageAutoFade(AcceptanceTester $tester): void
    {
        // Realiza login para gerar uma mensagem flash
        $tester->amOnPage('/login');
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', '123456');
        $tester->click('Entrar');

        // Verifica se a mensagem possui a classe para auto-fade
        $tester->seeElement('.flash-message.auto-fade');

        // Verifica se o botão de fechamento está presente
        $tester->seeElement('.flash-message .close-btn');
    }
}
