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
     * Get the employee's role for a specific project
     *
     * @param int $projectId ID of the project
     * @return string|null The role of the employee in the project, or null if not found
     */
    public function getRoleForProject(int $projectId): ?string
    {
        $employeeProject = EmployeeProject::findBy([
            'employee_id' => $this->id,
            'project_id' => $projectId
        ]);

        return $employeeProject ? $employeeProject->role : null;
    }

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

    /**
     * @param array<string, mixed> $data
     * @return array<int|string, mixed>
     */
    public static function createWithCredentials(array $data): array
    {
        return EmployeeFactory::createWithCredentials($data);
    }

    public static function findByEmail(string $email): ?Employee
    {
        return self::findBy(['email' => $email]);
    }

    /**
     * Busca um funcionário pelo ID do usuário
     *
     * @param int $userId ID do usuário
     * @return Employee|null Funcionário encontrado ou null
     */
    public static function findByUserId(int $userId): ?Employee
    {
        return EmployeeAuthentication::findByUserId($userId);
    }

    /**
     * Obtém o funcionário associado ao usuário atual
     *
     * @return Employee|null Funcionário ou null se não encontrado
     */
    public static function getCurrentUserEmployee(): ?Employee
    {
        return EmployeeAuthentication::getCurrentUserEmployee();
    }

    public function isAdmin(): bool
    {
        $role = $this->role();
        if (!$role) {
            return false;
        }
        return strtolower($role->name) === 'admin';
    }

    public function isHR(): bool
    {
        $role = $this->role();
        if (!$role) {
            return false;
        }
        return strtolower($role->name) === 'hr';
    }

    public function isUser(): bool
    {
        $role = $this->role();
        if (!$role) {
            return false;
        }
        return strtolower($role->name) === 'user';
    }

    public function authenticate(string $password): bool
    {
        return EmployeeAuthentication::authenticate($this, $password);
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
        return EmployeeFilter::filter($allEmployees, $search, $roleId, $status);
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
        return EmployeePaginator::paginate($employees, $page, $perPage);
    }
}
