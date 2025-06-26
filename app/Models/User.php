<?php

namespace App\Models;

use App\Interfaces\HasAvatar;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $encrypted_password
 * @property string|null $avatar_name
 */
class User extends Model implements HasAvatar
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

    /**
     * Retorna o nome do avatar do usuário
     *
     * @return string|null Nome do arquivo de avatar ou null
     */
    public function getAvatarName(): ?string
    {
        return $this->avatar_name;
    }

    /**
     * Define o nome do avatar do usuário
     *
     * @param string|null $avatarName Nome do arquivo de avatar ou null para remover
     * @return bool Se a atualização foi bem-sucedida
     */
    public function setAvatarName(?string $avatarName): bool
    {
        try {
            // Verifica se o avatar_name não mudou
            if ($this->avatar_name === $avatarName) {
                return true; // Considera sucesso se não houver mudança
            }

            // Atualiza o valor
            $result = $this->update([
                'avatar_name' => $avatarName
            ]);

            // Verifica explicitamente se o avatar foi atualizado
            // Se o rowCount for 0 mas o valor estiver atualizado na instância,
            // consideramos sucesso
            if (!$result && $this->avatar_name === $avatarName) {
                return true;
            }

            return $result;
        } catch (\Exception $e) {
            // Log do erro ou tratamento adequado
            return false;
        }
    }
}
