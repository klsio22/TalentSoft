<?php

namespace Tests\Acceptance\Employees;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class EmployeeCRUDCest extends BaseAcceptanceCest
{
    private const LOGIN_URL = '/login';
    private const ADMIN_EMAIL = 'klesio@admin.com';
    private const DEFAULT_PASSWORD = '123456';
    private const EMPLOYEES_INDEX_URL = '/employees';
    private const EMPLOYEES_CREATE_URL = '/employees/create';
    private const SAVE_EMPLOYEE_BUTTON = 'Salvar Funcionário';
    private const UPDATE_EMPLOYEE_BUTTON = 'Atualizar Funcionário';
    private const NEW_EMPLOYEE_HEADING = 'Novo Funcionário';
    private const EDIT_EMPLOYEE_HEADING = 'Editar Funcionário';
    private const DELETE_TEST_EMPLOYEE_NAME = 'Funcionário Para Deletar';

    private function loginAsAdmin(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', self::ADMIN_EMAIL);
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        $tester->wait(1);
    }

    /**
     * Teste de cadastro com dados incorretos
     */
    public function testCreateEmployeeWithInvalidData(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);
        
        $tester->amOnPage(self::EMPLOYEES_CREATE_URL);
        $tester->see(self::NEW_EMPLOYEE_HEADING);

        // Tentar submeter formulário vazio
        $tester->click(self::SAVE_EMPLOYEE_BUTTON);
        $tester->wait(1);

        // Verificar se permanece na página de criação
        $tester->seeInCurrentUrl(self::EMPLOYEES_CREATE_URL);
        
        // Preencher com dados inválidos
        $tester->fillField('name', ''); // Nome vazio
        $tester->fillField('email', 'email-invalido'); // Email inválido
        $tester->fillField('cpf', '123'); // CPF inválido
        $tester->fillField('birth_date', '2030-01-01'); // Data futura
        $tester->fillField('password', '123'); // Senha muito curta
        $tester->fillField('password_confirmation', '456'); // Confirmação diferente
        
        $tester->click(self::SAVE_EMPLOYEE_BUTTON);
        $tester->wait(2);

        // Verificar mensagens de erro (ou permanência na página)
        $tester->seeInCurrentUrl(self::EMPLOYEES_CREATE_URL);
    }

    /**
     * Teste de cadastro bem-sucedido
     */
    public function testCreateEmployeeSuccessfully(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);
        
        $tester->amOnPage(self::EMPLOYEES_CREATE_URL);
        $tester->see(self::NEW_EMPLOYEE_HEADING);

        // Preencher com dados válidos
        $tester->fillField('name', 'João Silva Teste');
        $tester->fillField('email', 'joao.teste@example.com');
        $tester->fillField('cpf', '12345678901');
        $tester->fillField('birth_date', '1990-01-01');
        $tester->selectOption('role_id', '1'); // Assumindo que existe role com ID 1
        $tester->fillField('salary', '5000.00');
        $tester->fillField('hire_date', date('Y-m-d'));
        $tester->fillField('password', 'senha123');
        $tester->fillField('password_confirmation', 'senha123');
        
        $tester->click(self::SAVE_EMPLOYEE_BUTTON);
        $tester->wait(2);

        // Verificar redirecionamento para listagem
        $tester->seeInCurrentUrl(self::EMPLOYEES_INDEX_URL);
        $tester->see('Funcionário cadastrado com sucesso!');
        $tester->see('João Silva Teste');
    }

    /**
     * Teste de atualização com dados incorretos
     */
    public function testUpdateEmployeeWithInvalidData(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);
        
        // Ir para listagem e editar primeiro funcionário
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);
        
        // Assumindo que existe um funcionário para editar
        $tester->click('//a[contains(@href, "/employees/") and contains(@href, "/edit")][1]');
        $tester->wait(1);
        $tester->see(self::EDIT_EMPLOYEE_HEADING);

        // Limpar e preencher com dados inválidos
        $tester->fillField('name', ''); // Nome vazio
        $tester->fillField('email', 'email-mal-formado'); // Email inválido
        
        $tester->click(self::UPDATE_EMPLOYEE_BUTTON);
        $tester->wait(2);

        // Verificar que permanece na página de edição
        $tester->seeInCurrentUrl('/edit');
    }

    /**
     * Teste de atualização bem-sucedida
     */
    public function testUpdateEmployeeSuccessfully(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);
        
        // Ir para listagem e editar primeiro funcionário
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);
        
        // Clicar em editar no primeiro funcionário
        $tester->click('//a[contains(@href, "/employees/") and contains(@href, "/edit")][1]');
        $tester->wait(1);
        $tester->see(self::EDIT_EMPLOYEE_HEADING);

        // Atualizar com dados válidos
        $tester->fillField('name', 'Nome Atualizado Teste');
        
        $tester->click(self::UPDATE_EMPLOYEE_BUTTON);
        $tester->wait(2);

        // Verificar sucesso
        $tester->see('Funcionário atualizado com sucesso!');
        $tester->see('Nome Atualizado Teste');
    }

    /**
     * Teste de visualização de funcionário
     */
    public function testViewEmployee(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);
        
        // Ir para listagem
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);
        
        // Clicar em visualizar no primeiro funcionário
        $tester->click('//a[contains(@href, "/employees/") and not(contains(@href, "/edit"))][1]');
        $tester->wait(1);
        
        // Verificar elementos da página de visualização
        $tester->see('Detalhes do Funcionário');
        $tester->seeElement('//div[contains(@class, "employee-details") or contains(@class, "card")]');
    }

    /**
     * Teste de exclusão de funcionário
     */
    public function testDeleteEmployee(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);
        
        // Criar um funcionário específico para deletar
        $tester->amOnPage(self::EMPLOYEES_CREATE_URL);
        $tester->fillField('name', self::DELETE_TEST_EMPLOYEE_NAME);
        $tester->fillField('email', 'deletar@example.com');
        $tester->fillField('cpf', '98765432100');
        $tester->fillField('birth_date', '1985-05-15');
        $tester->selectOption('role_id', '1');
        $tester->fillField('salary', '3000.00');
        $tester->fillField('hire_date', date('Y-m-d'));
        $tester->fillField('password', 'senha123');
        $tester->fillField('password_confirmation', 'senha123');
        $tester->click(self::SAVE_EMPLOYEE_BUTTON);
        $tester->wait(2);
        
        // Voltar para listagem e deletar
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);
        $tester->see(self::DELETE_TEST_EMPLOYEE_NAME);
        
        // Procurar e clicar no botão de deletar
        $tester->click('//button[@class="btn btn-danger" or contains(@class, "delete")]');
        $tester->wait(1);
        
        // Confirmar exclusão se houver modal
        $tester->acceptPopup();
        $tester->wait(2);
        
        // Verificar que o funcionário foi removido
        $tester->dontSee(self::DELETE_TEST_EMPLOYEE_NAME);
        $tester->see('Funcionário excluído com sucesso!');
    }

    /**
     * Teste de listagem básica
     */
    public function testEmployeesList(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);
        
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);
        
        // Verificar elementos da página de listagem
        $tester->see('Lista de Funcionários');
        $tester->seeElement('table');
        $tester->see('Nome');
        $tester->see('Email');
        $tester->see('Cargo');
        $tester->see('Ações');
        
        // Verificar funcionários padrão
        $tester->see('Klesio Nascimento');
        $tester->see('Caio Silva');
        $tester->see('Flavio Santos');
    }
}
