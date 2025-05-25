<?php use Core\Constants\CssClasses; ?>

<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-6">
        <ol class="flex space-x-2 text-sm text-gray-600">
            <li>
                <a href="<?= route('employees.index') ?>" class="hover:text-blue-600">Funcionários</a>
            </li>
            <li class="before:content-['/'] before:mr-2">Detalhes</li>
        </ol>
    </nav>

    <!-- Header com ações -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($employee->name) ?></h1>
        <div class="flex space-x-3">
            <a href="<?= route('employees.edit', ['id' => $employee->id]) ?>"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
            <button type="button"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center"
                    onclick="confirmDelete(<?= $employee->id ?>, '<?= htmlspecialchars($employee->name) ?>')">
                <i class="fas fa-trash mr-2"></i> Excluir
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna principal (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informações Pessoais -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold">Informações Pessoais</h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Nome Completo</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($employee->name) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">CPF</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($employee->cpf) ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Email</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($employee->email) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Data de Nascimento</p>
                            <p class="font-medium text-gray-900">
                                <?= $employee->birth_date ? date('d/m/Y', strtotime($employee->birth_date)) : 'Não informado' ?>
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Endereço Completo</p>
                        <p class="font-medium text-gray-900">
                            <?php if ($employee->address): ?>
                                <?= htmlspecialchars($employee->address) ?>
                                <?php if ($employee->city || $employee->state): ?>
                                    , <?= htmlspecialchars($employee->city ?? '') ?> - <?= htmlspecialchars($employee->state ?? '') ?>
                                <?php endif; ?>
                                <?php if ($employee->zipcode): ?>
                                    , CEP: <?= htmlspecialchars($employee->zipcode) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-500">Não informado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold">Observações</h5>
                </div>
                <div class="p-6">
                    <?php if ($employee->notes): ?>
                        <p class="text-gray-900 whitespace-pre-line"><?= htmlspecialchars($employee->notes) ?></p>
                    <?php else: ?>
                        <p class="text-gray-500">Nenhuma observação registrada</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            <!-- Informações Profissionais -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold">Informações Profissionais</h5>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Cargo</p>
                        <p class="font-medium text-gray-900">
                            <?php if ($employee->role()): ?>
                                <?= htmlspecialchars($employee->role()->description) ?>
                            <?php else: ?>
                                <span class="text-red-600">Não definido</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Salário</p>
                        <p class="font-medium text-gray-900">
                            <?= $employee->salary ? 'R$ ' . number_format($employee->salary, 2, ',', '.') : 'Não informado' ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Data de Contratação</p>
                        <p class="font-medium text-gray-900">
                            <?= date('d/m/Y', strtotime($employee->hire_date)) ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Status</p>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $employee->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= $employee->status === 'Active' ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Acesso ao Sistema -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-gray-600 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold">Acesso ao Sistema</h5>
                </div>
                <div class="p-6">
                    <?php $credential = $employee->credential(); ?>
                    <?php if ($credential): ?>
                        <div class="flex items-center text-green-600 mb-3">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span class="font-medium">Possui acesso ao sistema</span>
                        </div>
                        <p class="text-gray-500 text-sm flex items-center">
                            <i class="fas fa-clock mr-2"></i>
                            Última atualização: <?= date('d/m/Y \à\s H:i', strtotime($credential->last_updated)) ?>
                        </p>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                                <p class="text-yellow-700 text-sm">Este funcionário não possui credenciais de acesso ao sistema.</p>
                            </div>
                        </div>
                        <a href="<?= route('employees.edit', ['id' => $employee->id]) ?>"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm flex items-center justify-center">
                            <i class="fas fa-key mr-2"></i> Adicionar credenciais
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Confirmar exclusão</h3>
                <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-6">
                <p class="text-gray-700 mb-2">Tem certeza que deseja excluir o funcionário <strong id="employeeName" class="text-gray-900"></strong>?</p>
                <p class="text-red-600 text-sm">Esta ação não pode ser desfeita!</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Cancelar
                </button>
                <form action="<?= route('employees.destroy') ?>" method="post" class="inline">
                    <input type="hidden" name="id" id="employeeId">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('employeeId').value = id;
        document.getElementById('employeeName').textContent = name;

        // Mostra o modal
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Fecha o modal ao clicar fora dele
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Fecha o modal com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
