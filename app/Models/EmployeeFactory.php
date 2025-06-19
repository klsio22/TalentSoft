<?php

namespace App\Models;

class EmployeeFactory
{
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
        foreach (Employee::getColumns() as $field) {
            if (isset($data[$field])) {
                $employeeData[$field] = $data[$field];
            }
        }

        $employee = new Employee($employeeData);

        if (!$employee->save()) {
            // Construir resposta de erro do funcionário
            $errors = [];
            foreach (Employee::getColumns() as $field) {
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
}
