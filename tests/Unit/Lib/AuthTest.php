<?php

namespace Tests\Unit\Lib;

use App\Models\Employee;
use App\Models\Role;
use Core\Database\Database;
use Lib\Authentication\Auth;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected static Employee $adminEmployee;
    protected static Employee $hrEmployee;
    protected static Employee $userEmployee;

    protected function setUp(): void
    {
        parent::setUp();
        Database::create();
        Database::migrate();

        $adminRole = new Role(['name' => 'admin', 'description' => 'Administrador']);
        $adminRole->save();

        $hrRole = new Role(['name' => 'hr', 'description' => 'RH']);
        $hrRole->save();

        $userRole = new Role(['name' => 'user', 'description' => 'Usuário']);
        $userRole->save();

        self::$adminEmployee = new Employee([
            'name' => 'Auth Admin Teste',
            'cpf' => '111.222.333-44',
            'email' => 'auth_admin@example.com',
            'role_id' => $adminRole->id,
            'hire_date' => '2023-01-01',
            'status' => 'Active',
        ]);
        self::$adminEmployee->save();

        self::$hrEmployee = new Employee([
            'name' => 'Auth HR Teste',
            'cpf' => '222.333.444-55',
            'email' => 'auth_hr@example.com',
            'role_id' => $hrRole->id,
            'hire_date' => '2023-01-01',
            'status' => 'Active',
        ]);
        self::$hrEmployee->save();

        self::$userEmployee = new Employee([
            'name' => 'Auth User Teste',
            'cpf' => '333.444.555-66',
            'email' => 'auth_user@example.com',
            'role_id' => $userRole->id,
            'hire_date' => '2023-01-01',
            'status' => 'Active',
        ]);
        self::$userEmployee->save();
    }

    protected function tearDown(): void
    {
        Auth::logout();
        Database::drop();
        parent::tearDown();
    }

    public function testLoginAndCheckUser(): void
    {
        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::user());

        Auth::login(self::$userEmployee);

        $this->assertTrue(Auth::check());
        $this->assertNotNull(Auth::user());
        $this->assertEquals(self::$userEmployee->id, Auth::user()->id);
        $this->assertEquals('Auth User Teste', Auth::user()->name);
    }

    public function testLogout(): void
    {
        Auth::login(self::$userEmployee);
        $this->assertTrue(Auth::check());

        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::user());
    }

    public function testUserRoleChecks(): void
    {
        Auth::login(self::$adminEmployee);
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::isAdmin());
        $this->assertFalse(Auth::isHR());
        $this->assertFalse(Auth::isUser());
        Auth::logout();

        Auth::login(self::$hrEmployee);
        $this->assertTrue(Auth::check());
        $this->assertFalse(Auth::isAdmin());
        $this->assertTrue(Auth::isHR());
        $this->assertFalse(Auth::isUser());
        Auth::logout();

        Auth::login(self::$userEmployee);
        $this->assertTrue(Auth::check());
        $this->assertFalse(Auth::isAdmin());
        $this->assertFalse(Auth::isHR());
        $this->assertTrue(Auth::isUser());
        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertFalse(Auth::isAdmin());
        $this->assertFalse(Auth::isHR());
        $this->assertFalse(Auth::isUser());
    }
}
