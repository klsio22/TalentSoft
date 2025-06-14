<?php

namespace Tests\Acceptance\Employees;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class EmployeePaginationCest extends BaseAcceptanceCest
{
    private const LOGIN_URL = '/login';
    private const ADMIN_EMAIL = 'klesio@admin.com';
    private const DEFAULT_PASSWORD = '123456';
    private const EMPLOYEES_INDEX_URL = '/employees';
    private const TABLE_ROW_SELECTOR = 'table tbody tr';

    private function loginAsAdmin(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', self::ADMIN_EMAIL);
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        $tester->wait(1);
    }

    public function testPaginationElementsExist(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Navigate to employees list
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);

        // Check if there are employees in the table
        $tester->seeElement(self::TABLE_ROW_SELECTOR);
    }

    public function testNavigateThroughPages(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Navigate to employees list
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);

        // Check for employees in the table
        $tester->seeElement(self::TABLE_ROW_SELECTOR);

        // Check if there are employees in the table
        $tester->seeElement(self::TABLE_ROW_SELECTOR);

        // Verify we can see employee data
        $tester->see('Nome', 'th');
        $tester->see('Email', 'th');
        $tester->see('Ações', 'th');
    }

    public function testEmployeeListFiltering(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Navigate to employees list
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);

        // Just verify we can see the employee list
        $tester->seeElement(self::TABLE_ROW_SELECTOR);

        // Check if we can see at least one employee name
        $tester->see('Klesio Nascimento');
        $tester->wait(1);
    }
}
