<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

class Employee extends Model
{
    protected static string $table = 'Employees';
    protected static array $columns = [
        'name', 'cpf', 'email', 'birth_date', 'role_id',
        'salary', 'hire_date', 'status', 'address',
        'city', 'state', 'zipcode', 'created_at', 'notes'
    ];

    public static function getColumns(): array
    {
        return static::$columns;
    }

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
        $credentials = $this->hasMany(UserCredential::class, 'employee_id')->get();
        return $credentials[0] ?? null;
    }

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

    public static function createWithCredentials(array $data): array
    {
        $processedData = self::preprocessEmployeeData($data);

        $validationResult = self::validateEmployeeData($processedData);
        if (!$validationResult['isValid']) {
            return [false, $validationResult['message'], null];
        }

        return self::createEmployeeWithCredentials($processedData);
    }

    private static function preprocessEmployeeData(array $data): array
    {
        if (isset($data['salary']) && !empty($data['salary'])) {
            $data['salary'] = str_replace(['R$', ' ', '.'], '', $data['salary']);
            $data['salary'] = str_replace(',', '.', $data['salary']);
        } else {
            $data['salary'] = null;
        }

        if (isset($data['hire_date']) && !empty($data['hire_date']) && strtotime($data['hire_date']) !== false) {
            $data['hire_date'] = date('Y-m-d', strtotime($data['hire_date']));
        }

        return $data;
    }

    private static function validateEmployeeData(array $data): array
    {
        $validationErrors = self::checkAllRequiredFields($data);
        if (!empty($validationErrors)) {
            return ['isValid' => false, 'message' => $validationErrors];
        }

        if ($data['password'] !== ($data['password_confirmation'] ?? '')) {
            return ['isValid' => false, 'message' => "A senha e a confirmação de senha não conferem"];
        }

        return ['isValid' => true, 'message' => ''];
    }

    private static function checkAllRequiredFields(array $data): string
    {
        $requiredFields = ['name', 'cpf', 'email', 'role_id', 'hire_date'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return "Campo obrigatório não preenchido: {$field}";
            }
        }

        if (empty($data['password'])) {
            return "A senha é obrigatória para um novo funcionário";
        }

        return '';
    }

    private static function createEmployeeWithCredentials(array $data): array
    {
        $employeeData = [];
        foreach (self::$columns as $field) {
            if (isset($data[$field])) {
                $employeeData[$field] = $data[$field];
            }
        }

        $employee = new Employee($employeeData);

        if (!$employee->save()) {
            // Construir resposta de erro do funcionário
            $errors = [];
            foreach (self::$columns as $field) {
                if ($employee->errors($field)) {
                    $errors[] = "{$field}: " . $employee->errors($field);
                }
            }
            $errorMessage = !empty($errors) ? implode("; ", $errors) : "Erro ao salvar funcionário";
            return [false, $errorMessage, null];
        }

        // Criar credenciais do usuário
        $credentials = new UserCredential([
            'employee_id' => $employee->id,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        $credentials->password = $data['password'];
        $credentials->passwordConfirmation = $data['password_confirmation'] ?? '';

        if (!$credentials->save()) {
            $employee->destroy();
            return [false, "Erro ao salvar credenciais do usuário", null];
        }

        return [true, null, $employee];
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

    public static function findWhere(string $whereClause, array $params = [], int $page = 1, int $perPage = 10, ?string $route = null): \Lib\Paginator
    {
        $pdo = \Core\Database\Database::getDatabaseConn();
        $table = static::$table;
        $attributes = implode(', ', static::$columns);

        $sql = "SELECT id, {$attributes} FROM {$table} WHERE {$whereClause} ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $total = count($results);

        $offset = ($page - 1) * $perPage;
        $paginatedResults = array_slice($results, $offset, $perPage);

        $items = [];
        foreach ($paginatedResults as $row) {
            $items[] = new static($row);
        }

        return new class ($items, $total, $page, $perPage, $route) extends \Lib\Paginator {
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

                $this->customTotalOfRegisters = $total;
                $this->customTotalOfPages = ceil($total / $perPage);
                $this->customTotalOfRegistersOfPage = count($items);
            }

            public function totalOfRegisters(): int
            {
                return $this->customTotalOfRegisters;
            }

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
