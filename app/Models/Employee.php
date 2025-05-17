<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property string $name
 * @property string $cpf
 * @property string $email
 * @property string $birth_date
 * @property int $role_id
 * @property float $salary
 * @property string $hire_date
 * @property string $status
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zipcode
 * @property string $created_at
 * @property string $notes
 */
class Employee extends Model
{
    protected static string $table = 'Employees';
    protected static array $columns = [
        'name', 'cpf', 'email', 'birth_date', 'role_id',
        'salary', 'hire_date', 'status', 'address',
        'city', 'state', 'zipcode', 'created_at', 'notes'
    ];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::notEmpty('cpf', $this);
        Validations::notEmpty('email', $this);
        Validations::notEmpty('role_id', $this);
        Validations::notEmpty('hire_date', $this);

        Validations::uniqueness('email', $this);
        Validations::uniqueness('cpf', $this);
    }

    /**
     * Obtém a credencial do funcionário
     */
    public function credential()
    {
        return $this->hasMany(UserCredential::class, 'employee_id')->get()[0] ?? null;
    }

    /**
     * Obtém o papel do funcionário
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id')->get();
    }

    /**
     * Obtém os projetos do funcionário
     */
    public function projects()
    {
        return $this->BelongsToMany(
            Project::class,
            'Employee_Projects',
            'employee_id',
            'project_id'
        );
    }

    /**
     * Obtém as notificações do funcionário
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'employee_id');
    }

    /**
     * Obtém as aprovações do funcionário
     */
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'employee_id');
    }

    /**
     * Encontra um funcionário por email
     */
    public static function findByEmail(string $email): ?Employee
    {
        return self::findBy(['email' => $email]);
    }

    /**
     * Verifica se o funcionário é admin
     */
    public function isAdmin(): bool
    {
        return $this->role()->name === 'admin';
    }

    /**
     * Verifica se o funcionário é de recursos humanos
     */
    public function isHR(): bool
    {
        return $this->role()->name === 'hr';
    }

    /**
     * Verifica se o funcionário é um usuário comum
     */
    public function isUser(): bool
    {
        return $this->role()->name === 'user';
    }

    /**
     * Autentica o funcionário com a senha fornecida
     */
    public function authenticate(string $password): bool
    {
        $credential = $this->credential();

        if ($credential === null) {
            return false;
        }

        return password_verify($password, $credential->password_hash);
    }
}
