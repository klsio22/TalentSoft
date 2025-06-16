<?php

namespace App\Models;

use Lib\Authentication\Auth;

class EmployeeAuthentication
{
    /**
     * Busca um funcionário pelo ID do usuário
     *
     * @param int $userId ID do usuário
     * @return Employee|null Funcionário encontrado ou null
     */
    public static function findByUserId(int $userId): ?Employee
    {
        // Primeiro, buscar todas as credenciais de usuário
        $credentials = UserCredential::all();

        // Encontrar a credencial que corresponde ao ID do usuário
        foreach ($credentials as $credential) {
            if ($credential->id === $userId) {
                // Retornar o funcionário associado a esta credencial
                return Employee::findById($credential->employee_id);
            }
        }

        return null;
    }

    /**
     * Obtém o funcionário associado ao usuário atual
     *
     * @return Employee|null Funcionário ou null se não encontrado
     */
    public static function getCurrentUserEmployee(): ?Employee
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return null;
        }

        $credential = UserCredential::findById($currentUser->id);
        if (!$credential || !$credential->employee_id) {
            return null;
        }

        return Employee::findById($credential->employee_id);
    }

    /**
     * Autentica um funcionário usando senha
     *
     * @param Employee $employee O funcionário a ser autenticado
     * @param string $password A senha para autenticação
     * @return bool True se a autenticação for bem-sucedida
     */
    public static function authenticate(Employee $employee, string $password): bool
    {
        // Verificar se o funcionário está ativo
        if ($employee->status !== 'Active') {
            return false;
        }

        $credential = $employee->credential();

        if ($credential === null) {
            return false;
        }

        return password_verify($password, $credential->password_hash);
    }
}
