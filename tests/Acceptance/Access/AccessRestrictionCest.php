<?php

namespace Tests\Acceptance\Access;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class AccessRestrictionCest extends BaseAcceptanceCest
{
    private const LOGIN_HEADING = 'Acesso ao Sistema';
    private const USER_HOME_URL = '/user';
    private const HR_HOME_URL = '/hr';
    private const ADMIN_HOME_URL = '/admin';
    private const LOGIN_URL = '/login';
    private const DEFAULT_PASSWORD = '123456';
    public function testUnauthenticatedAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::ADMIN_HOME_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);
        $tester->see(self::LOGIN_HEADING, 'h2');

        $tester->amOnPage(self::HR_HOME_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);
        $tester->see(self::LOGIN_HEADING, 'h2');

        $tester->amOnPage(self::USER_HOME_URL);
        $tester->seeInCurrentUrl(self::LOGIN_URL);
        $tester->see(self::LOGIN_HEADING, 'h2');
    }

    public function testUserRestrictedAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'flavio@user.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        $tester->seeInCurrentUrl(self::USER_HOME_URL);

        $tester->amOnPage(self::ADMIN_HOME_URL);
        $tester->dontSeeInCurrentUrl(self::ADMIN_HOME_URL);
        $tester->seeInCurrentUrl(self::USER_HOME_URL);

        $tester->amOnPage(self::HR_HOME_URL);
        $tester->dontSeeInCurrentUrl(self::HR_HOME_URL);
        $tester->seeInCurrentUrl(self::USER_HOME_URL);

        $tester->wait(2.5);
        $tester->click('Sair');
        $tester->seeInCurrentUrl(self::LOGIN_URL);
    }

    public function testHRRestrictedAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'caio@rh.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        
        // HR users are redirected to /hr page
        $tester->seeInCurrentUrl(self::HR_HOME_URL);

        $tester->amOnPage(self::ADMIN_HOME_URL);
        $tester->dontSeeInCurrentUrl(self::ADMIN_HOME_URL);
        $tester->seeInCurrentUrl(self::USER_HOME_URL);

        $tester->amOnPage(self::HR_HOME_URL);
        $tester->seeInCurrentUrl(self::HR_HOME_URL);

        $tester->wait(2.5);
        $tester->click('Sair');
        $tester->seeInCurrentUrl(self::LOGIN_URL);
    }

    public function testAdminFullAccess(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);
        $tester->fillField('email', 'klesio@admin.com');
        $tester->fillField('password', self::DEFAULT_PASSWORD);
        $tester->click('Entrar');
        $tester->seeInCurrentUrl(self::ADMIN_HOME_URL);

        $tester->amOnPage(self::ADMIN_HOME_URL);
        $tester->seeInCurrentUrl(self::ADMIN_HOME_URL);

        $tester->amOnPage(self::HR_HOME_URL);
        $tester->seeInCurrentUrl(self::HR_HOME_URL);

        $tester->amOnPage(self::USER_HOME_URL);
        $tester->seeInCurrentUrl(self::USER_HOME_URL);

        $tester->wait(2.5);
        $tester->click('Sair');
        $tester->seeInCurrentUrl(self::LOGIN_URL);
    }
}
