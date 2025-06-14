<?php

namespace Tests\Acceptance\Employees;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class EmployeeValidationCest extends BaseAcceptanceCest
{
    private const LOGIN_URL = '/login';
    private const ADMIN_EMAIL = 'klesio@admin.com';
    private const DEFAULT_PASSWORD = '123456';
    private const EMPLOYEES_CREATE_URL = '/employees/create';
    private const SAVE_BUTTON = 'Salvar Funcionário';
    private const ERROR_MESSAGE_SELECTOR = '.invalid-feedback, .text-danger, .error-message';
    private const TEST_EMPLOYEE_NAME = 'Test Employee';

    private function loginAsAdmin(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', self::ADMIN_EMAIL);
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        $tester->wait(1);
    }

    public function testRequiredFields(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        $tester->amOnPage(self::EMPLOYEES_CREATE_URL);
        $tester->wait(1);

        // Submit form without filling any fields
        $tester->click(self::SAVE_BUTTON);
        $tester->wait(1);

        // Check for validation errors
        $tester->seeElement(self::ERROR_MESSAGE_SELECTOR);

        // Check specific required fields
        $tester->see('obrigatório', self::ERROR_MESSAGE_SELECTOR);
    }

    public function testEmailValidation(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        $tester->amOnPage(self::EMPLOYEES_CREATE_URL);
        $tester->wait(1);

        // Fill form with invalid email
        $tester->fillField('name', self::TEST_EMPLOYEE_NAME);
        $tester->fillField('email', 'invalid-email');
        $tester->fillField('cpf', '123.456.789-00');
        $tester->fillField('birth_date', '1990-01-01');

        // Submit form
        $tester->click(self::SAVE_BUTTON);
        $tester->wait(1);

        // Check for email validation error
        $tester->seeElement(self::ERROR_MESSAGE_SELECTOR);
        $tester->see('email', self::ERROR_MESSAGE_SELECTOR);
    }

    public function testCpfValidation(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        $tester->amOnPage(self::EMPLOYEES_CREATE_URL);
        $tester->wait(1);

        // Fill form with invalid CPF
        $tester->fillField('name', self::TEST_EMPLOYEE_NAME);
        $tester->fillField('email', 'valid@example.com');
        $tester->fillField('cpf', '123');
        $tester->fillField('birth_date', '1990-01-01');

        // Submit form
        $tester->click(self::SAVE_BUTTON);
        $tester->wait(1);

        // Check for CPF validation error
        $tester->seeElement(self::ERROR_MESSAGE_SELECTOR);
        $tester->see('CPF', self::ERROR_MESSAGE_SELECTOR);
    }

    public function testDateValidation(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        $tester->amOnPage(self::EMPLOYEES_CREATE_URL);
        $tester->wait(1);

        // Fill form with future date
        $tester->fillField('name', self::TEST_EMPLOYEE_NAME);
        $tester->fillField('email', 'valid@example.com');
        $tester->fillField('cpf', '123.456.789-00');
        $tester->fillField('birth_date', date('Y-m-d', strtotime('+1 year')));

        // Submit form
        $tester->click(self::SAVE_BUTTON);
        $tester->wait(1);

        // Check for date validation error - only verify the error element exists
        $tester->seeElement(self::ERROR_MESSAGE_SELECTOR);
        // Check that we're still on the create form page
        $tester->seeInCurrentUrl('create');
        $tester->see('Dados Pessoais');
    }
}
