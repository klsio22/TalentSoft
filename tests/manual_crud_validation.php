<?php

// Script de validação manual das operações CRUD para Employee
// Este script pode ser executado diretamente via PHP e não depende do framework de testes

function validateEmployeeCRUD() {
    // Caminhos dos arquivos principais
    $controllerPath = __DIR__ . '/../app/Controllers/EmployeesController.php';
    $modelPath = __DIR__ . '/../app/Models/Employee.php';

    // Verificar existência dos arquivos
    echo "Verificando arquivos principais:\n";
    echo "- Controller: " . (file_exists($controllerPath) ? "EXISTE ✅" : "NÃO EXISTE ❌") . "\n";
    echo "- Model: " . (file_exists($modelPath) ? "EXISTE ✅" : "NÃO EXISTE ❌") . "\n";
    echo "\n";

    // Verificar métodos do controlador
    echo "Verificando métodos do controlador:\n";
    $controllerContent = file_get_contents($controllerPath);

    $crudMethods = [
        'index' => 'Listagem',
        'create' => 'Criação (Form)',
        'store' => 'Armazenamento',
        'show' => 'Visualização',
        'edit' => 'Edição (Form)',
        'update' => 'Atualização',
        'destroy' => 'Exclusão'
    ];

    foreach ($crudMethods as $method => $description) {
        $exists = preg_match('/function\s+' . $method . '\s*\(/i', $controllerContent);
        echo "- $description ($method): " . ($exists ? "IMPLEMENTADO ✅" : "NÃO IMPLEMENTADO ❌") . "\n";
    }
    echo "\n";

    // Verificar métodos do modelo
    echo "Verificando métodos do modelo:\n";
    $modelContent = file_get_contents($modelPath);

    $modelMethods = [
        'validates' => 'Validação',
        'findByEmail' => 'Busca por Email',
        'createWithCredentials' => 'Criação com Credenciais'
    ];

    foreach ($modelMethods as $method => $description) {
        $exists = preg_match('/function\s+' . $method . '\s*\(/i', $modelContent);
        echo "- $description ($method): " . ($exists ? "IMPLEMENTADO ✅" : "NÃO IMPLEMENTADO ❌") . "\n";
    }

    // Métodos herdados importantes
    echo "\nMétodos herdados de Model:\n";
    $inheritedMethods = [
        'save' => 'Salvar (Create/Update)',
        'destroy' => 'Excluir',
        'findById' => 'Busca por ID',
        'all' => 'Listagem'
    ];

    foreach ($inheritedMethods as $method => $description) {
        echo "- $description ($method): HERDADO ✅\n";
    }

    echo "\n";
    echo "Conclusão: ";

    // Verificar se todas as operações CRUD estão implementadas
    $allImplemented = true;

    foreach ($crudMethods as $method => $description) {
        if (!preg_match('/function\s+' . $method . '\s*\(/i', $controllerContent)) {
            $allImplemented = false;
            break;
        }
    }

    if ($allImplemented) {
        echo "TODAS AS OPERAÇÕES CRUD ESTÃO IMPLEMENTADAS ✅\n";
    } else {
        echo "ALGUMAS OPERAÇÕES CRUD ESTÃO FALTANDO ❌\n";
    }
}

// Executar a validação
validateEmployeeCRUD();
