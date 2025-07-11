<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserCredential;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

/**
 * Controller para gerenciamento de funcionários
 * Apenas usuários com roles Admin ou HR podem acessar
 */

class EmployeesController extends Controller
{
    protected string $layout = 'application';

    private const EMPLOYEE_NOT_FOUND = 'Funcionário não encontrado';
    private const ACCESS_DENIED = 'Acesso negado';
    private const EMPLOYEE_CREATED = 'Funcionário cadastrado com sucesso!';
    private const EMPLOYEE_UPDATED = 'Funcionário atualizado com sucesso!';
    private const EMPLOYEE_DEACTIVATED = 'Funcionário desativado com sucesso!';
    private const CREDENTIAL_ERROR = 'Erro ao salvar credenciais do usuário';

    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        } elseif (!Auth::isHR() && !Auth::isAdmin()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
        }
    }

    public function index(): void
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
        $roleId = filter_input(INPUT_GET, 'role', FILTER_VALIDATE_INT);
        // Padrão: mostrar apenas funcionários ativos
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Active';
        $perPage = 10;

        // Usar o método do modelo Employee para paginação com filtros
        $employees = Employee::paginateWithFilters(
            $page,
            $perPage,
            $search,
            $roleId,
            $status
        );

        $roles = Role::all();
        $title = 'Lista de Funcionários';

        // Preparar parâmetros de consulta para URLs de paginação
        $queryParams = $this->prepareQueryParams($search, $roleId, $status);

        $this->render('employees/index', compact('employees', 'title', 'roles', 'queryParams'));
    }

    /**
     * Prepara os parâmetros de consulta para URLs de paginação
     * @return array<string, string|int>
     */
    private function prepareQueryParams(?string $search, ?int $roleId, ?string $status): array
    {
        $queryParams = [];
        if ($search) {
            $queryParams['search'] = $search;
        }
        if ($roleId) {
            $queryParams['role'] = $roleId;
        }
        if ($status) {
            $queryParams['status'] = $status;
        }
        return $queryParams;
    }


    public function create(): void
    {
        $employee = new Employee();
        $roles = Role::all();
        $title = 'Novo Funcionário';

        $this->render('employees/create', compact('employee', 'roles', 'title'));
    }

    public function store(Request $request): void
    {
        try {
            $data = $request->getParams();

            // Delegar toda a lógica de validação e criação para o modelo
            list($success, $errorMessage) = Employee::createWithCredentials($data);

            if ($success) {
                FlashMessage::success(self::EMPLOYEE_CREATED);
                $this->redirectTo(route('employees.index'));
                return;
            }

            // Se chegou aqui, houve erro
            FlashMessage::danger($errorMessage);
            $this->renderCreateForm($data);
        } catch (\Exception $e) {
            // Mostrar mensagem amigável para o usuário
            FlashMessage::danger("Erro interno ao cadastrar funcionário. Por favor, tente novamente.");
            $this->renderCreateForm($request->getParams());
        }
    }

    /**
     * Helper para renderizar o formulário de criação
     * @param array<string, mixed> $data Form data
     * @param array<string, string> $errors Validation errors
     */
    private function renderCreateForm(array $data = [], array $errors = []): void
    {
        $roles = Role::all();
        $title = 'Novo Funcionário';

        // Filtrar apenas os campos do modelo Employee
        $employeeData = [];
        $employeeFields = Employee::getColumns();
        foreach ($employeeFields as $field) {
            if (isset($data[$field])) {
                $employeeData[$field] = $data[$field];
            }
        }

        $employee = new Employee($employeeData);

        // Adicionar campos extra necessários para o formulário (não do model)
        $formData = [
            'password' => $data['password'] ?? '',
            'password_confirmation' => $data['password_confirmation'] ?? ''
        ];

        $this->render('employees/create', compact('employee', 'roles', 'title', 'errors', 'formData'));
    }

    public function show(Request $request): void
    {
        $id = $request->getParam('id');

        if (!$id) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $employee = Employee::findById((int)$id);

        if (!$employee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $title = 'Detalhes do Funcionário';

        $this->render('employees/show', compact('employee', 'title'));
    }

    /**
     * Mostra o formulário para editar um funcionário
     */
    public function edit(Request $request): void
    {
        $id = $request->getParam('id');

        if (!$id) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $employee = Employee::findById((int)$id);

        if (!$employee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $roles = Role::all();
        $title = 'Editar Funcionário';

        $this->render('employees/edit', compact('employee', 'roles', 'title'));
    }

    /**
     * Atualiza um funcionário no banco de dados
     */
    public function update(Request $request): void
    {
        // Verificar permissões antes de qualquer processamento
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $id = $request->getParam('id');

        if (!$id) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $employee = Employee::findById((int)$id);

        if (!$employee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $data = $request->getParams();

        if ($this->updateEmployee($employee, $data)) {
            FlashMessage::success(self::EMPLOYEE_UPDATED);
            $this->redirectTo(route('employees.show', ['id' => $employee->id]));
        } else {
            $this->renderEditFormWithErrors($employee);
        }
    }

    /**
     * Desativa um funcionário no sistema (soft delete)
     */
    public function destroy(Request $request): void
    {
        $id = $request->getParam('id');

        if (!$id) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        $employee = Employee::findById((int)$id);

        if (!$employee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        // Não permitir desativar o próprio usuário logado
        if ($employee->id === Auth::user()->id) {
            FlashMessage::danger('Não é possível desativar seu próprio usuário');
            $this->redirectTo(route('employees.index'));
            return;
        }

        // Desativar o funcionário usando o método específico do modelo
        if ($employee->deactivate()) {
            FlashMessage::success(self::EMPLOYEE_DEACTIVATED);
        } else {
            FlashMessage::danger('Erro ao desativar funcionário');
        }

        $this->redirectTo(route('employees.index'));
    }

    /**
     * Atualiza os dados do funcionário e suas credenciais
     * @param Employee $employee
     * @param array<string, mixed> $data
     * @return bool
     */
    private function updateEmployee(Employee $employee, array $data): bool
    {
        $this->preprocessEmployeeUpdateData($data);
        $this->updateEmployeeAttributes($employee, $data);

        if (!$employee->save()) {
            return false;
        }

        return $this->updateEmployeeCredentials($employee, $data);
    }

    /**
     * Processa os dados de atualização do funcionário
     * @param array<string, mixed> &$data
     */
    private function preprocessEmployeeUpdateData(array &$data): void
    {
        // Processar salário (remover formatação)
        if (isset($data['salary']) && !empty($data['salary'])) {
            $data['salary'] = str_replace(['R$', ' ', '.'], '', $data['salary']);
            $data['salary'] = str_replace(',', '.', $data['salary']);
            // Converter para float para garantir formato correto
            $data['salary'] = floatval($data['salary']);
        }

        // Processar data de contratação
        if (isset($data['hire_date']) && !empty($data['hire_date']) && strtotime($data['hire_date']) !== false) {
            $data['hire_date'] = date('Y-m-d', strtotime($data['hire_date']));
        }

        // Processar data de nascimento
        if (isset($data['birth_date']) && !empty($data['birth_date']) && strtotime($data['birth_date']) !== false) {
            $data['birth_date'] = date('Y-m-d', strtotime($data['birth_date']));
        }

        // Remover campos vazios que não devem ser atualizados (exceto notes e salary)
        foreach ($data as $key => $value) {
            if (empty($value) && !in_array($key, ['notes', 'salary'])) {
                unset($data[$key]);
            }
        }
    }

    /**
     * Atualiza os atributos do funcionário
     * @param Employee $employee
     * @param array<string, mixed> $data
     */
    private function updateEmployeeAttributes(Employee $employee, array $data): void
    {
        $employeeColumns = Employee::getColumns();
        foreach ($data as $key => $value) {
            if (in_array($key, $employeeColumns)) {
                $employee->$key = $value;
            }
        }
    }

    /**
     * Atualiza ou cria credenciais do funcionário se necessário
     * @param Employee $employee
     * @param array<string, mixed> $data
     * @return bool
     */
    private function updateEmployeeCredentials(Employee $employee, array $data): bool
    {
        if (!isset($data['password']) || empty($data['password'])) {
            return true;
        }

        $credential = $employee->credential();

        if ($credential) {
            return $this->updateExistingCredential($credential, $data);
        }

        return $this->createNewCredential($employee, $data);
    }

    /**
     * Atualiza uma credencial existente
     * @param UserCredential $credential
     * @param array<string, mixed> $data
     * @return bool
     */
    private function updateExistingCredential(UserCredential $credential, array $data): bool
    {
        // Use magic __set method to set password
        $credential->password = $data['password'];
        $credential->password_confirmation = $data['password_confirmation'] ?? '';
        $credential->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $credential->last_updated = date(self::DATETIME_FORMAT);

        if (!$credential->save()) {
            FlashMessage::danger(self::CREDENTIAL_ERROR);
            return false;
        }

        return true;
    }

    /**
     * Cria uma nova credencial para o funcionário
     * @param Employee $employee
     * @param array<string, mixed> $data
     * @return bool
     */
    private function createNewCredential(Employee $employee, array $data): bool
    {
        $credentials = new UserCredential([
            'employee_id' => $employee->id,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'last_updated' => date(self::DATETIME_FORMAT)
        ]);

        // Use magic __set method to set password
        $credentials->password = $data['password'];
        $credentials->password_confirmation = $data['password_confirmation'] ?? '';

        if (!$credentials->save()) {
            FlashMessage::danger(self::CREDENTIAL_ERROR);
            return false;
        }

        return true;
    }

    /**
     * Renderiza o formulário de edição com erros
     */
    private function renderEditFormWithErrors(Employee $employee): void
    {
        $roles = Role::all();
        $title = 'Editar Funcionário';

        // Passar os erros de validação para a view
        $errors = [];
        $employeeColumns = Employee::getColumns();
        foreach ($employeeColumns as $field) {
            if ($employee->errors($field)) {
                $errors[$field] = $employee->errors($field);
            }
        }

        $this->render('employees/edit', compact('employee', 'roles', 'title', 'errors'));
    }
}
