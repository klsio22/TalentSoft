<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Classe AcceptanceTester - Ator para Testes de Aceitação
 *
 * Esta classe herda funcionalidades do Codeception\Actor e fornece métodos
 * para realizar testes de aceitação automatizados na aplicação TalentSoft.
 *
 * Métodos Herdados do Codeception:
 * @method void wantTo($text) Define o objetivo do teste
 * @method void wantToTest($text) Define o que será testado
 * @method void execute($callable) Executa uma função callable
 * @method void expectTo($prediction) Define uma expectativa
 * @method void expect($prediction) Define uma expectativa (alias)
 * @method void amGoingTo($argumentation) Descreve a próxima ação
 * @method void am($role) Define o papel do usuário no teste
 * @method void lookForwardTo($achieveValue) Define um resultado esperado
 * @method void comment($description) Adiciona comentário ao teste
 * @method void pause($vars = []) Pausa a execução para debug
 *
 * Métodos de Navegação e Interação Web:
 * @method void amOnPage(string $url) Navega para uma URL específica
 * @method void fillField(string $field, string $value) Preenche um campo de formulário
 * @method void click(string $button) Clica em um botão ou link
 * @method void see(string $text, string $selector = NULL) Verifica se um texto está visível
 * @method void seeInCurrentUrl(string $url) Verifica se a URL atual contém o texto especificado
 * @method void seeElement(string $selector) Verifica se um elemento CSS existe na página
 * @method void wait(float $timeout) Aguarda um tempo específico em segundos
 *
 * Métodos Customizados de Autenticação:
 * @method void login(string $username, string $password) Realiza login no sistema
 *
 * @author TalentSoft Team
 * @package Tests\Support
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Área para definir ações personalizadas do testador
     *
     * Este espaço pode ser usado para adicionar métodos customizados
     * que encapsulem ações complexas ou frequentemente utilizadas
     * nos testes de aceitação da aplicação TalentSoft.
     *
     * Exemplo de método personalizado:
     *
     * public function loginAsAdmin(): void
     * {
     *     $this->amOnPage('/login');
     *     $this->fillField('email', 'admin@talentsoft.com');
     *     $this->fillField('password', 'admin123');
     *     $this->click('Entrar');
     * }
     */
}
