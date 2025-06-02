<?php
/**
 * Componente de paginação para ser incluído nas visualizações que usam paginação
 * Este componente espera uma variável $employees ou similar que seja uma instância de Paginator
 */

// Verificando se a variável $employees existe e é uma instância de Paginator
if (!isset($employees) || !$employees instanceof \Lib\Paginator) {
    return;
}

$totalPages = $employees->lastPage();

if ($totalPages <= 1) {
    return;
}

$currentPage = $employees->currentPage();

$range = 2;

$start = max(1, $currentPage - $range);
$end = min($totalPages, $currentPage + $range);

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
        <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $employees->url($currentPage - 1) ?>" aria-label="Anterior">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>

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

        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="<?= $employees->url($i) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

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

        <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $employees->url($currentPage + 1) ?>" aria-label="Próximo">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
