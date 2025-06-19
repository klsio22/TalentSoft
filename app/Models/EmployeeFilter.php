<?php

namespace App\Models;

class EmployeeFilter
{
    /**
     * Filtra funcionários com base em critérios de busca
     *
     * @param array<int, Employee> $allEmployees Lista de todos os funcionários
     * @param string|null $search Termo de busca para nome ou email
     * @param int|null $roleId ID do cargo para filtrar
     * @param string|null $status Status do funcionário para filtrar
     * @return array<int, Employee> Lista filtrada de funcionários
     */
    public static function filter(array $allEmployees, ?string $search, ?int $roleId, ?string $status): array
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
}
