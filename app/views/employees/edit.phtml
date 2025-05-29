<?php
use Core\Constants\CssClasses;
use Lib\Authentication\Auth;

// Verificar permissões de edição
$canEdit = Auth::isAdmin() || Auth::isHR();
$readonly = $canEdit ? '' : 'readonly';
$disabled = $canEdit ? '' : 'disabled';
$disabledClass = $canEdit ? '' : ' opacity-60 cursor-not-allowed';
?>

<!-- Header Section -->
<div class="glass-effect rounded-2xl shadow-xl p-8 mb-8">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl mb-4 shadow-lg">
            <i class="fas fa-user-edit text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
            Editar Funcionário
        </h1>
        <p class="text-gray-600">Atualize as informações pessoais e profissionais</p>
    </div>
</div>

<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-8">
        <ol class="flex space-x-2 text-sm text-gray-600">
            <li>
                <a href="<?= route('employees.index') ?>" class="hover:text-blue-600 transition-colors duration-200">
                    <i class="fas fa-users mr-1"></i>Funcionários
                </a>
            </li>
            <li class="before:content-['/'] before:mr-2 before:text-gray-400">
                <a href="<?= route('employees.show', ['id' => $employee->id]) ?>"
                   class="hover:text-blue-600 transition-colors duration-200"
                   aria-label="Ver detalhes do funcionário <?= htmlspecialchars($employee->name ?? 'Funcionário') ?>">
                    <?= htmlspecialchars($employee->name ?? 'Funcionário') ?>
                </a>
            </li>
            <li class="before:content-['/'] before:mr-2 before:text-gray-400">
                <span class="text-gray-500">Editar</span>
            </li>
        </ol>
    </nav>

    <?php if (!$canEdit): ?>
        <div class="glass-effect rounded-2xl shadow-xl p-6 mb-8 bg-gradient-to-r from-blue-50 to-indigo-100 border border-blue-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-blue-900 mb-1">
                        Modo Somente Leitura
                    </h3>
                    <p class="text-blue-800">
                        Apenas usuários com perfil Admin ou RH podem editar informações de funcionários.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= route('employees.update') ?>" method="post" class="space-y-8" novalidate>
        <input type="hidden" name="id" value="<?= $employee->id ?>">

        <!-- Dados Pessoais -->
        <div class="glass-effect rounded-2xl shadow-xl p-8">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-user mr-3 text-blue-600"></i>
                    Dados Pessoais
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>Nome completo*
                    </label>
                    <input type="text"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['name']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                           id="name" name="name" value="<?= htmlspecialchars($employee->name) ?>"
                           required <?= $readonly ?>>
                    <?php if(isset($errors['name'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['name'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-blue-600"></i>Email*
                    </label>
                    <input type="email"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['email']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                           id="email" name="email" value="<?= htmlspecialchars($employee->email) ?>"
                           required <?= $readonly ?>>
                    <?php if(isset($errors['email'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['email'] ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-2 text-blue-600"></i>CPF*
                    </label>
                    <input type="text"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['cpf']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                           id="cpf" name="cpf" value="<?= htmlspecialchars($employee->cpf) ?>"
                           placeholder="000.000.000-00" required <?= $readonly ?>>
                    <?php if(isset($errors['cpf'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['cpf'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="birth_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Data de Nascimento*
                    </label>
                    <input type="date"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['birth_date']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                           id="birth_date" name="birth_date" value="<?= htmlspecialchars($employee->birth_date ?? '') ?>"
                           required <?= $readonly ?>>
                    <?php if(isset($errors['birth_date'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['birth_date'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="role_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-2 text-blue-600"></i>Cargo*
                    </label>
                    <select class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['role_id']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                            id="role_id" name="role_id" required <?= $disabled ?>>
                        <option value="" disabled>Selecione...</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role->id ?>" <?= $employee->role_id == $role->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role->description) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(isset($errors['role_id'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['role_id'] ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Dados Profissionais -->
        <div class="glass-effect rounded-2xl shadow-xl p-8">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-briefcase mr-3 text-blue-600"></i>
                    Dados Profissionais
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="salary" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign mr-2 text-blue-600"></i>Salário*
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium">R$</span>
                        </div>
                        <input type="text"
                               class="w-full pl-12 pr-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['salary']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="salary" name="salary" value="<?= $employee->salary ? number_format($employee->salary, 2, ',', '.') : '' ?>"
                               placeholder="0,00"
                               pattern="[0-9,.]*"
                               inputmode="decimal" required <?= $readonly ?>>
                    </div>
                    <?php if(isset($errors['salary'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['salary'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="hire_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check mr-2 text-blue-600"></i>Data de Contratação*
                    </label>
                    <input type="date"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['hire_date']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                           id="hire_date" name="hire_date" value="<?= htmlspecialchars($employee->hire_date ?? '') ?>"
                           required <?= $readonly ?>>
                    <?php if(isset($errors['hire_date'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['hire_date'] ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6">
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-4">
                    <i class="fas fa-toggle-on mr-2 text-blue-600"></i>Status do Funcionário
                </label>
                <div class="flex items-center space-x-4">
                    <!-- Toggle Switch -->
                    <label class="relative inline-flex items-center cursor-pointer<?= $disabledClass ?>">
                        <input type="hidden" name="status" value="Inactive">
                        <input type="checkbox"
                               id="status"
                               name="status"
                               value="Active"
                               class="sr-only peer"
                               <?= $employee->status === 'Active' ? 'checked' : '' ?>
                               <?= $disabled ?>>
                        <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-green-500 peer-checked:to-green-600 transition-all duration-300 shadow-inner"></div>
                    </label>

                    <!-- Status Text -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-600">Status:</span>
                        <span id="status-text" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $employee->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <i class="fas fa-circle mr-1 text-xs"></i>
                            <span id="status-label"><?= $employee->status === 'Active' ? 'Ativo' : 'Inativo' ?></span>
                        </span>
                    </div>
                </div>

                <!-- Script para atualizar o texto do status -->
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Funcionários ativos podem fazer login no sistema
                </p>
            </div>
        </div>

        <!-- Endereço -->
        <div class="glass-effect rounded-2xl shadow-xl p-8">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-map-marker-alt mr-3 text-blue-600"></i>
                    Endereço
                </h2>
            </div>

            <div class="space-y-6">
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-home mr-2 text-blue-600"></i>Endereço
                    </label>
                    <input type="text"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['address']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                           id="address" name="address" value="<?= htmlspecialchars($employee->address ?? '') ?>"
                           placeholder="Rua, número, complemento"
                           <?= $readonly ?>>
                    <?php if(isset($errors['address'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['address'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-city mr-2 text-blue-600"></i>Cidade
                        </label>
                        <input type="text"
                               class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['city']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="city" name="city" value="<?= htmlspecialchars($employee->city ?? '') ?>"
                               placeholder="Digite a cidade"
                               <?= $readonly ?>>
                        <?php if(isset($errors['city'])): ?>
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['city'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map mr-2 text-blue-600"></i>Estado
                        </label>
                        <input type="text"
                               class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['state']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="state" name="state" value="<?= htmlspecialchars($employee->state ?? '') ?>"
                               placeholder="Digite o estado"
                               <?= $readonly ?>>
                        <?php if(isset($errors['state'])): ?>
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['state'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="zipcode" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-mail-bulk mr-2 text-blue-600"></i>CEP
                        </label>
                        <input type="text"
                               class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 <?= isset($errors['zipcode']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="zipcode" name="zipcode" value="<?= htmlspecialchars($employee->zipcode ?? '') ?>"
                               placeholder="00000-000" <?= $readonly ?>>
                        <?php if(isset($errors['zipcode'])): ?>
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['zipcode'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sticky-note mr-2 text-blue-600"></i>Observações
                    </label>
                    <textarea class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none <?= isset($errors['notes']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                              id="notes" name="notes" rows="4"
                              placeholder="Informações adicionais sobre o funcionário..."
                              <?= $readonly ?>><?= htmlspecialchars($employee->notes ?? '') ?></textarea>
                    <?php if(isset($errors['notes'])): ?>
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i><?= $errors['notes'] ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Credenciais de Acesso -->
        <div class="glass-effect rounded-2xl shadow-xl p-8">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-key mr-3 text-blue-600"></i>
                    Credenciais de Acesso
                </h2>
            </div>

            <?php $credential = $employee->credential(); ?>
            <?php if ($credential): ?>
                <div class="glass-effect rounded-lg p-6 mb-6 bg-gradient-to-r from-blue-50 to-indigo-100 border border-blue-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-semibold text-blue-900 mb-1">
                                Credenciais Existentes
                            </h3>
                            <p class="text-blue-800 text-sm">
                                O funcionário já possui credenciais de acesso. Preencha apenas se desejar alterar a senha.
                            </p>
                            <p class="text-blue-600 text-xs mt-1">
                                Última atualização: <?= $credential->last_updated ? date('d/m/Y \à\s H:i', strtotime($credential->last_updated)) : 'Não disponível' ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="glass-effect rounded-lg p-6 mb-6 bg-gradient-to-r from-yellow-50 to-orange-100 border border-yellow-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-semibold text-yellow-900 mb-1">
                                Credenciais Necessárias
                            </h3>
                            <p class="text-yellow-800 text-sm">
                                Este funcionário ainda não possui credenciais de acesso. Defina uma senha para permitir o acesso ao sistema.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>Senha <?= !$credential ? '*' : '' ?>
                    </label>
                    <input type="password"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200<?= $disabledClass ?>"
                           id="password" name="password" placeholder="Digite a nova senha"
                           <?= !$credential ? 'required' : '' ?> <?= $readonly ?>>
                    <p class="mt-2 text-sm text-gray-500 flex items-center">
                        <i class="fas fa-info-circle mr-1"></i>Mínimo de 6 caracteres
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>Confirmar Senha <?= !$credential ? '*' : '' ?>
                    </label>
                    <input type="password"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200<?= $disabledClass ?>"
                           id="password_confirmation" name="password_confirmation" placeholder="Confirme a senha"
                           <?= !$credential ? 'required' : '' ?> <?= $readonly ?>>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-between items-center">
            <a href="<?= route('employees.show', ['id' => $employee->id]) ?>"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-lg hover:from-gray-200 hover:to-gray-300 transition-all duration-200 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Cancelar
            </a>

            <?php if ($canEdit): ?>
                <button type="submit"
                        class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alterações
                </button>
            <?php else: ?>
                <div class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-gray-300 to-gray-400 text-gray-600 rounded-lg cursor-not-allowed font-medium">
                    <i class="fas fa-lock mr-2"></i>
                    Acesso Somente Leitura
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script src="/assets/js/employee-form-validation.js"></script>
<script src="/assets/js/employee-status-toggle.js"></script>
