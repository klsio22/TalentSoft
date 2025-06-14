<?php

namespace Tests\Acceptance\Auth;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class AuthenticationRedirectCest extends BaseAcceptanceCest
{
    // Constants for URLs
    private const LOGIN_URL = '/login';
    private const ADMIN_URL = '/admin';
    private const HR_URL = '/hr';
    private const USER_URL = '/user';
    private const EMPLOYEES_URL = '/employees';
    private const PROFILE_URL = '/profile';
    private const DEFAULT_PASSWORD = '123456';

    // Test that unauthenticated users are redirected to login page
    public function testUnauthenticatedRedirectToLogin(AcceptanceTester $tester): void
    {
        // Try to access admin page without authentication
        $tester->amOnPage(self::ADMIN_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);

        // Try to access HR page without authentication
        $tester->amOnPage(self::HR_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);

        // Try to access user page without authentication
        $tester->amOnPage(self::USER_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);

        // Try to access employees page without authentication
        $tester->amOnPage(self::EMPLOYEES_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);

        // Try to access profile page without authentication
        $tester->amOnPage(self::PROFILE_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);
    }

    // Test that admin users are redirected to admin dashboard after login
    public function testAdminRedirectAfterLogin(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'klesio@admin.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');

        // Admin should be redirected to admin dashboard
        $tester->seeInCurrentUrl(self::ADMIN_URL);
        $tester->see('Bem-vindo, Klesio Nascimento!');
    }

    // Test that HR users are redirected to HR dashboard after login
    public function testHRRedirectAfterLogin(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'caio@rh.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');

        // HR should be redirected to HR dashboard
        $tester->seeInCurrentUrl(self::HR_URL);
        $tester->see('Bem-vindo, Caio Silva!');
    }

    // Test that regular users are redirected to user dashboard after login
    public function testUserRedirectAfterLogin(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');

        // User should be redirected to user dashboard
        $tester->seeInCurrentUrl(self::USER_URL);
        $tester->see('Bem-vindo, Flavio Santos!');
    }

    // Test that users are redirected to login page after logout
    public function testRedirectToLoginAfterLogout(AcceptanceTester $tester): void
    {
        // Login as admin
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'klesio@admin.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        $tester->seeInCurrentUrl(self::ADMIN_URL);

        // Logout
        $tester->click('Sair');

        // Should be redirected to login page
        $tester->seeInCurrentUrl(self::LOGIN_URL);
        $tester->wait(1);
        $tester->see('Logout realizado com sucesso');
    }

    // Test access restrictions for regular users
    public function testUserAccessRestrictions(AcceptanceTester $tester): void
    {
        // Login as regular user
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');

        // Try to access admin page as regular user
        $tester->amOnPage(self::ADMIN_URL);
        $tester->dontSeeInCurrentUrl(self::ADMIN_URL);

        // Try to access HR page as regular user
        $tester->amOnPage(self::HR_URL);
        $tester->dontSeeInCurrentUrl(self::HR_URL);

        // Try to access employees page as regular user
        $tester->amOnPage(self::EMPLOYEES_URL);
        $tester->dontSeeInCurrentUrl(self::EMPLOYEES_URL);
    }
}
