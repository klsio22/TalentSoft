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

    /**
     * Retorna as colunas disponíveis do modelo
     */
    public static function getColumns(): array
    {
        return static::$columns;
    }

    /**
     * Retorna o nome da tabela
     */
    public static function getTable(): string
    {
        return static::$table;
    }

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
        /** @var array<int, UserCredential> $credentials */
        $credentials = $this->hasMany(UserCredential::class, 'employee_id')->get();
        return $credentials[0] ?? null;
    }

    /**
     * @return Role|null
     */
    public function role(): ?Role
    {
        $result = $this->belongsTo(Role::class, 'role_id')->get();
        return $result instanceof Role ? $result : null;
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

    /**
     * Busca funcionários com base em critérios personalizados e retorna com paginação
     *
     * @param string $whereClause Cláusula WHERE SQL (sem a palavra 'WHERE')
     * @param array $params Parâmetros para consulta preparada
     * @param int $page Página atual
     * @param int $perPage Itens por página
     * @param string|null $route Rota usada para gerar links de paginação
     * @return \Lib\Paginator
     */
    public static function findWhere(string $whereClause, array $params = [], int $page = 1, int $perPage = 10, ?string $route = null): \Lib\Paginator
    {
        $pdo = \Core\Database\Database::getDatabaseConn();
        $table = static::$table;
        $attributes = implode(', ', static::$columns);

        // Constrói a consulta SQL com WHERE personalizado
        $sql = "SELECT id, {$attributes} FROM {$table} WHERE {$whereClause} ORDER BY id DESC";

        // Executar a consulta
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value); // bind posicional (índices começam em 1)
        }
        $stmt->execute();

        // Buscar todos os resultados
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calcular total de registros
        $total = count($results);

        // Paginar manualmente
        $offset = ($page - 1) * $perPage;
        $paginatedResults = array_slice($results, $offset, $perPage);

        // Criar objetos do modelo
        $items = [];
        foreach ($paginatedResults as $row) {
            $items[] = new static($row);
        }

        // Criar objeto de paginação personalizado
        return new class($items, $total, $page, $perPage, $route) extends \Lib\Paginator {
            private array $items;
            private int $customTotalOfRegisters;
            private int $customTotalOfPages;
            private int $customTotalOfRegistersOfPage;

            public function __construct(array $items, int $total, int $page, int $perPage, ?string $route)
            {
                $this->items = $items;
                parent::__construct(
                    class: \App\Models\Employee::class,
                    page: $page,
                    per_page: $perPage,
                    table: \App\Models\Employee::getTable(),
                    attributes: \App\Models\Employee::getColumns(),
                    conditions: [],
                    route: $route
                );

                // Armazenar valores personalizados
                $this->customTotalOfRegisters = $total;
                $this->customTotalOfPages = ceil($total / $perPage);
                $this->customTotalOfRegistersOfPage = count($items);
            }

            public function totalOfRegisters(): int
            {
                return $this->customTotalOfRegisters;
            }

            /**
             * Alias para totalOfRegisters() para compatibilidade
             */
            public function total(): int
            {
                return $this->customTotalOfRegisters;
            }

            public function totalOfPages(): int
            {
                return $this->customTotalOfPages;
            }

            public function totalOfRegistersOfPage(): int
            {
                return $this->customTotalOfRegistersOfPage;
            }

            public function items(): array
            {
                return $this->items;
            }

            public function registers(): array
            {
                return $this->items;
            }
        };
    }
}
