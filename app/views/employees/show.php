<?php use Core\Constants\CssClasses; ?>

<!-- Header Section -->
<div class="glass-effect rounded-2xl shadow-xl p-8 mb-8">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl mb-4 shadow-lg">
            <i class="fas fa-user text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
            <?= htmlspecialchars($employee->name) ?>
        </h1>
        <p class="text-gray-600">Visualize todas as informações do funcionário</p>
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
                <span class="text-gray-500">Detalhes</span>
            </li>
        </ol>
    </nav>

    <!-- Ações -->
    <div class="flex justify-center mb-8 space-x-4">
        <a href="<?= route('employees.edit', ['id' => $employee->id]) ?>"
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg hover:from-yellow-600 hover:to-orange-600 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            <i class="fas fa-edit mr-2"></i> Editar
        </a>
        <button type="button"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg hover:from-red-700 hover:to-pink-700 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                onclick="confirmDelete(<?= $employee->id ?>, '<?= htmlspecialchars($employee->name) ?>')">
            <i class="fas fa-trash mr-2"></i> Excluir
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna principal (2/3) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Informações Pessoais -->
            <div class="glass-effect rounded-2xl shadow-xl p-8">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-user mr-3 text-blue-600"></i>
                        Informações Pessoais
                    </h2>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Completo</label>
                            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-user text-blue-600 mr-3"></i>
                                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($employee->name) ?></span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">CPF</label>
                            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-id-card text-blue-600 mr-3"></i>
                                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($employee->cpf) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-blue-600 mr-3"></i>
                                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($employee->email) ?></span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Data de Nascimento</label>
                            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>
                                    <span class="text-gray-900 font-medium">
                                        <?= $employee->birth_date ? date('d/m/Y', strtotime($employee->birth_date)) : 'Não informado' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Endereço Completo</label>
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-blue-600 mr-3 mt-1"></i>
                                <span class="text-gray-900 font-medium">
                                    <?php if ($employee->address): ?>
                                        <?= htmlspecialchars($employee->address) ?>
                                        <?php if ($employee->city || $employee->state): ?>
                                            <br><?= htmlspecialchars($employee->city ?? '') ?> - <?= htmlspecialchars($employee->state ?? '') ?>
                                        <?php endif; ?>
                                        <?php if ($employee->zipcode): ?>
                                            <br>CEP: <?= htmlspecialchars($employee->zipcode) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500">Não informado</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="glass-effect rounded-2xl shadow-xl p-8">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-sticky-note mr-3 text-blue-600"></i>
                        Observações
                    </h2>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                    <?php if ($employee->notes): ?>
                        <p class="text-gray-900 whitespace-pre-line"><?= htmlspecialchars($employee->notes) ?></p>
                    <?php else: ?>
                        <p class="text-gray-500 italic">Nenhuma observação registrada</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-8">
            <!-- Informações Profissionais -->
            <div class="glass-effect rounded-2xl shadow-xl p-8">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-briefcase mr-3 text-blue-600"></i>
                        Informações Profissionais
                    </h2>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cargo</label>
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-user-tag text-blue-600 mr-3"></i>
                                <span class="text-gray-900 font-medium">
                                    <?php if ($employee->role()): ?>
                                        <?= htmlspecialchars($employee->role()->description) ?>
                                    <?php else: ?>
                                        <span class="text-red-600">Não definido</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Salário</label>
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-dollar-sign text-blue-600 mr-3"></i>
                                <span class="text-gray-900 font-medium">
                                    <?= $employee->salary ? 'R$ ' . number_format($employee->salary, 2, ',', '.') : 'Não informado' ?>
                                </span>
                            </div>
                        </div>
                    </div>                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Data de Contratação</label>
                            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-check text-blue-600 mr-3"></i>
                                    <span class="text-gray-900 font-medium">
                                        <?= $employee->hire_date ? date('d/m/Y', strtotime($employee->hire_date)) : 'Não informado' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-100 px-4 py-3 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-circle text-blue-600 mr-3"></i>
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full <?= $employee->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $employee->status === 'Active' ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acesso ao Sistema -->
            <div class="glass-effect rounded-2xl shadow-xl p-8">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-key mr-3 text-blue-600"></i>
                        Acesso ao Sistema
                    </h2>
                </div>

                <?php $credential = $employee->credential(); ?>
                <?php if ($credential): ?>
                    <div class="glass-effect rounded-lg p-6 bg-gradient-to-r from-green-50 to-emerald-100 border border-green-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <span class="font-semibold text-green-900">Possui acesso ao sistema</span>
                        </div>
                        <p class="text-green-800 text-sm flex items-center">
                            <i class="fas fa-clock mr-2"></i>
                            Última atualização: <?= $credential->last_updated ? date('d/m/Y \à\s H:i', strtotime($credential->last_updated)) : 'Não informado' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="glass-effect rounded-lg p-6 mb-6 bg-gradient-to-r from-yellow-50 to-orange-100 border border-yellow-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                            <span class="font-semibold text-yellow-900">Sem acesso ao sistema</span>
                        </div>
                        <p class="text-yellow-800 text-sm mb-4">
                            Este funcionário não possui credenciais de acesso ao sistema.
                        </p>
                        <a href="<?= route('employees.edit', ['id' => $employee->id]) ?>"
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium text-sm">
                            <i class="fas fa-key mr-2"></i> Adicionar credenciais
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 border w-96 shadow-2xl rounded-2xl bg-white glass-effect">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl mb-4">
                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2">Confirmar exclusão</h3>
            <p class="text-gray-600 mb-4">
                Tem certeza que deseja excluir o funcionário <strong id="employeeName" class="text-gray-900"></strong>?
            </p>
            <div class="glass-effect rounded-lg p-4 mb-6 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200">
                <p class="text-red-800 text-sm font-medium flex items-center justify-center">
                    <i class="fas fa-warning mr-2"></i>
                    Esta ação não pode ser desfeita!
                </p>
            </div>

            <div class="flex justify-center space-x-4">
                <button type="button" onclick="closeDeleteModal()"
                        class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-lg hover:from-gray-200 hover:to-gray-300 transition-all duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <form action="<?= route('employees.destroy') ?>" method="post" class="inline">
                    <input type="hidden" name="id" id="employeeId">
                    <button type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg hover:from-red-700 hover:to-pink-700 transition-all duration-200 font-medium shadow-lg">
                        <i class="fas fa-trash mr-2"></i>Excluir
                    </button>
                </form>
            </div>

            <button type="button" onclick="closeDeleteModal()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
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
