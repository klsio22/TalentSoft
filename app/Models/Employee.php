<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property string $name
 * @property string $cpf
 * @property string $email
 * @property string|null $birth_date
 * @property int $role_id
 * @property float|null $salary
 * @property string $hire_date
 * @property string|null $status
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zipcode
 * @property string|null $created_at
 * @property string|null $notes
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
     * @return array<int, string>
     */
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


    /**
     * @return UserCredential|null
     */
    public function credential(): ?UserCredential
    {
        $credentials = $this->hasMany(UserCredential::class, 'employee_id')->get();
        if (isset($credentials[0])) {
            return UserCredential::findById($credentials[0]->id);
        }
        return null;
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

    /**
     * @param array<string, mixed> $data
     * @return array<int|string, mixed>
     */
    public static function createWithCredentials(array $data): array
    {
        $processedData = self::preprocessEmployeeData($data);

        $validationResult = self::validateEmployeeData($processedData);
        if (!$validationResult['isValid']) {
            return [false, $validationResult['message'], null];
        }

        return self::createEmployeeWithCredentials($processedData);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
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

    /**
     * @param array<string, mixed> $data
     * @return array{isValid: bool, message: string}
     */
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

    /**
     * @param array<string, mixed> $data
     */
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

    /**
     * @param array<string, mixed> $data
     * @return array{0: bool, 1: string, 2: ?Employee}
     */
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

        // Use magic __set method to set password
        $credentials->password = $data['password'];
        $credentials->password_confirmation = $data['password_confirmation'] ?? '';

        if (!$credentials->save()) {
            $employee->destroy();
            return [false, "Erro ao salvar credenciais do usuário", null];
        }

        return [true, '', $employee];
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
     * Filtra funcionários com base em critérios de busca
     *
     * @param array<int, Employee> $allEmployees Lista de todos os funcionários
     * @param string|null $search Termo de busca para nome ou email
     * @param int|null $roleId ID do cargo para filtrar
     * @param string|null $status Status do funcionário para filtrar
     * @return array<int, Employee> Lista filtrada de funcionários
     */
    public static function filterEmployees(array $allEmployees, ?string $search, ?int $roleId, ?string $status): array
    {
        $filteredEmployees = [];

        foreach ($allEmployees as $employee) {
            $matchesSearch = true;
            $matchesRole = true;
            $matchesStatus = true;

            if ($search) {
                $matchesSearch = (stripos($employee->name, $search) !== false ||
                                 stripos($employee->email, $search) !== false);
            }

            if ($roleId) {
                $matchesRole = $employee->role_id == $roleId;
            }

            if ($status) {
                $matchesStatus = $employee->status === $status;
            }

            if ($matchesSearch && $matchesRole && $matchesStatus) {
                $filteredEmployees[] = $employee;
            }
        }

        return $filteredEmployees;
    }

    /**
     * Cria um objeto de paginação a partir de uma lista de funcionários
     *
     * @param array<int, Employee> $employees Lista de funcionários
     * @param int $page Número da página atual
     * @param int $perPage Itens por página
     * @return object Objeto de paginação
     */
    public static function createPaginator(array $employees, int $page, int $perPage): object
    {
        $total = count($employees);
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages ?: 1)); // Garantir que a página é válida
        $offset = ($page - 1) * $perPage;
        $paginatedEmployees = array_slice($employees, $offset, $perPage);

        return new class ($paginatedEmployees, $total, $page, $perPage) {
            /** @var array<int, \App\Models\Employee> */
            private array $items;
            private int $total;
            private int $page;
            private int $perPage;

            /**
             * @param array<int, \App\Models\Employee> $items
             * @param int $total
             * @param int $page
             * @param int $perPage
             */
            public function __construct(array $items, int $total, int $page, int $perPage)
            {
                $this->items = $items;
                $this->total = $total;
                $this->page = $page;
                $this->perPage = $perPage;
            }

            /**
             * @return array<int, \App\Models\Employee>
             */
            public function items(): array
            {
                return $this->items;
            }

            public function total(): int
            {
                return $this->total;
            }

            public function getPage(): int
            {
                return $this->page;
            }

            public function perPage(): int
            {
                return $this->perPage;
            }

            public function getTotalPages(): int
            {
                return (int)ceil($this->total / $this->perPage);
            }

            public function totalOfRegisters(): int
            {
                return $this->total;
            }

            public function totalOfRegistersOfPage(): int
            {
                return count($this->items);
            }
        };
    }
}
