<?php

namespace App\Controllers;

use App\Models\Employee;
use Core\Http\Controllers\Controller;
use Core\Http\Request;

class AjaxController extends Controller
{
    /**
     * Retorna os projetos associados a um funcionário em formato JSON
     *
     * @param mixed $id ID do funcionário
     * @return void
     */
    public function getEmployeeProjects($id)
    {
        // Extrair ID do funcionário
        $employeeId = $this->extractEmployeeId($id);

        // Validar ID
        if ($employeeId <= 0) {
            $this->sendJsonResponse(['error' => 'ID do funcionário inválido'], 400);
        }

        // Buscar funcionário
        $employee = Employee::findById($employeeId);
        if (!$employee) {
            $this->sendJsonResponse(['error' => 'Funcionário não encontrado'], 404);
        }

        // Buscar projetos usando o framework ORM
        $projects = $this->getEmployeeProjectsUsingORM($employee);

        // Formatar resposta
        $response = [
            'success' => true,
            'employee' => $employee->name,
            'employee_id' => $employee->id,
            'projects' => $projects,
            'project_count' => count($projects)
        ];

        $this->sendJsonResponse($response);
    }

    /**
     * Extrai o ID do funcionário do parâmetro
     */
    private function extractEmployeeId($id): int
    {
        if ($id instanceof Request) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('/\/ajax\/employee\/(\d+)\/projects/', $uri, $matches)) {
                return intval($matches[1]);
            }
            return 0;
        }

        return intval($id);
    }

    /**
     * Busca projetos do funcionário usando o framework ORM
     *
     * @param Employee $employee Instância do funcionário
     * @return array<int, array<string, mixed>> Lista formatada de projetos
     */
    private function getEmployeeProjectsUsingORM(Employee $employee): array
    {
        try {
            // Usar o relacionamento BelongsToMany para buscar os projetos
            $projectsRelation = $employee->projects();
            $projects = $projectsRelation->get();

            // Formatar dados para a resposta
            $formattedProjects = [];
            foreach ($projects as $project) {
                // Buscar o papel do funcionário neste projeto específico
                $role = $employee->getRoleForProject($project->id);

                $formattedProjects[] = [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description ?? 'Sem descrição',
                    'status' => $project->status ?? 'Ativo',
                    'role' => $role ?? 'Membro da equipe'
                ];
            }

            return $formattedProjects;
        } catch (\Exception $e) {
            // Em caso de erro, retornar array vazio
            return [];
        }
    }

    /**
     * Envia resposta JSON
     */
    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($data);
        exit;
    }
}
