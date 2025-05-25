<?php
require_once 'config/bootstrap.php';

use Lib\Authentication\Auth;

if (!Auth::check()) {
    echo "Usuário não está logado\n";
    exit;
}

$user = Auth::user();
echo "Usuário atual: " . $user->name . " (ID: " . $user->id . ")\n";
echo "Email: " . $user->email . "\n";

$role = $user->role();
if ($role) {
    echo "Cargo: " . $role->description . "\n";
    echo "Permissões:\n";
    echo "  - Auth::isAdmin(): " . (Auth::isAdmin() ? 'true' : 'false') . "\n";
    echo "  - Auth::isHR(): " . (Auth::isHR() ? 'true' : 'false') . "\n";
    echo "  - Auth::isUser(): " . (Auth::isUser() ? 'true' : 'false') . "\n";

    $canEdit = Auth::isAdmin() || Auth::isHR();
    echo "  - Can Edit Employees: " . ($canEdit ? 'true' : 'false') . "\n";
} else {
    echo "Cargo: Não definido\n";
}
