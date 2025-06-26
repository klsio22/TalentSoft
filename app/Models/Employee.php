<?php

namespace App\Models;

use App\Interfaces\HasAvatar;
use App\Services\ProfileAvatar;
use Core\Database\ActiveRecord\BelongsToMany;
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
 * @property string|null $avatar_name
 */
class Employee extends Model implements HasAvatar
{
    protected static string $table = 'Employees';
    protected static array $columns = [
    'name',
    'cpf',
    'email',
    'birth_date',
    'role_id',
    'salary',
    'hire_date',
    'status',
    'address',
    'city',
    'state',
    'zipcode',
    'created_at',
    'notes',
    'avatar_name'
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

  /**
   * Filtra funcionários disponíveis para atribuição a um projeto
   * (funcionários que ainda não foram atribuídos ao projeto)
   *
   * @param array<int, object> $allEmployees Todos os funcionários
   * @param array<int, object> $projectEmployees Funcionários já atribuídos ao projeto
   * @return array<int, object> Funcionários disponíveis
   */
    public static function filterAvailableEmployees(array $allEmployees, array $projectEmployees): array
    {
        return array_filter($allEmployees, function ($employee) use ($projectEmployees) {
            foreach ($projectEmployees as $projectEmployee) {
                if ($projectEmployee->id === $employee->id) {
                    return false;
                }
            }
            return true;
        });
    }

  /**
   * Retorna uma instância do serviço ProfileAvatar para este funcionário
   *
   * @return \App\Services\ProfileAvatar
   */
    public function avatar(): ProfileAvatar
    {
        return new ProfileAvatar($this);
    }

    /**
     * Retorna o nome do avatar do funcionário
     *
     * @return string|null Nome do arquivo de avatar ou null
     */
    public function getAvatarName(): ?string
    {
        return $this->avatar_name;
    }

    /**
     * Define o nome do avatar do funcionário
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
