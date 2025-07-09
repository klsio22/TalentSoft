/**
 * ==========================================
 * CONSTANTES E CONFIGURAÇÕES
 * ==========================================
 */

/**
 * URL base para a API de projetos de funcionários
 * @const {string}
 */
const API_URL_BASE = '/employee/';

/**
 * Tempo de expiração do cache em milissegundos (5 minutos)
 * @const {number}
 */
const CACHE_EXPIRATION_TIME = 5 * 60 * 1000;

/**
 * Classes CSS reutilizáveis
 * @const {Object}
 */
const CSS_CLASSES = {
  HIDDEN: 'hidden',
};

/**
 * Strings reutilizáveis para internacionalização e consistência
 * @const {Object}
 */
const STRINGS = {
  EMPLOYEE_PROJECTS: 'Projetos de ',
  ACTIVE_STATUS: 'Active',
  ACTIVE_TRANSLATED: 'Ativo',
  NO_DESCRIPTION: 'Sem descrição disponível',
  EMPLOYEE_NOT_AVAILABLE: 'Nome não disponível',
  PROJECT_COUNT: 'Total de projetos:',
  EMPLOYEE_LABEL: 'Funcionário:',
  ROLE_LABEL: 'Função:',
  PROJECT_NO_NAME: 'Projeto sem nome',
};

/**
 * ==========================================
 * UTILITÁRIOS DE MANIPULAÇÃO DO DOM
 * ==========================================
 */

/**
 * Mostra um modal na interface
 * @param {HTMLElement} modal - O elemento do modal a ser exibido
 */
function showModal(modal) {
  modal.classList.remove(CSS_CLASSES.HIDDEN);
  document.body.style.overflow = 'hidden'; // Impedir rolagem no fundo
}

/**
 * Esconde um modal da interface
 * @param {HTMLElement} modal - O elemento do modal a ser escondido
 */
function hideModal(modal) {
  modal.classList.add(CSS_CLASSES.HIDDEN);
  document.body.style.overflow = ''; // Restaurar rolagem
}

/**
 * Mostra um loader e esconde o conteúdo
 * @param {HTMLElement} loader - O elemento de loader a ser exibido
 * @param {HTMLElement} content - O elemento de conteúdo a ser escondido
 */
function showLoader(loader, content) {
  loader.classList.remove(CSS_CLASSES.HIDDEN);
  content.classList.add(CSS_CLASSES.HIDDEN);
}

/**
 * Esconde um loader e mostra o conteúdo
 * @param {HTMLElement} loader - O elemento de loader a ser escondido
 * @param {HTMLElement} content - O elemento de conteúdo a ser exibido
 */
function hideLoader(loader, content) {
  loader.classList.add(CSS_CLASSES.HIDDEN);
  content.classList.remove(CSS_CLASSES.HIDDEN);
}

/**
  PROJECT_NO_NAME: 'Projeto sem nome'
};

/**
 * Funções utilitárias para formatação e estilização
 */

/**
 * Obtém a classe CSS apropriada para o status do projeto
 * @param {string} status - O status do projeto
 * @returns {string} A classe CSS a ser aplicada
 */
function getStatusClass(status) {
  return status === STRINGS.ACTIVE_STATUS ||
    status === STRINGS.ACTIVE_TRANSLATED
    ? 'bg-green-100 text-green-800'
    : 'bg-gray-100 text-gray-800';
}

/**
 * Formata o status do projeto para exibição
 * @param {string} status - O status do projeto
 * @returns {string} O status formatado em português
 */
function formatStatus(status) {
  return status === STRINGS.ACTIVE_STATUS
    ? STRINGS.ACTIVE_TRANSLATED
    : status || 'Inativo';
}

/**
 * Funções para renderização de dados
 */

/**
 * Prepara o HTML para exibir a função do funcionário no projeto
 * @param {string|null} role - A função do funcionário no projeto
 * @returns {string} HTML para exibir a função ou string vazia
 */
function prepareRoleHtml(role) {
  if (!role) return '';
  return `<p class="text-blue-600 mt-1 text-xs font-medium">Papel: ${role}</p>`;
}

/**
 * Cria elementos de projeto a partir do template e dados
 * @param {Array} projects - Array de objetos com dados dos projetos
 * @param {HTMLElement} projectsContent - Elemento onde os projetos serão inseridos
 */
function createProjectsList(projects, projectsContent) {
  if (!projects || !Array.isArray(projects) || projects.length === 0) {
    console.error('Dados de projetos inválidos ou vazios:', projects);
    // Usa o template de estado vazio
    const emptyTemplate = document.querySelector('#empty-projects-template');
    if (emptyTemplate) {
      projectsContent.appendChild(emptyTemplate.content.cloneNode(true));
    }
    return;
  }

  // Usa o template principal de projetos
  const projectTemplate = document.querySelector('#project-template');
  if (!projectTemplate) {
    console.error('Template de projetos não encontrado');
    return;
  }

  // Clona o template principal
  const content = projectTemplate.content.cloneNode(true);

  // Obtém a lista de projetos do template
  const projectsList = content.querySelector('#projects-list');

  // Para cada projeto, cria um item usando o template de item
  projects.forEach((project) => {
    // Obtém o template de item de projeto
    const projectItemTemplate = document.querySelector(
      '#project-item-template',
    );
    if (!projectItemTemplate) {
      console.error('Template de item de projeto não encontrado');
      return;
    }

    // Clona o template de item
    const projectItem = projectItemTemplate.content.cloneNode(true);
    const projectLi = projectItem.querySelector('li');

    // Nome do projeto
    const nameElement = projectItem.querySelector('[data-project-name]');
    if (nameElement) {
      nameElement.textContent = project.name || STRINGS.PROJECT_NO_NAME;
    }

    // Status do projeto
    const statusElement = projectItem.querySelector('[data-project-status]');
    if (statusElement) {
      statusElement.className = `inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mb-2 ${getStatusClass(
        project.status,
      )}`;
    }

    const statusTextElement = projectItem.querySelector('[data-status-text]');
    if (statusTextElement) {
      statusTextElement.textContent = formatStatus(project.status);
    }

    // Descrição do projeto
    const descriptionElement = projectItem.querySelector(
      '[data-project-description]',
    );
    if (descriptionElement) {
      descriptionElement.textContent =
        project.description || STRINGS.NO_DESCRIPTION;
    }

    // Função no projeto
    const roleElement = projectItem.querySelector('[data-project-role]');
    if (roleElement) {
      if (project.role) {
        const roleTextElement = projectItem.querySelector('[data-role-text]');
        if (roleTextElement) {
          roleTextElement.textContent = project.role;
        }
      } else {
        roleElement.style.display = 'none';
      }
    }

    // Adiciona o item à lista de projetos
    projectsList.appendChild(projectLi);
  });

  // Adiciona o conteúdo ao elemento
  projectsContent.appendChild(content);
}

/**
 * Funções de manipulação de resposta da API
 */

/**
 * Processa uma resposta de erro da API
 * @param {Response} response - O objeto de resposta HTTP
 * @returns {Promise<never>} Uma promessa rejeitada com detalhes do erro
 */
async function processErrorResponse(response) {
  return response.text().then((text) => {
    console.error('Erro na resposta:', text);
    throw new Error(`Erro ${response.status}: ${text}`);
  });
}

/**
 * Renderiza o conteúdo da resposta no DOM usando templates HTML
 * @param {Object} data - Os dados da resposta da API
 * @param {HTMLElement} projectsContent - O elemento onde renderizar o conteúdo
 */
function renderResponseContent(data, projectsContent) {
  try {
    // Limpa o conteúdo atual
    projectsContent.innerHTML = '';

    // Verifica se há projetos para exibir
    if (
      data.projects &&
      Array.isArray(data.projects) &&
      data.projects.length > 0
    ) {
      // Usa a função createProjectsList para criar e preencher os templates
      createProjectsList(data.projects, projectsContent);

      // Atualiza os dados do funcionário e contador de projetos no template principal
      const employeeNameElement = projectsContent.querySelector(
        '#employee-name',
      );
      if (employeeNameElement) {
        employeeNameElement.textContent = `${STRINGS.EMPLOYEE_LABEL} ${
          data.employee || STRINGS.EMPLOYEE_NOT_AVAILABLE
        }`;
      }

      const projectCountElement = projectsContent.querySelector(
        '#project-count',
      );
      if (projectCountElement) {
        projectCountElement.textContent = `${STRINGS.PROJECT_COUNT} ${
          data.project_count || data.projects.length
        }`;
      }
    } else {
      // Exibe o template de estado vazio
      const emptyTemplate = document.querySelector('#empty-projects-template');
      if (emptyTemplate) {
        projectsContent.appendChild(emptyTemplate.content.cloneNode(true));
      }
    }
  } catch (error) {
    console.error('Erro ao renderizar conteúdo:', error);
    // Exibe o template de erro
    const errorTemplate = document.querySelector('#error-template');
    if (errorTemplate) {
      projectsContent.appendChild(errorTemplate.content.cloneNode(true));
    } else {
      projectsContent.innerHTML = `
        <div class="p-4 text-center text-red-600">
          <p>Ocorreu um erro ao processar os dados. Por favor, tente novamente.</p>
        </div>
      `;
    }
  }
}

/**
 * Manipula uma resposta bem-sucedida da API
 * @param {Object} data - Os dados da resposta
 * @param {HTMLElement} projectsLoader - O elemento loader
 * @param {HTMLElement} projectsContent - O elemento de conteúdo
 */
function handleSuccessResponse(data, projectsLoader, projectsContent) {
  hideLoader(projectsLoader, projectsContent);
  renderResponseContent(data, projectsContent);
}

/**
 * Manipula um erro na requisição
 * @param {Error} error - O objeto de erro
 * @param {string|number} employeeId - O ID do funcionário
 * @param {HTMLElement} projectsLoader - O elemento loader
 * @param {HTMLElement} projectsContent - O elemento de conteúdo
 */
function handleErrorCase(error, employeeId, projectsLoader, projectsContent) {
  console.error('Erro:', error);
  hideLoader(projectsLoader, projectsContent);

  // Usa o template de erro do PHP
  const errorTemplate = document.querySelector('#error-template');
  if (errorTemplate) {
    const errorContent = errorTemplate.content.cloneNode(true);

    // Preenche os dados de erro no template
    const errorMessage = errorContent.querySelector('[data-error-message]');
    if (errorMessage) {
      errorMessage.textContent =
        error.message || 'Erro desconhecido ao carregar projetos';
    }

    const employeeIdElement = errorContent.querySelector('[data-employee-id]');
    if (employeeIdElement) {
      employeeIdElement.textContent = employeeId;
    }

    // Limpa o conteúdo anterior e adiciona o template de erro
    projectsContent.innerHTML = '';
    projectsContent.appendChild(errorContent);
  } else {
    // Fallback caso o template não seja encontrado
    projectsContent.innerHTML = `
      <div class="p-4 text-center text-red-600">
        <p>Ocorreu um erro ao processar os dados. Por favor, tente novamente.</p>
      </div>
    `;
  }
}

/**
 * Função principal para buscar projetos de um funcionário
 * Implementa cache para melhorar performance
 *
 * @param {string|number} employeeId - ID do funcionário
 * @param {HTMLElement} projectsLoader - Elemento de loading
 * @param {HTMLElement} projectsContent - Elemento onde o conteúdo será renderizado
 * @returns {Promise} Promessa que resolve quando os dados são carregados
 */
async function fetchEmployeeProjects(
  employeeId,
  projectsLoader,
  projectsContent,
) {
  return fetch(`${API_URL_BASE}${employeeId}/projects`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json',
      'Cache-Control': 'no-cache',
    },
  })
    .then((response) => {
      console.log('Status da resposta:', response.status);
      console.log('Response OK:', response.ok);

      return response.ok ? response.json() : processErrorResponse(response);
    })
    .then((data) => {
      handleSuccessResponse(data, projectsLoader, projectsContent);
      return data;
    })
    .catch((error) => {
      handleErrorCase(error, employeeId, projectsLoader, projectsContent);
      throw error; // Re-throw para permitir tratamento adicional se necessário
    });
}

/**
 * Função para inicializar o manipulador de cliques nos botões de projeto
 * @param {NodeListOf<Element>} buttons - Lista de botões para adicionar event listeners
 * @param {HTMLElement} modal - Elemento do modal
 * @param {HTMLElement} modalEmployeeName - Elemento para título do modal
 * @param {HTMLElement} projectsLoader - Elemento de loading
 * @param {HTMLElement} projectsContent - Elemento para conteúdo dos projetos
 */
function initProjectButtonHandlers(
  buttons,
  modal,
  modalEmployeeName,
  projectsLoader,
  projectsContent,
) {
  buttons.forEach((button) => {
    button.addEventListener('click', function () {
      // Extrair dados do funcionário
      const employeeId = this.dataset.employeeId;
      const employeeName = this.parentElement
        .querySelector('div')
        .textContent.trim();

      if (!employeeId) {
        console.error('ID de funcionário não encontrado no botão');
        return;
      }

      // Mostrar o modal
      showModal(modal);

      // Atualizar o título com o nome do funcionário
      modalEmployeeName.textContent = STRINGS.EMPLOYEE_PROJECTS + employeeName;

      // Mostrar o loader e esconder o conteúdo
      showLoader(projectsLoader, projectsContent);

      // Buscar os projetos via AJAX com tratamento de erro
      fetchEmployeeProjects(employeeId, projectsLoader, projectsContent).catch(
        (error) => {
          console.error('Erro não tratado:', error);
        },
      );
    });
  });
}

/**
 * Função para inicializar os manipuladores de fechamento do modal
 * @param {HTMLElement} modal - Elemento do modal
 * @param {NodeListOf<Element>} closeButtons - Botões para fechar o modal
 */
function initModalCloseHandlers(modal, closeButtons) {
  // Fechar o modal quando clicar nos botões de fechar
  closeButtons.forEach((button) => {
    button.addEventListener('click', () => hideModal(modal));
  });

  // Fechar o modal quando clicar fora dele
  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      hideModal(modal);
    }
  });

  // Fechar o modal com a tecla ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.classList.contains(CSS_CLASSES.HIDDEN)) {
      hideModal(modal);
    }
  });
}

/**
 * Inicialização do módulo quando o DOM estiver completamente carregado
 */
document.addEventListener('DOMContentLoaded', function () {
  // Selecionar elementos do DOM
  const modal = document.getElementById('projectsModal');
  const modalEmployeeName = document.getElementById('modal-employee-name');
  const projectsLoader = document.getElementById('projects-loader');
  const projectsContent = document.getElementById('projects-content');
  const closeButtons = document.querySelectorAll('.close-modal');
  const showProjectsButtons = document.querySelectorAll('.show-projects-btn');

  // Verificar se todos os elementos necessários existem
  if (!modal || !modalEmployeeName || !projectsLoader || !projectsContent) {
    console.error('Elementos necessários não encontrados no DOM');
    return;
  }

  // Inicializar manipuladores de eventos
  initProjectButtonHandlers(
    showProjectsButtons,
    modal,
    modalEmployeeName,
    projectsLoader,
    projectsContent,
  );
  initModalCloseHandlers(modal, closeButtons);
});
