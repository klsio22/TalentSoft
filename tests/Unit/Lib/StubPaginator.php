<?php

namespace Tests\Unit\Lib;

/**
 * Stub da classe Paginator para testes, reimplementando apenas o comportamento necessário
 * sem depender do banco de dados
 */
class StubPaginator
{
    private int $page;
    private int $perPage;
    private int $total;
    private int $totalPages;
    private int $offset;
    /** @var array<int, \stdClass> $registers */
    private array $registers = [];
    private int $registersOfPage;
    private string $table;
    private ?string $route;

    /**
     * Cria um paginador stub com valores predefinidos para testes
     */
    public function __construct(int $page = 1, int $perPage = 10, int $total = 20, ?string $routeName = null)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->totalPages = (int)ceil($total / $perPage);
        $this->offset = ($page - 1) * $perPage;

        // Garantir que não ultrapassamos o total
        $itemsInThisPage = min($perPage, max(0, $total - $this->offset));
        $this->registersOfPage = $itemsInThisPage;

        // Criar registros de teste
        $this->registers = $this->createMockRegisters($itemsInThisPage);

        $this->table = 'mock_table';
        $this->route = $routeName;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function totalOfRegistersOfPage(): int
    {
        return $this->registersOfPage;
    }

    public function totalOfRegisters(): int
    {
        return $this->total;
    }

    public function totalOfPages(): int
    {
        return $this->totalPages;
    }

    public function previousPage(): int
    {
        return $this->page - 1;
    }

    public function nextPage(): int
    {
        return $this->page + 1;
    }

    public function hasPreviousPage(): bool
    {
        return $this->previousPage() >= 1;
    }

    public function hasNextPage(): bool
    {
        return $this->nextPage() <= $this->totalPages;
    }

    public function isPage(int $page): bool
    {
        return $this->page === $page;
    }

    public function entriesInfo(): string
    {
        $begin = $this->offset + 1;
        $end = $begin + $this->registersOfPage - 1;
        return "Mostrando {$begin} - {$end} de {$this->total}";
    }

    /**
     * @return array<int, \stdClass>
     */
    public function registers(): array
    {
        return $this->registers;
    }

    public function getRouteName(): string
    {
        return $this->route ?? "{$this->table}.paginate";
    }

    /**
     * Cria objetos mock para simular registros do banco de dados
     *
     * @param int $count Quantidade de registros para criar
     * @return array<int, \stdClass> Array de objetos simulando registros
     */
    private function createMockRegisters(int $count): array
    {
        $registers = [];
        for ($i = 0; $i < $count; $i++) {
            $register = new \stdClass();
            $register->id = $this->offset + $i + 1;
            $register->column1 = "Value " . ($this->offset + $i);
            $registers[] = $register;
        }
        return $registers;
    }
}
