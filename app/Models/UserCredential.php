<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $password_hash
 * @property string $last_updated
 */
class UserCredential extends Model
{
    protected static string $table = 'UserCredentials';
    protected static array $columns = ['employee_id', 'password_hash', 'last_updated'];

    protected ?string $password = null;
    /**
     * @var null|string Password confirmation for validation
     */
    protected ?string $passwordConfirmation = null;

    public function validates(): void
    {
        Validations::notEmpty('employee_id', $this);
        Validations::notEmpty('password_hash', $this);

        if ($this->password !== null && $this->password !== '') {
            Validations::passwordConfirmation($this);
        }
    }

    /**
     * @return Employee|null
     */
    public function employee(): ?Employee
    {
        return $this->belongsTo(Employee::class, 'employee_id')->get();
    }

    public function __set(string $property, mixed $value): void
    {
        // Se estiver tentando definir password_confirmation, redirecione para passwordConfirmation
        if ($property === 'password_confirmation') {
            $this->passwordConfirmation = $value;
            return;
        }

        parent::__set($property, $value);

        if (
            $property === 'password' &&
            $value !== null && $value !== ''
        ) {
            $this->password_hash = password_hash($value, PASSWORD_DEFAULT);
        }
    }

    public function __get(string $property): mixed
    {
        // Se estiver tentando acessar password_confirmation, retorne passwordConfirmation
        if ($property === 'password_confirmation') {
            return $this->passwordConfirmation;
        }

        return parent::__get($property);
    }

    public function authenticate(string $password): bool
    {
        if ($this->password_hash === null || $this->password_hash === '') {
            return false;
        }

        return password_verify($password, $this->password_hash);
    }
}
