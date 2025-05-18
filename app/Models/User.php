<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $encrypted_password
 * @property string $avatar_name
 */
class User extends Model
{
    protected static string $table = 'users';
    protected static array $columns = ['name', 'email', 'encrypted_password', 'avatar_name'];

    protected ?string $password = null;
    /**
     * @var null|string Password confirmation for validation
     */
    protected ?string $passwordConfirmation = null;

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::notEmpty('email', $this);

        Validations::uniqueness('email', $this);

        if ($this->newRecord()) {
            Validations::passwordConfirmation($this);
        }
    }

    public function authenticate(string $password): bool
    {
        if ($this->encrypted_password == null) {
            return false;
        }

        return password_verify($password, $this->encrypted_password);
    }

    public static function findByEmail(string $email): User | null
    {
        return User::findBy(['email' => $email]);
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
            $this->newRecord() &&
            $value !== null && $value !== ''
        ) {
            $this->encrypted_password = password_hash($value, PASSWORD_DEFAULT);
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
}
