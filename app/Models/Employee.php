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

    /**
     * Retorna todos os projetos associados a este funcionário
     *
     * @return BelongsToMany Os projetos associados ao funcionário
     */
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
   * Filtra funcionários disponíveis para atribuição a um projeto
   * (funcionários que ainda não foram atribuídos ao projeto e estão ativos)
   *
   * @param array<int, object> $allEmployees Todos os funcionários
   * @param array<int, object> $projectEmployees Funcionários já atribuídos ao projeto
   * @return array<int, object> Funcionários disponíveis e ativos
   */
    public static function filterAvailableEmployees(array $allEmployees, array $projectEmployees): array
    {
        return array_filter($allEmployees, function ($employee) use ($projectEmployees) {
            // Verificar se o funcionário está ativo
            if ($employee->status !== 'Active') {
                return false;
            }

            // Verificar se o funcionário já está atribuído ao projeto
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
   * O serviço já vem configurado com as validações padrão
   *
   * @return \App\Services\ProfileAvatar
   */
    public function avatar(): ProfileAvatar
    {
        // Utiliza o serviço com as validações padrão definidas no próprio serviço
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
                $success = true; // Considera sucesso se não houver mudança
            } else {
                // Atualiza o valor
                $success = $this->update([
                    'avatar_name' => $avatarName
                ]);

                // Verifica explicitamente se o avatar foi atualizado
                if (!$success && $this->avatar_name === $avatarName) {
                    $success = true;
                }
            }

            return $success;
        } catch (\Exception $e) {
            // Log do erro ou tratamento adequado
            return false;
        }
    }

    public static function paginateWithFilters(
        int $page = 1,
        int $perPage = 10,
        ?string $search = null,
        ?int $roleId = null,
        ?string $status = null
    ): \Lib\Paginator {


    // Adicionar condições baseadas nos filtros
        if (!empty($search)) {
            $conditions['name LIKE'] = "%{$search}%";
        }

        if ($roleId !== null) {
            $conditions['role_id'] = $roleId;
        }

        if (!empty($status)) {
            $conditions['status'] = $status;
        }

    // Criar o Paginator com as condições
        return new \Lib\Paginator(
            class: static::class,
            page: $page,
            per_page: $perPage,
            table: static::$table,
            attributes: static::$columns,
            route: 'employees.index'
        );
    }

  /**
   * Desativa o funcionário no sistema (soft delete)
   * Define o status como 'Inactive' sem remover do banco de dados
   *
   * @return bool True se a desativação foi bem-sucedida, false caso contrário
   */
    public function deactivate(): bool
    {
        return $this->update(['status' => 'Inactive']);
    }
}
