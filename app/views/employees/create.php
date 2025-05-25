<?php
use Core\Constants\CssClasses;
?>

<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-6">
        <ol class="flex space-x-2 text-sm text-gray-600">
            <li>
                <a href="<?= route('employees.index') ?>" class="hover:text-blue-600">Funcionários</a>
            </li>
            <li class="before:content-['/'] before:mr-2">Novo Funcionário</li>
        </ol>
    </nav>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
            <h1 class="text-xl font-semibold">Novo Funcionário</h1>
        </div>
        <div class="p-6">
            <form action="<?= route('employees.store') ?>" method="post" class="space-y-6" novalidate>
                <!-- Dados Pessoais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome completo*</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['name']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="name" name="name" value="<?= htmlspecialchars($employee->name ?? '') ?>" required>
                        <?php if(isset($errors['name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['name'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email*</label>
                        <input type="email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['email']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="email" name="email" value="<?= htmlspecialchars($employee->email ?? '') ?>" required>
                        <?php if(isset($errors['email'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['email'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">CPF*</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['cpf']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="cpf" name="cpf" value="<?= htmlspecialchars($employee->cpf ?? '') ?>"
                               placeholder="000.000.000-00" required>
                        <?php if(isset($errors['cpf'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['cpf'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
                        <input type="date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['birth_date']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="birth_date" name="birth_date" value="<?= htmlspecialchars($employee->birth_date ?? '') ?>">
                        <?php if(isset($errors['birth_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['birth_date'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">Cargo*</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['role_id']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                                id="role_id" name="role_id" required>
                            <option value="" selected disabled>Selecione...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role->id ?>" <?= ($employee->role_id ?? '') == $role->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role->description) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(isset($errors['role_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['role_id'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="salary" class="block text-sm font-medium text-gray-700 mb-2">Salário</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">R$</span>
                            </div>
                            <input type="number" step="0.01" min="0"
                                   class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['salary']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                                   id="salary" name="salary" value="<?= htmlspecialchars($employee->salary ?? '') ?>">
                        </div>
                        <?php if(isset($errors['salary'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['salary'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Contratação*</label>
                        <input type="date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['hire_date']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="hire_date" name="hire_date" value="<?= htmlspecialchars($employee->hire_date ?? date('Y-m-d')) ?>" required>
                        <?php if(isset($errors['hire_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['hire_date'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Endereço -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
                    <input type="text"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['address']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                           id="address" name="address" value="<?= htmlspecialchars($employee->address ?? '') ?>">
                    <?php if(isset($errors['address'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['address'] ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['city']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="city" name="city" value="<?= htmlspecialchars($employee->city ?? '') ?>">
                        <?php if(isset($errors['city'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['city'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['state']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="state" name="state" value="<?= htmlspecialchars($employee->state ?? '') ?>">
                        <?php if(isset($errors['state'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['state'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="zipcode" class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['zipcode']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                               id="zipcode" name="zipcode" value="<?= htmlspecialchars($employee->zipcode ?? '') ?>"
                               placeholder="00000-000">
                        <?php if(isset($errors['zipcode'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['zipcode'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['notes']) ? 'border-red-300 focus:ring-red-500' : '' ?>"
                              id="notes" name="notes" rows="3"><?= htmlspecialchars($employee->notes ?? '') ?></textarea>
                    <?php if(isset($errors['notes'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['notes'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Divisor -->
                <hr class="border-gray-200">

                <!-- Credenciais de Acesso -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Credenciais de Acesso</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <p class="text-blue-700 text-sm">Defina uma senha para o funcionário poder acessar o sistema.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha*</label>
                        <input type="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               id="password" name="password" required>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha*</label>
                        <input type="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                id="status" name="status">
                            <option value="Active" <?= ($employee->status ?? 'Active') === 'Active' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inactive" <?= ($employee->status ?? '') === 'Inactive' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">

                <!-- Botões de Ação -->
                <div class="flex justify-between pt-6">
                    <a href="<?= route('employees.index') ?>" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md flex items-center">
                        <i class="fas fa-save mr-2"></i> Salvar Funcionário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/employee-form-validation.js"></script>
