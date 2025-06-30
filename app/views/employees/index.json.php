<?php

$employeesData = [];

// Obter a lista de funcionários com segurança
$employeesList = [];
if (method_exists($employees, 'registers')) {
    $employeesList = $employees->registers();
} elseif (method_exists($employees, 'items')) {
    $employeesList = $employees->items();
}

foreach ($employeesList as $employee) {
    // Extraindo apenas os dados necessários para a resposta JSON
    $employeesData[] = [
        'id' => $employee->id,
        'name' => $employee->name,
        'email' => $employee->email,
        'role' => $employee->role_id,
        // Adicione outros campos necessários aqui
    ];
}

// Definir os dados de paginação com segurança
$page = 1;
if (method_exists($employees, 'getPage')) {
    $page = $employees->getPage();
}

$perPage = count($employeesList);
if (method_exists($employees, 'perPage')) {
    $perPage = $employees->perPage();
}

$totalOfPages = 1;
if (method_exists($employees, 'totalOfPages')) {
    $totalOfPages = $employees->totalOfPages();
} elseif (method_exists($employees, 'getTotalPages')) {
    $totalOfPages = $employees->getTotalPages();
}

$totalOfRegisters = count($employeesList);
if (method_exists($employees, 'totalOfRegisters')) {
    $totalOfRegisters = $employees->totalOfRegisters();
} elseif (method_exists($employees, 'total')) {
    $totalOfRegisters = $employees->total();
}

$totalOfRegistersOfPage = count($employeesList);
if (method_exists($employees, 'totalOfRegistersOfPage')) {
    $totalOfRegistersOfPage = $employees->totalOfRegistersOfPage();
}

$json['employees'] = $employeesData;
$json['pagination'] = [
    'page'                       => $page,
    'per_page'                   => $perPage,
    'total_of_pages'             => $totalOfPages,
    'total_of_registers'         => $totalOfRegisters,
    'total_of_registers_of_page' => $totalOfRegistersOfPage,
];

// Configurar cabeçalhos para resposta JSON
header('Content-Type: application/json');

// Enviar a resposta JSON
echo json_encode($json);
