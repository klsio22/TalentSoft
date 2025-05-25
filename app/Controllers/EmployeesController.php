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

    // Constantes para mensagens
    private const EMPLOYEE_NOT_FOUND = 'Funcionário não encontrado';
    private const ACCESS_DENIED = 'Acesso negado';
    private const EMPLOYEE_CREATED = 'Funcionário cadastrado com sucesso!';
    private const EMPLOYEE_UPDATED = 'Funcionário atualizado com sucesso!';
    private const EMPLOYEE_DELETED = 'Funcionário removido com sucesso!';
    private const CREDENTIAL_ERROR = 'Erro ao salvar credenciais do usuário';

    // Constante para formato de data
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct()
    {
        parent::__construct();

        // Apenas Admin e RH podem acessar este controller
        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        } elseif (!Auth::isHR() && !Auth::isAdmin()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
        }
    }

    /**
     * Exibe a lista de funcionários com opções de filtro
     */
    public function index(): void
    {
        // Parâmetros de pesquisa
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
        $roleId = filter_input(INPUT_GET, 'role', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        // Construir a condição WHERE
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($roleId) {
            $where[] = "role_id = ?";
            $params[] = $roleId;
        }

        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        // Buscar funcionários com ou sem filtros
        if (!empty($where)) {
            $whereClause = implode(' AND ', $where);
            $employees = Employee::findWhere($whereClause, $params, $page, 10, 'employees.index');
        } else {
            $employees = Employee::paginate($page, 10, 'employees.index');
        }

        // Buscar todos os cargos para o filtro de seleção
        $roles = Role::all();
        $title = 'Lista de Funcionários';

        // Manter os parâmetros de filtro para a paginação
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

        $this->render('employees/index', compact('employees', 'title', 'roles', 'queryParams'));
    }

    /**
     * Mostra o formulário para criar um novo funcionário
     */
    public function create(): void
    {
        $employee = new Employee();
        $roles = Role::all();
        $title = 'Novo Funcionário';

        $this->render('employees/create', compact('employee', 'roles', 'title'));
    }    /**
     * Armazena um novo funcionário no banco de dados
     */
    public function store(Request $request): void
    {
        $data = $request->getParams();
        $employee = new Employee($data);

        if ($employee->save()) {
            // Se o funcionário foi salvo com sucesso, criar credenciais
            if (isset($data['password']) && !empty($data['password'])) {
                $credentials = new UserCredential([
                    'employee_id' => $employee->id,
                    'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                    'last_updated' => date(self::DATETIME_FORMAT)
                ]);

                // Configurar senha e confirmação
                $credentials->password = $data['password'];
                $credentials->passwordConfirmation = $data['password_confirmation'] ?? '';

                if (!$credentials->save()) {
                    FlashMessage::danger(self::CREDENTIAL_ERROR);
                    $this->redirectTo(route('employees.edit', ['id' => $employee->id]));
                    return;
                }
            }

            FlashMessage::success(self::EMPLOYEE_CREATED);
            $this->redirectTo(route('employees.index'));
        } else {
            // Se houver erros, exibe o formulário novamente com os erros
            $roles = Role::all();
            $title = 'Novo Funcionário';

            // Passar os erros de validação para a view
            $errors = [];
            $employeeColumns = Employee::getColumns();
            foreach ($employeeColumns as $field) {
                if ($employee->errors($field)) {
                    $errors[$field] = $employee->errors($field);
                }
            }

            $this->render('employees/create', compact('employee', 'roles', 'title', 'errors'));
        }
    }    /**
     * Exibe um funcionário específico
     */
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
    }    /**
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
     * Remove um funcionário do banco de dados
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

        // Não permitir excluir o próprio usuário logado
        if ($employee->id === Auth::user()->id) {
            FlashMessage::danger('Não é possível excluir seu próprio usuário');
            $this->redirectTo(route('employees.index'));
            return;
        }

        // Remover credenciais primeiro (restrição de chave estrangeira)
        $credential = $employee->credential();
        if ($credential) {
            $credential->destroy();
        }

        if ($employee->destroy()) {
            FlashMessage::success(self::EMPLOYEE_DELETED);
        } else {
            FlashMessage::danger('Erro ao excluir funcionário');
        }

        $this->redirectTo(route('employees.index'));
    }

    /**
     * Atualiza os dados do funcionário e suas credenciais
     */
    private function updateEmployee(Employee $employee, array $data): bool
    {
        $this->sanitizeEmployeeData($data);
        $this->updateEmployeeAttributes($employee, $data);

        if (!$employee->save()) {
            return false;
        }

        return $this->updateEmployeeCredentials($employee, $data);
    }

    /**
     * Remove campos vazios que não devem ser atualizados
     */
    private function sanitizeEmployeeData(array &$data): void
    {
        foreach ($data as $key => $value) {
            if (empty($value) && $key !== 'notes') { // notes pode ser vazio
                unset($data[$key]);
            }
        }
    }

    /**
     * Atualiza os atributos do funcionário
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
     * Atualiza credencial existente
     */
    private function updateExistingCredential(UserCredential $credential, array $data): bool
    {
        $credential->password = $data['password'];
        $credential->passwordConfirmation = $data['password_confirmation'] ?? '';
        $credential->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $credential->last_updated = date(self::DATETIME_FORMAT);

        if (!$credential->save()) {
            FlashMessage::danger('Erro ao atualizar senha');
            return false;
        }

        return true;
    }

    /**
     * Cria nova credencial
     */
    private function createNewCredential(Employee $employee, array $data): bool
    {
        $credentials = new UserCredential([
            'employee_id' => $employee->id,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'last_updated' => date(self::DATETIME_FORMAT)
        ]);

        $credentials->password = $data['password'];
        $credentials->passwordConfirmation = $data['password_confirmation'] ?? '';

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
