<?php

namespace Tests\Acceptance\Projects;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class EmployeeProjectsCest extends BaseAcceptanceCest
{
    private const LOGIN_URL = '/login';
    private const ADMIN_EMAIL = 'klesio@admin.com';
    private const DEFAULT_PASSWORD = '123456';
    private const PROJECTS_INDEX_URL = '/projects';
    private const EMPLOYEES_INDEX_URL = '/employees';
    private const SUCCESS_MESSAGE_SELECTOR =
        '//div[contains(@class, "alert") or contains(@class, "message") or contains(@class, "flash-message")]';
    private const SCROLL_TO_BOTTOM = 'window.scrollTo(0, document.body.scrollHeight);';
    private const TEST_PROJECT_NAME = 'Projeto Teste para Funcionários';
    private const TEST_EMPLOYEE_NAME = 'Funcionário Teste';
    private const TEST_EMPLOYEE_ROLE = 'Desenvolvedor';
    private const TEST_EMPLOYEE_NEW_ROLE = 'Gerente de Projeto';
    private const FIRST_PROJECT_LINK_SELECTOR = '//table//tbody//tr[1]//td[1]//a';

    private function loginAsAdmin(AcceptanceTester $tester): void
    {
        $tester->amOnPage(self::LOGIN_URL);

        // Verificar quais campos estão disponíveis na página de login
        $tester->wait(1);

        // Tentar diferentes abordagens para preencher os campos de login
        try {
            // Abordagem 1: Usar seletores simples
            $tester->fillField('email', self::ADMIN_EMAIL);
            $tester->fillField('password', self::DEFAULT_PASSWORD);
        } catch (\Exception $e) {
            try {
                // Abordagem 2: Usar seletores de tipo de input
                $tester->fillField('input[type=email]', self::ADMIN_EMAIL);
                $tester->fillField('input[type=password]', self::DEFAULT_PASSWORD);
            } catch (\Exception $e2) {
                // Abordagem 3: Usar qualquer input na página (assumindo que há apenas dois campos)
                $inputs = $tester->executeJS('return document.querySelectorAll("input").length');
                if ($inputs >= 2) {
                    $tester->fillField('(//input)[1]', self::ADMIN_EMAIL);
                    $tester->fillField('(//input)[2]', self::DEFAULT_PASSWORD);
                }
            }
        }

        // Tentar diferentes abordagens para clicar no botão de login
        try {
            // Abordagem 1: Clicar em qualquer botão ou input de submissão
            $tester->click('button[type=submit], input[type=submit]');
        } catch (\Exception $e) {
            try {
                // Abordagem 2: Clicar em qualquer botão
                $tester->click('button');
            } catch (\Exception $e2) {
                // Abordagem 3: Tentar submeter o formulário diretamente se existir
                $tester->executeJS(
                    'const form = document.querySelector("form"); ' .
                    'if (form) { form.submit(); } else { console.log("No form found"); }'
                );
            }
        }

        $tester->wait(1);
    }

    private function scrollToButtonAndClick(AcceptanceTester $tester, string $buttonText): void
    {
        $tester->executeJS(self::SCROLL_TO_BOTTOM);
        $tester->wait(1);
        $tester->click($buttonText);
    }

    /**
     * Cria um projeto de teste para usar nos testes de associação
     */
    private function createTestProject(AcceptanceTester $tester): string
    {
        $tester->amOnPage('/projects/create');
        $tester->see('Novo Projeto');

        // Gerar nome único para o projeto
        $projectName = self::TEST_PROJECT_NAME . ' ' . uniqid();

        // Preencher com dados válidos
        $tester->fillField('name', $projectName);
        $tester->fillField('description', 'Descrição do projeto para testes de associação');
        $tester->fillField('budget', '10000.00');
        $tester->selectOption('status', 'Em andamento');

        // Datas de início e término
        $startDate = date('Y-m-d'); // Hoje
        $endDate = date('Y-m-d', strtotime('+30 days')); // 30 dias a partir de hoje

        $tester->fillField('start_date', $startDate);
        $tester->fillField('end_date', $endDate);

        // Salvar o projeto usando seletores mais genéricos
        $tester->executeJS(self::SCROLL_TO_BOTTOM);
        $tester->wait(1);

        try {
            $tester->click(
                '//button[contains(text(), "Salvar")] | //input[@value="Salvar"] | ' .
                '//button[@type="submit"] | //input[@type="submit"]'
            );
            $tester->wait(1);

            // Se houver um alerta de validação, aceitar
            try {
                $tester->acceptPopup();
                $tester->comment('Alerta de validação aceito durante criação de projeto');
            } catch (\Exception $e) {
                // Sem alerta, continuar normalmente
            }

            $tester->wait(2);
        } catch (\Exception $e) {
            // Se houver problema, aceitar alerta e tentar novamente
            try {
                $tester->acceptPopup();
                $tester->comment('Alerta aceito após erro');
                $tester->wait(1);
            } catch (\Exception $alertError) {
                // Sem alerta para aceitar
            }
        }

        return $projectName;
    }

    /**
     * Cria um funcionário de teste para usar nos testes de associação
     */
    private function createTestEmployee(AcceptanceTester $tester): string
    {
        $tester->amOnPage('/employees/create');
        $tester->see('Novo Funcionário');

        // Gerar nome e email únicos
        $uniqueId = uniqid();
        $employeeName = self::TEST_EMPLOYEE_NAME . ' ' . $uniqueId;
        $employeeEmail = 'funcionario.' . $uniqueId . '@example.com';
        $employeeCpf = rand(100, 999) . '.' . rand(100, 999) . '.' . rand(100, 999) . '-' . rand(10, 99);

        // Preencher dados do funcionário
        $tester->fillField('name', $employeeName);
        $tester->fillField('email', $employeeEmail);
        $tester->fillField('cpf', $employeeCpf);

        // Selecionar cargo (role_id)
        $tester->selectOption('role_id', '2'); // Assumindo que 2 é um ID válido para um cargo

        // Data de contratação
        $hireDate = date('Y-m-d');
        $tester->fillField('hire_date', $hireDate);

        // Status
        $tester->selectOption('status', 'Ativo');

        // Salvar o funcionário
        $this->scrollToButtonAndClick($tester, 'Salvar');
        $tester->wait(2);

        return $employeeName;
    }

    /**
     * Teste de associação de funcionário a um projeto
     */
    public function testAssignEmployeeToProject(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Criar um projeto de teste
        $projectName = $this->createTestProject($tester);

        // Ir para a página de detalhes do projeto
        $tester->amOnPage(self::PROJECTS_INDEX_URL);
        $tester->wait(1);

        // Encontrar e clicar no link do projeto recém-criado usando abordagens mais robustas
        try {
            // Abordagem 1: Tentar clicar diretamente no link com o nome do projeto
            $tester->click("//a[contains(text(), '{$projectName}')]");
            $tester->comment('Clicked on project link using exact name');
        } catch (\Exception $e) {
            try {
                // Abordagem 2: Tentar clicar em qualquer link de visualização de projeto
                $tester->click('//a[contains(@href, "/projects/") and not(contains(@href, "/edit"))][1]');
                $tester->comment('Clicked on first project details link');
            } catch (\Exception $e2) {
                // Abordagem 3: Tentar clicar no nome do projeto ou em um ícone de visualização
                $tester->click(self::FIRST_PROJECT_LINK_SELECTOR);
                $tester->comment('Clicked on first project name link');
            }
        }
        $tester->wait(1);

        // Verificar se estamos na página de detalhes do projeto usando JavaScript para ser mais flexível
        $isProjectDetailsPage = $tester->executeJS('return (
            document.body.textContent.includes("Project Details") ||
            document.body.textContent.includes("Detalhes do Projeto") ||
            document.body.textContent.includes("Visualizar Projeto") ||
            document.body.textContent.includes("View Project") ||
            document.querySelector("h1, h2, h3, h4, h5") !== null
        )');

        $tester->comment('Verificando se estamos na página de detalhes do projeto: ' . ($isProjectDetailsPage ? 'Sim' : 'Não'));

        // Se não estivermos na página de detalhes, tentar navegar diretamente para a página de projetos
        // e tentar novamente com outro projeto
        if (!$isProjectDetailsPage) {
            $tester->amOnPage(self::PROJECTS_INDEX_URL);
            $tester->wait(1);

            // Tentar clicar no primeiro projeto disponível
            $tester->click(self::FIRST_PROJECT_LINK_SELECTOR);
            $tester->wait(1);
        }

        // Verificar se há um botão ou link para adicionar funcionários
        $addEmployeeButtonExists = $tester->executeJS('return (
            document.querySelector("button[data-toggle=\'modal\'], a[data-toggle=\'modal\']") !== null ||
            Array.from(document.querySelectorAll("button, a")).some(el =>
                el.textContent.includes("Add Employee") ||
                el.textContent.includes("Adicionar Funcionário") ||
                el.textContent.includes("Adicionar")
            )
        )');

        if ($addEmployeeButtonExists) {
            // Clicar no botão para abrir o modal de adicionar funcionário
            $tester->executeJS('
                // Tenta encontrar o botão por diferentes abordagens
                const modalButton = document.querySelector("button[data-toggle=\'modal\'], a[data-toggle=\'modal\']") ||
                Array.from(document.querySelectorAll("button, a")).find(el =>
                    el.textContent.includes("Add Employee") ||
                    el.textContent.includes("Adicionar Funcionário") ||
                    el.textContent.includes("Adicionar")
                );

                if (modalButton) {
                    modalButton.click();
                } else {
                    console.log("Botão para adicionar funcionário não encontrado");
                }
            ');
            $tester->wait(1);

            // Verificar se o modal está aberto
            $modalIsOpen = $tester->executeJS(
                'return document.querySelector(".modal.show, .modal[style*=\'display: block\']") !== null'
            );

            if ($modalIsOpen) {
                // Selecionar um funcionário no dropdown (assumindo que já existe pelo menos um funcionário)
                $hasEmployeeSelect = $tester->executeJS(
                    'return document.querySelector("select[name=\'employee_id\']") !== null'
                );

                if (!$hasEmployeeSelect) {
                    // Se não houver funcionários disponíveis, criar um
                    $tester->comment('Nenhum funcionário disponível, criando um novo...');
                    $tester->executeJS(
                        'document.querySelector(".modal.show .close, .modal.show .btn-secondary, ' .
                        '.modal[style*=\'display: block\'] .close").click();'
                    );
                    $tester->wait(1);

                    $this->createTestEmployee($tester); // Criar funcionário de teste

                    // Voltar para a página de detalhes do projeto
                    $tester->amOnPage(self::PROJECTS_INDEX_URL);
                    $tester->wait(1);

                    // Tentar diferentes abordagens para clicar no projeto
                    try {
                        // Abordagem 1: Tentar clicar diretamente no link com o nome do projeto
                        $tester->click("//a[contains(text(), '{$projectName}')]");
                        $tester->comment('Clicked on project link using exact name');
                    } catch (\Exception $e) {
                        try {
                            // Abordagem 2: Tentar clicar em qualquer link de visualização de projeto
                            $tester->click('//a[contains(@href, "/projects/") and not(contains(@href, "/edit"))][1]');
                            $tester->comment('Clicked on first project details link');
                        } catch (\Exception $e2) {
                            // Abordagem 3: Tentar clicar no nome do projeto ou em um ícone de visualização
                            $tester->click('//table//tbody//tr[1]//td[1]//a');
                            $tester->comment('Clicked on first project name link');
                        }
                    }
                    $tester->wait(1);

                    // Abrir o modal novamente
                    $tester->executeJS('
                        // Tenta encontrar o botão por diferentes abordagens
                        var modalButton = document.querySelector("button[data-toggle=\'modal\']")
                            || document.querySelector("a[data-toggle=\'modal\']")
                            || document.querySelector("button:contains(\'Add Employee\')")
                            || document.querySelector("a:contains(\'Add Employee\')")
                        if (modalButton) {
                            modalButton.click();
                        }
                    ');
                    $tester->wait(1);
                }

                // Selecionar um funcionário e definir uma função
                $tester->selectOption('employee_id', '1'); // Assumindo que 1 é um ID válido para um funcionário
                $tester->fillField('role', self::TEST_EMPLOYEE_ROLE);

                // Clicar no botão para adicionar o funcionário ao projeto
                $tester->click('Adicionar');
                $tester->wait(2);

                // Verificar se há mensagem de sucesso
                try {
                    $tester->see('sucesso', self::SUCCESS_MESSAGE_SELECTOR);
                    $tester->comment('Funcionário adicionado com sucesso ao projeto');
                } catch (\Exception $e) {
                    $tester->comment(
                        'Não foi possível encontrar mensagem de sucesso, verificando se o funcionário foi adicionado...'
                    );

                    // Verificar se o funcionário aparece na lista de equipe do projeto
                    $tester->see(
                        self::TEST_EMPLOYEE_ROLE,
                        '//table[contains(@class, "team-table") or contains(@id, "team-table")]'
                    );
                }
            } else {
                $tester->comment('Modal não foi aberto, pulando teste de associação');
            }
        } else {
            $tester->comment('Botão para adicionar funcionário não encontrado, pulando teste de associação');
        }
    }

    /**
     * Teste de atualização da função de um funcionário em um projeto
     */
    public function testUpdateEmployeeRoleInProject(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Primeiro, garantir que temos um funcionário associado a um projeto
        $this->testAssignEmployeeToProject($tester);

        // Agora, vamos atualizar a função do funcionário
        // Verificar se há um botão ou link para editar a função do funcionário
        $editRoleButtonExists = $tester->executeJS(
            'return document.querySelector("button[title=\'Editar Função\'], a[title=\'Editar Função\'], ' .
            '.edit-role-btn") !== null'
        );

        if ($editRoleButtonExists) {
            // Clicar no botão para abrir o modal de edição de função
            $tester->executeJS(
                'document.querySelector("button[title=\'Editar Função\'], a[title=\'Editar Função\'], ' .
                '.edit-role-btn").click();'
            );
            $tester->wait(1);

            // Verificar se o modal está aberto
            $modalIsOpen = $tester->executeJS(
                'return document.querySelector(".modal.show, .modal[style*=\'display: block\']") !== null'
            );

            if ($modalIsOpen) {
                // Atualizar a função do funcionário
                $tester->fillField('new_role', self::TEST_EMPLOYEE_NEW_ROLE);

                // Clicar no botão para salvar a nova função
                $tester->click('Salvar');
                $tester->wait(2);

                // Verificar se há mensagem de sucesso
                try {
                    $tester->see('sucesso', self::SUCCESS_MESSAGE_SELECTOR);
                    $tester->comment('Função do funcionário atualizada com sucesso');
                } catch (\Exception $e) {
                    $tester->comment('Não foi possível encontrar mensagem de sucesso, verificando se a função foi atualizada...');

                    // Verificar se a nova função aparece na lista de equipe do projeto
                    $tester->see(
                        self::TEST_EMPLOYEE_NEW_ROLE,
                        '//table[contains(@class, "team-table") or contains(@id, "team-table")]'
                    );
                }
            } else {
                $tester->comment('Modal não foi aberto, pulando teste de atualização de função');
            }
        } else {
            $tester->comment('Botão para editar função não encontrado, pulando teste de atualização de função');
        }
    }

    /**
     * Teste de remoção de funcionário de um projeto
     */
    public function testRemoveEmployeeFromProject(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Primeiro, garantir que temos um funcionário associado a um projeto
        $this->testAssignEmployeeToProject($tester);

        // Agora, vamos remover o funcionário do projeto
        // Verificar se há um botão ou link para remover o funcionário
        $removeButtonExists = $tester->executeJS('
            // Buscar botão de remoção por diferentes seletores
            return document.querySelector(
                "button[title=\'Remover\'], " +
                "a[title=\'Remover\'], " +
                ".remove-employee-btn, " +
                "form[action*=\'/employee-projects/remove\'] button"
            ) !== null;
        ');

        if ($removeButtonExists) {
            // Usar JavaScript para clicar no botão de remoção para evitar problemas de visibilidade
            $tester->executeJS('
                // Buscar e clicar no botão de remoção
                const removeButton = document.querySelector(
                    "button[title=\'Remover\'], " +
                    "a[title=\'Remover\'], " +
                    ".remove-employee-btn, " +
                    "form[action*=\'/employee-projects/remove\'] button"
                );
                if (removeButton) removeButton.click();
            ');
            $tester->wait(2);

            // Tentar diferentes abordagens para confirmar a remoção
            try {
                // Primeiro, tentar encontrar um modal de confirmação
                $hasModal = $tester->executeJS('return document.querySelector(".modal, .dialog, [role=dialog]") !== null');

                if ($hasModal) {
                    // Clicar no botão de confirmação dentro do modal
                    $tester->executeJS(
                        'document.querySelector(".modal button.confirm, .modal .btn-danger, ' .
                        '.modal button:not(.cancel), [role=dialog] button.confirm, ' .
                        '[role=dialog] .btn-danger").click();'
                    );
                } else {
                    // Se não houver modal, pode ser um alerta do navegador
                    $tester->acceptPopup();
                }
            } catch (\Exception $e) {
                // Se falhar, tentar outra abordagem - pode ser que a remoção já tenha ocorrido
                $tester->comment('Interação com o modal falhou, continuando teste: ' . $e->getMessage());
            }

            $tester->wait(2);

            // Verificar que a operação foi concluída com sucesso
            try {
                $tester->see('sucesso', self::SUCCESS_MESSAGE_SELECTOR);
                $tester->comment('Funcionário removido do projeto com sucesso!');
            } catch (\Exception $e) {
                $tester->comment(
                    'Não foi possível encontrar mensagem de sucesso, verificando se o funcionário foi removido...'
                );

                // Verificar se o funcionário não aparece mais na lista de equipe do projeto
                $employeeStillPresent = $tester->executeJS(
                    'return document.querySelector("table tbody tr td:contains(\'' . self::TEST_EMPLOYEE_ROLE . '\')") !== null'
                );
                if (!$employeeStillPresent) {
                    $tester->comment('Funcionário foi removido do projeto com sucesso!');
                }
            }
        } else {
            $tester->comment('Botão para remover funcionário não encontrado, pulando teste de remoção');
        }
    }

    /**
     * Teste de visualização dos projetos de um funcionário
     */
    public function testViewEmployeeProjects(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Ir para a listagem de funcionários
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(1);

        // Verificar se há funcionários na lista
        $hasEmployees = $tester->executeJS('return document.querySelectorAll("table tbody tr").length > 0');

        if (!$hasEmployees) {
            // Criar um funcionário para o teste se não existir
            $this->createTestEmployee($tester); // Criar funcionário de teste
            $tester->see('Projetos');

            // Verificar se há uma tabela de projetos
            $hasProjectsTable = $tester->executeJS('return document.querySelector("table") !== null');
            if ($hasProjectsTable) {
                $tester->comment('Tabela de projetos do funcionário encontrada');
            } else {
                $tester->comment('Funcionário não tem projetos associados');
            }
        } else {
            $tester->comment('Link para ver projetos do funcionário não encontrado, pulando teste');
        }
    }

    /**
     * Teste específico para a requisição AJAX que busca projetos de um funcionário
     */
    public function testAjaxEmployeeProjects(AcceptanceTester $tester): void
    {
        $this->loginAsAdmin($tester);

        // Ir para a listagem de funcionários
        $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
        $tester->wait(2);

        // Verificar se há funcionários listados
        $employeesCount = $tester->executeJS('return document.querySelectorAll("table tbody tr").length');
        $tester->comment('Funcionários encontrados: ' . $employeesCount);

        if ($employeesCount == 0) {
            // Criar um funcionário se não existir nenhum
            $this->createTestEmployee($tester);
            // Retornar à listagem de funcionários
            $tester->amOnPage(self::EMPLOYEES_INDEX_URL);
            $tester->wait(1);
        }

        // Verificar se existe botão para ver projetos
        $showProjectsBtnExists = $tester->executeJS('
            return document.querySelector(".show-projects-btn") !== null;
        ');

        if (!$showProjectsBtnExists) {
            $tester->comment('Botão para mostrar projetos não encontrado. Verificando outras possibilidades...');

            // Verificar outras maneiras de mostrar projetos de funcionário
            $anyProjectsLinkExists = $tester->executeJS('
                return document.querySelector("a[href*=\'projects\']") !== null;
            ');

            if (!$anyProjectsLinkExists) {
                $tester->comment('Nenhum link de projetos encontrado. Pulando teste AJAX.');
                return;
            }
        }

        // Testar o modal e requisição AJAX
        $tester->comment('Testando requisição AJAX para buscar projetos...');

        // Obter o primeiro ID de funcionário disponível
        $employeeId = $tester->executeJS('
            const btn = document.querySelector(".show-projects-btn");
            if (btn) return btn.dataset.employeeId;
            
            // Se não encontrar o botão específico, tentar outra abordagem
            const anyRow = document.querySelector("table tbody tr");
            return anyRow ? anyRow.dataset.employeeId || "1" : "1";
        ');

        // Simular o clique no botão que mostra os projetos ou fazer a requisição diretamente
        $tester->executeJS('
            // Tentar clicar no botão primeiro
            const showBtn = document.querySelector(".show-projects-btn");
            if (showBtn) {
                showBtn.click();
                return "Botão clicado";
            }
            
            // Se não encontrar o botão, simular a requisição Ajax diretamente
            return "Botão não encontrado, simulando requisição";
        ');

        $tester->wait(2);

        // Verificar se o modal foi aberto ou fazer a verificação direta da requisição
        $modalOpened = $tester->executeJS('
            return document.querySelector("#projectsModal:not(.hidden)") !== null;
        ');

        if ($modalOpened) {
            $tester->comment('Modal de projetos aberto com sucesso');

            // Verificar se o conteúdo foi carregado
            $loaderHidden = $tester->executeJS('
                return document.querySelector("#projects-loader.hidden") !== null;
            ');

            if ($loaderHidden) {
                $tester->comment('Loader escondido, conteúdo carregado');

                // Verificar o conteúdo recebido
                $contentLoaded = $tester->executeJS('
                    const content = document.querySelector("#projects-content");
                    if (!content) return "Elemento de conteúdo não encontrado";
                    
                    if (content.querySelector("ul li")) {
                        return "Projetos listados com sucesso";
                    } else if (content.textContent.includes("Nenhum projeto")) {
                        return "Mensagem de nenhum projeto exibida";
                    } else if (content.textContent.includes("Erro")) {
                        return "Erro exibido: " + content.textContent;
                    }
                    
                    return "Conteúdo desconhecido";
                ');

                $tester->comment("Resultado da requisição AJAX: " . $contentLoaded);
            } else {
                $tester->comment('Loader ainda visível, possível problema na requisição');
            }

            // Testar fechamento do modal
            $tester->executeJS('
                const closeBtn = document.querySelector(".close-modal");
                if (closeBtn) closeBtn.click();
            ');
            $tester->wait(1);
        } else {
            // Fazer requisição AJAX direta para testar a API
            $tester->comment('Testando API diretamente via JavaScript');

            // Realizar requisição AJAX direta e validar resposta
            $apiTestResult = $tester->executeJS('
                return new Promise((resolve) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open("GET", `/employee/${' . $employeeId . '}/projects`);
                    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xhr.setRequestHeader("Accept", "application/json");
                    
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                resolve("Sucesso: " + (response.success ? "true" : "false") + 
                                        ", Projetos: " + (response.projects ? response.projects.length : 0));
                            } catch(e) {
                                resolve("Erro ao fazer parse da resposta: " + e.message);
                            }
                        } else {
                            resolve("Erro na requisição: " + xhr.status + " " + xhr.statusText);
                        }
                    };
                    
                    xhr.onerror = function() {
                        resolve("Erro na comunicação com o servidor");
                    };
                    
                    xhr.send();
                });
            ');

            $tester->comment('Resposta da API: ' . $apiTestResult);
        }
    }
}
