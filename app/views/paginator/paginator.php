<?php
/**
 * Componente de paginação para ser incluído nas visualizações que usam paginação
 * Este componente espera uma variável $employees ou similar que seja uma instância de Paginator
 */

// Verificando se a variável $employees existe e é uma instância de Paginator
if (!isset($employees) || !$employees instanceof \Lib\Paginator) {
    return;
}

// Calculando o total de páginas
$totalPages = $employees->lastPage();

// Se tivermos apenas uma página, não mostrar o paginador
if ($totalPages <= 1) {
    return;
}

// Página atual
$currentPage = $employees->currentPage();

// Número de páginas para mostrar antes e depois da atual
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
?>

<nav aria-label="Paginação de funcionários">
    <ul class="pagination justify-content-center">
        <!-- Botão 'Anterior' -->
        <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $employees->url($currentPage - 1) ?>" aria-label="Anterior">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

        <!-- Primeira página, se não estiver no início da faixa -->
        <?php if ($start > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $employees->url(1) ?>">1</a>
            </li>
            <?php if ($start > 2): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Números das páginas -->
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="<?= $employees->url($i) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <!-- Última página, se não estiver no fim da faixa -->
        <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="<?= $employees->url($totalPages) ?>"><?= $totalPages ?></a>
            </li>
        <?php endif; ?>

        <!-- Botão 'Próximo' -->
        <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $employees->url($currentPage + 1) ?>" aria-label="Próximo">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
