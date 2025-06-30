<?php

namespace Tests\Unit\Lib;

use Lib\Paginator;

/**
 * Mock da classe Paginator para testes, substituindo comportamentos que dependem do banco de dados
 *
 * Nota: Esta classe redefine apenas os métodos que dependem do banco de dados, sem preocupação
 * com as propriedades privadas da classe pai.
 */
class MockPaginator extends Paginator
{
    /** @var array<int, \stdClass> */
    protected array $mockRegisters = [];

    /** @var int */
    protected int $mockTotalRegisters;

    /** @var int */
    protected int $mockTotalPages;

    /**
     * Construtor que chama o construtor pai mas substitui os métodos que acessam o banco
     *
     * @param int $page Número da página atual
     * @param int $perPage Quantidade de registros por página
     * @param int $total Total de registros disponíveis
     */
    public function __construct(int $page = 1, int $perPage = 10, int $total = 20)
    {
        // A classe StubPaginator seria preferível para testes unitários, mas quando
        // precisamos estender a Paginator original, usamos esta abordagem
        parent::__construct('stdClass', $page, $perPage, 'mock_table', ['id', 'column1']);

        // Simulamos os dados que seriam carregados do banco
        $this->mockTotalRegisters = $total;
        $this->mockTotalPages = (int)ceil($total / $perPage);

        // Criamos registros fictícios para testes
        $offset = ($page - 1) * $perPage;
        $this->mockRegisters = $this->createMockRegisters(min($perPage, max(0, $total - $offset)));
    }

    /**
     * Retorna o número total de registros
     *
     * @return int Número total de registros
     */
    public function totalOfRegisters(): int
    {
        return $this->mockTotalRegisters;
    }

    /**
     * Retorna o número total de páginas
     *
     * @return int Número total de páginas
     */
    public function totalOfPages(): int
    {
        return $this->mockTotalPages;
    }

    /**
     * Retorna os registros da página atual
     *
     * @return array<int, \stdClass> Registros da página atual
     */
    public function registers(): array
    {
        return $this->mockRegisters;
    }

    /**
     * Sobrescreve o método loadTotals para não acessar o banco de dados
     */
    protected function loadTotals(): void
    {
        // Não faz nada - evita acesso ao banco
    }

    /**
     * Sobrescreve o método loadRegisters para não acessar o banco de dados
     */
    protected function loadRegisters(): void
    {
        // Não faz nada - evita acesso ao banco
    }

    /**
     * Cria registros fictícios para testes
     *
     * @param int $count Quantidade de registros para criar
     * @return array<int, \stdClass> Array de objetos simulando registros
     */
    private function createMockRegisters(int $count): array
    {
        $registers = [];
        for ($i = 0; $i < $count; $i++) {
            $register = new \stdClass();
            $register->id = $i + 1;
            $register->column1 = "Value $i";
            $registers[] = $register;
        }
        return $registers;
    }
}
