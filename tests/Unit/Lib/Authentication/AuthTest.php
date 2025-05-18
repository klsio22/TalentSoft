<?php

namespace Tests\Unit\Lib\Authentication;

use Lib\Authentication\Auth;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    private Employee $employee;

    public function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];

        $userRole = new Role(['name' => 'user', 'description' => 'Usuário comum']);
        $userRole->save();

        $this->employee = new Employee([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'cpf' => '111.111.111-11',
            'role_id' => $userRole->id,
            'hire_date' => date('Y-m-d'),
            'status' => 'Active',
        ]);
        $this->employee->save();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $_SESSION = [];
    }

    public function test_login(): void
    {
        Auth::login($this->employee);

        $this->assertEquals($this->employee->id, $_SESSION['employee']['id']);
    }

    public function test_user(): void
    {
        Auth::login($this->employee);

        $userFromSession = Auth::user();

        $this->assertEquals($this->employee->id, $userFromSession->id);
    }

    public function test_check(): void
    {
        Auth::login($this->employee);

        $this->assertTrue(Auth::check());
    }

    public function test_logout(): void
    {
        Auth::login($this->employee);
        Auth::logout();

        $this->assertFalse(Auth::check());
    }
}
