<?php

namespace App\Models;

class EmployeePaginator
{
    /**
     * Cria um objeto de paginação a partir de uma lista de funcionários
     *
     * @param array<int, Employee> $employees Lista de funcionários
     * @param int $page Número da página atual
     * @param int $perPage Itens por página
     * @return object Objeto de paginação
     */
    public static function paginate(array $employees, int $page, int $perPage): object
    {
        $total = count($employees);
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages ?: 1)); // Garantir que a página é válida
        $offset = ($page - 1) * $perPage;
        $paginatedEmployees = array_slice($employees, $offset, $perPage);

        return new class ($paginatedEmployees, $total, $page, $perPage) {
            /** @var array<int, \App\Models\Employee> */
            private array $items;
            private int $total;
            private int $page;
            private int $perPage;

            /**
             * @param array<int, \App\Models\Employee> $items
             * @param int $total
             * @param int $page
             * @param int $perPage
             */
            public function __construct(array $items, int $total, int $page, int $perPage)
            {
                $this->items = $items;
                $this->total = $total;
                $this->page = $page;
                $this->perPage = $perPage;
            }

            /**
             * @return array<int, \App\Models\Employee>
             */
            public function items(): array
            {
                return $this->items;
            }

            public function total(): int
            {
                return $this->total;
            }

            public function getPage(): int
            {
                return $this->page;
            }

            public function perPage(): int
            {
                return $this->perPage;
            }

            public function getTotalPages(): int
            {
                return (int)ceil($this->total / $this->perPage);
            }

            public function totalOfRegisters(): int
            {
                return $this->total;
            }

            public function totalOfRegistersOfPage(): int
            {
                return count($this->items);
            }
        };
    }
}
