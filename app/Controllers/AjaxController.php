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

        // Buscar projetos
        $projects = $this->queryEmployeeProjects($employeeId);

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
     * Busca projetos do funcionário via SQL
     */
    private function queryEmployeeProjects(int $employeeId): array
    {
        try {
            $pdo = \Core\Database\Database::getDatabaseConn();

            $sql = "
                SELECT
                    p.id as id,
                    p.name as name,
                    p.description as description,
                    p.status as status,
                    ep.role as role
                FROM
                    Projects p
                INNER JOIN Employee_Projects ep ON p.id = ep.project_id
                WHERE
                    ep.employee_id = :employee_id
                ORDER BY p.name ASC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':employee_id', $employeeId, \PDO::PARAM_INT);
            $stmt->execute();

            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Formatar dados
            $formattedProjects = [];
            foreach ($projects as $project) {
                $formattedProjects[] = [
                    'id' => $project['id'],
                    'name' => $project['name'],
                    'description' => $project['description'] ?? 'Sem descrição',
                    'status' => $project['status'] ?? 'Ativo',
                    'role' => $project['role'] ?? 'Membro da equipe'
                ];
            }

            return $formattedProjects;
        } catch (\Exception $e) {
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
