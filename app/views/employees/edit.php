<?php
use Core\Constants\CssClasses;
use Lib\Authentication\Auth;

// Verificar permissões de edição
$canEdit = Auth::isAdmin() || Auth::isHR();
$readonly = $canEdit ? '' : 'readonly';
$disabled = $canEdit ? '' : 'disabled';
$disabledClass = $canEdit ? '' : ' opacity-60 cursor-not-allowed';
?>
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-6">
        <ol class="flex space-x-2 text-sm text-gray-600">
            <li>
                <a href="<?= route('employees.index') ?>" class="hover:text-blue-600">Funcionários</a>
            </li>
            <li class="before:content-['/'] before:mr-2">
                <a href="<?= route('employees.show', ['id' => $employee->id]) ?>"
                   class="hover:text-blue-600"
                   aria-label="Ver detalhes do funcionário <?= htmlspecialchars($employee->name ?? 'Funcionário') ?>">
                    <?= htmlspecialchars($employee->name ?? 'Funcionário') ?>
                </a>
            </li>
            <li class="before:content-['/'] before:mr-2">Editar</li>
        </ol>
    </nav>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="bg-yellow-500 text-white px-6 py-4 rounded-t-lg">
            <h1 class="text-xl font-semibold">Editar Funcionário</h1>
        </div>

        <?php if (!$canEdit): ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Você está visualizando este funcionário em <strong>modo somente leitura</strong>.
                            Apenas usuários com perfil Admin ou RH podem editar informações de funcionários.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="p-6">
            <form action="<?= route('employees.update') ?>" method="post" class="space-y-6" novalidate>
                <input type="hidden" name="id" value="<?= $employee->id ?>">

                <!-- Dados Pessoais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome completo*</label>
                        <input type="text"
                               class="<?= CssClasses::inputClass(isset($errors['name'])) ?><?= $disabledClass ?>"
                               id="name" name="name" value="<?= htmlspecialchars($employee->name) ?>"
                               required <?= $readonly ?>>
                        <?php if(isset($errors['name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['name'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email*</label>
                        <input type="email"
                               class="<?= CssClasses::inputClass(isset($errors['email'])) ?><?= $disabledClass ?>"
                               id="email" name="email" value="<?= htmlspecialchars($employee->email) ?>"
                               required <?= $readonly ?>>
                        <?php if(isset($errors['email'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['email'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">CPF*</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['cpf']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="cpf" name="cpf" value="<?= htmlspecialchars($employee->cpf) ?>"
                               placeholder="000.000.000-00" required <?= $readonly ?>>
                        <?php if(isset($errors['cpf'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['cpf'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento*</label>
                        <input type="date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['birth_date']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="birth_date" name="birth_date" value="<?= htmlspecialchars($employee->birth_date ?? '') ?>"
                               required <?= $readonly ?>>
                        <?php if(isset($errors['birth_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['birth_date'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">Cargo*</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['role_id']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                                id="role_id" name="role_id" required <?= $disabled ?>>
                            <option value="" disabled>Selecione...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role->id ?>" <?= $employee->role_id == $role->id ? 'selected' : '' ?>>
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
                        <label for="salary" class="block text-sm font-medium text-gray-700 mb-2">Salário*</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">R$</span>
                            </div>
                            <input type="text"
                                   class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['salary']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                                   id="salary" name="salary" value="<?= $employee->salary ? number_format($employee->salary, 2, ',', '.') : '' ?>"
                                   placeholder="0,00"
                                   pattern="[0-9,.]*"
                                   inputmode="decimal" required <?= $readonly ?>>
                        </div>
                        <?php if(isset($errors['salary'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['salary'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Contratação*</label>
                        <input type="date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['hire_date']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="hire_date" name="hire_date" value="<?= htmlspecialchars($employee->hire_date) ?>"
                               required <?= $readonly ?>>
                        <?php if(isset($errors['hire_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['hire_date'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Endereço -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
                    <input type="text"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['address']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                           id="address" name="address" value="<?= htmlspecialchars($employee->address ?? '') ?>"
                           <?= $readonly ?>>
                    <?php if(isset($errors['address'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['address'] ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['city']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="city" name="city" value="<?= htmlspecialchars($employee->city ?? '') ?>"
                               <?= $readonly ?>>
                        <?php if(isset($errors['city'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['city'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['state']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="state" name="state" value="<?= htmlspecialchars($employee->state ?? '') ?>"
                               <?= $readonly ?>>
                        <?php if(isset($errors['state'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['state'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="zipcode" class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                        <input type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['zipcode']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                               id="zipcode" name="zipcode" value="<?= htmlspecialchars($employee->zipcode ?? '') ?>"
                               placeholder="00000-000" <?= $readonly ?>>
                        <?php if(isset($errors['zipcode'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['zipcode'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['notes']) ? 'border-red-300 focus:ring-red-500' : '' ?><?= $disabledClass ?>"
                              id="notes" name="notes" rows="3" <?= $readonly ?>><?= htmlspecialchars($employee->notes ?? '') ?></textarea>
                    <?php if(isset($errors['notes'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['notes'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Divisor -->
                <hr class="border-gray-200">

                <!-- Credenciais de Acesso -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Credenciais de Acesso</h4>
                    <?php $credential = $employee->credential(); ?>
                    <?php if ($credential): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                                <div>
                                    <p class="text-blue-700 text-sm">O funcionário já possui credenciais de acesso. Preencha apenas se desejar alterar a senha.</p>
                                    <p class="text-blue-600 text-xs mt-1">Última atualização: <?= date('d/m/Y \à\s H:i', strtotime($credential->last_updated)) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                                <p class="text-yellow-700 text-sm">Este funcionário ainda não possui credenciais de acesso. Defina uma senha para permitir o acesso ao sistema.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha <?= !$credential ? '*' : '' ?></label>
                        <input type="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent<?= $disabledClass ?>"
                               id="password" name="password" <?= !$credential ? 'required' : '' ?> <?= $readonly ?>>
                        <p class="mt-1 text-sm text-gray-500">Mínimo de 6 caracteres</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha <?= !$credential ? '*' : '' ?></label>
                        <input type="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent<?= $disabledClass ?>"
                               id="password_confirmation" name="password_confirmation" <?= !$credential ? 'required' : '' ?> <?= $readonly ?>>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent<?= $disabledClass ?>"
                                id="status" name="status" <?= $disabled ?>>
                            <option value="Active" <?= $employee->status === 'Active' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inactive" <?= $employee->status === 'Inactive' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-between pt-6">
                    <a href="<?= route('employees.show', ['id' => $employee->id]) ?>" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </a>
                    <?php if ($canEdit): ?>
                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md flex items-center">
                            <i class="fas fa-save mr-2"></i> Salvar Alterações
                        </button>
                    <?php else: ?>
                        <div class="px-6 py-2 bg-gray-300 text-gray-500 rounded-md flex items-center cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i> Acesso Somente Leitura
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/employee-form-validation.js"></script>
