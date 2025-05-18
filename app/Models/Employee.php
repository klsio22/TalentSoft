<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
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


    public function credential(): ?UserCredential
    {
        /** @var UserCredential|null */
        return $this->hasMany(UserCredential::class, 'employee_id')->get()[0] ?? null;
    }

    public function role(): ?Role
    {
        /** @var Role|null */
        return $this->belongsTo(Role::class, 'role_id')->get();
    }

    public function projects(): BelongsToMany
    {
        return $this->BelongsToMany(
            Project::class,
            'Employee_Projects',
            'employee_id',
            'project_id'
        );
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'employee_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'employee_id');
    }

    public static function findByEmail(string $email): ?Employee
    {
        return self::findBy(['email' => $email]);
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role()->name) === 'admin';
    }

    public function isHR(): bool
    {
        return strtolower($this->role()->name) === 'hr';
    }


    public function isUser(): bool
    {
        return strtolower($this->role()->name) === 'user';
    }

    public function authenticate(string $password): bool
    {
        $credential = $this->credential();

        if ($credential === null) {
            return false;
        }

        return password_verify($password, $credential->password_hash);
    }
}
