<?php
use Core\Constants\CssClasses;
?>
<div class="<?= CssClasses::CONTAINER_MAX_WIDTH ?>">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Lista de Funcionários</h1>
        <div class="flex justify-between items-center">
            <p class="text-gray-600">Gerencie os funcionários do sistema</p>
            <a href="<?= route('employees.create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-user-plus mr-2"></i> Novo Funcionário
            </a>
        </div>
    </div>

    <!-- Filtro de pesquisa -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <form action="<?= route('employees.index') ?>" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4" id="searchForm">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Nome ou Email</label>                    <input type="text" class="<?= CssClasses::INPUT_BASE ?>"
                           id="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                           placeholder="Buscar por nome ou email">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Cargo</label>
                    <select class="<?= CssClasses::SELECT_BASE ?>"
                            id="role" name="role">
                        <option value="">Todos os cargos</option>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?= $role->id ?>" <?= isset($_GET['role']) && $_GET['role'] == $role->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role->description) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select class="<?= CssClasses::SELECT_BASE ?>"
                            id="status" name="status">
                        <option value="">Todos</option>
                        <option value="Active" <?= isset($_GET['status']) && $_GET['status'] === 'Active' ? 'selected' : '' ?>>
                            Ativo
                        </option>
                        <option value="Inactive" <?= isset($_GET['status']) && $_GET['status'] === 'Inactive' ? 'selected' : '' ?>>
                            Inativo
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de funcionários -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <?php if ($employees->total() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($employees->items() as $employee): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $employee->id ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($employee->name) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($employee->email) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php if ($employee->role()): ?>
                                            <?= htmlspecialchars($employee->role()->description) ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">Não definido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $employee->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= $employee->status === 'Active' ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="<?= route('employees.show', ['id' => $employee->id]) ?>"
                                               class="<?= CssClasses::ACTION_VIEW ?>"
                                               title="Ver detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= route('employees.edit', ['id' => $employee->id]) ?>"
                                               class="<?= CssClasses::ACTION_EDIT ?>"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="<?= CssClasses::ACTION_DELETE ?>"
                                                    onclick="confirmDelete(<?= $employee->id ?>, '<?= htmlspecialchars($employee->name) ?>')"
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Mostrando <?= $employees->totalOfRegistersOfPage() ?> de <?= $employees->totalOfRegisters() ?> registros
                    </div>

                    <nav aria-label="Paginação de funcionários" class="flex items-center space-x-1">
                        <!-- Botão 'Anterior' -->
                        <?php if ($employees->getPage() > 1): ?>
                            <a href="<?= route('employees.index', ['page' => $employees->getPage() - 1] + ($queryParams ?? [])) ?>"
                               class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-md flex items-center"
                               aria-label="Anterior">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 text-sm text-gray-300 rounded-md flex items-center cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        <?php endif; ?>

                        <?php
                        $totalPages = $employees->totalOfPages();
                        $currentPage = $employees->getPage();
                        $range = 2;

                        // Calculando o início e o fim da faixa de páginas
                        $start = max(1, $currentPage - $range);
                        $end = min($totalPages, $currentPage + $range);

                        // Garantindo que mostremos pelo menos 5 páginas se possível
                        if ($end - $start + 1 < 5 && $totalPages >= 5) {
                            if ($start === 1) {
                                $end = min($totalPages, 5);
                            } elseif ($end === $totalPages) {
                                $start = max(1, $totalPages - 4);
                            }
                        }

                        // Primeira página, se não estiver no início da faixa
                        if ($start > 1): ?>
                            <a href="<?= route('employees.index', ['page' => 1] + ($queryParams ?? [])) ?>"
                               class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md">1</a>
                            <?php if ($start > 2): ?>
                                <span class="px-3 py-2 text-sm text-gray-400">...</span>
                            <?php endif;
                        endif;

                        // Números das páginas
                        for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i === $currentPage): ?>
                                <span class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= route('employees.index', ['page' => $i] + ($queryParams ?? [])) ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor;

                        // Última página, se não estiver no fim da faixa
                        if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                                <span class="px-3 py-2 text-sm text-gray-400">...</span>
                            <?php endif; ?>
                            <a href="<?= route('employees.index', ['page' => $totalPages] + ($queryParams ?? [])) ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <!-- Botão 'Próximo' -->
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= route('employees.index', ['page' => $currentPage + 1] + ($queryParams ?? [])) ?>"
                               class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-md flex items-center"
                               aria-label="Próximo">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 text-sm text-gray-300 rounded-md flex items-center cursor-not-allowed">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>

            <?php else: ?>
                <div class="text-center py-12">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum funcionário encontrado</h3>
                        <p class="text-gray-500 mb-4">Não há funcionários cadastrados que correspondam aos filtros aplicados.</p>
                        <a href="<?= route('employees.create') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-user-plus mr-2"></i> Cadastrar Primeiro Funcionário
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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
