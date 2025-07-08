/**
 * Constantes para classes e strings reutilizáveis
 */
const CSS_CLASSES = {
  HIDDEN: 'hidden',
};

const STRINGS = {
  EMPLOYEE_PROJECTS: 'Projetos de ',
  ACTIVE_STATUS: 'Active',
  ACTIVE_TRANSLATED: 'Ativo',
  NO_DESCRIPTION: 'Sem descrição',
};

/**
 * Funções utilitárias para manipulação do DOM
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
 * Templates HTML pré-definidos para evitar aninhamento excessivo
 * e melhorar a manutenção do código
 * @type {Object}
 */
const PROJECT_TEMPLATES = {
  /**
   * Template para item de projeto individual
   * @param {string} name - Nome do projeto
   * @param {string} statusClass - Classes CSS para o status
   * @param {string} formattedStatus - Texto do status formatado
   * @param {string} description - Descrição do projeto
   * @param {string} roleHtml - HTML da função do funcionário
   * @returns {string} HTML do item de projeto
   */
  projectItem: (name, statusClass, formattedStatus, description, roleHtml) => `
    <li class="bg-white/60 p-3 rounded-lg border border-gray-100">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-gray-800">${name}</span>
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
          ${formattedStatus}
        </span>
      </div>
      <p class="text-gray-600 mt-1 text-sm">${
        description || STRINGS.NO_DESCRIPTION
      }</p>
      ${roleHtml}
    </li>
  `,

  /**
   * Template para quando não há projetos para mostrar
   */
  emptyState: `
    <div class="flex flex-col items-center justify-center py-8 text-center">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
        <i class="fas fa-briefcase text-blue-500 text-xl"></i>
      </div>
      <h4 class="text-gray-800 font-medium mb-1">Nenhum projeto encontrado</h4>
      <p class="text-gray-500 text-sm">Este funcionário não está associado a nenhum projeto.</p>
    </div>
  `,

  /**
   * Template para exibição de erros
   * @param {string} errorMessage - Mensagem de erro
   * @param {string|number} employeeId - ID do funcionário
   * @returns {string} HTML da mensagem de erro
   */
  errorState: (errorMessage, employeeId) => `
    <div class="flex flex-col items-center justify-center py-8 text-center">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
      </div>
      <h4 class="text-gray-800 font-medium mb-1">Erro ao carregar projetos</h4>
      <p class="text-red-600 text-sm">${errorMessage}</p>
      <p class="text-gray-500 text-xs mt-2">ID do funcionário: ${employeeId}</p>
      <button type="button" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" 
              onclick="location.reload()">
        Tentar novamente
      </button>
    </div>
  `,

  /**
   * Templates para marcadores da lista de projetos
   */
  listStart: '<ul class="space-y-3">',
  listEnd: '</ul>',
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
 * Cria o HTML da lista de projetos
 * @param {Array} projects - Array de objetos com dados dos projetos
 * @returns {string} HTML da lista de projetos formatada
 */
function createProjectsList(projects) {
  if (!projects || !Array.isArray(projects)) {
    console.error('Dados de projetos inválidos:', projects);
    return PROJECT_TEMPLATES.emptyState;
  }

  let html = PROJECT_TEMPLATES.listStart;

  // Usar for-of em vez de for com índice (melhor prática)
  for (const project of projects) {
    const statusClass = getStatusClass(project.status);
    const formattedStatus = formatStatus(project.status);
    const roleHtml = prepareRoleHtml(project.role);

    html += PROJECT_TEMPLATES.projectItem(
      project.name || 'Projeto sem nome',
      statusClass,
      formattedStatus,
      project.description,
      roleHtml,
    );
  }

  html += PROJECT_TEMPLATES.listEnd;
  return html;
}

/**
 * Funções de manipulação de resposta da API
 */

/**
 * Processa uma resposta de erro da API
 * @param {Response} response - O objeto de resposta HTTP
 * @returns {Promise<never>} Uma promessa rejeitada com detalhes do erro
 */
function processErrorResponse(response) {
  return response.text().then((text) => {
    console.error('Erro na resposta:', text);
    throw new Error(`Erro ${response.status}: ${text}`);
  });
}

/**
 * Renderiza o conteúdo da resposta no DOM
 * @param {Object} data - Os dados da resposta da API
 * @param {HTMLElement} projectsContent - O elemento onde renderizar o conteúdo
 */
function renderResponseContent(data, projectsContent) {
  try {
    if (
      data.projects &&
      Array.isArray(data.projects) &&
      data.projects.length > 0
    ) {
      projectsContent.innerHTML = createProjectsList(data.projects);
    } else {
      projectsContent.innerHTML = PROJECT_TEMPLATES.emptyState;
    }
  } catch (error) {
    console.error('Erro ao renderizar conteúdo:', error);
    projectsContent.innerHTML = `
      <div class="p-4 text-center text-red-600">
        <p>Ocorreu um erro ao processar os dados. Por favor, tente novamente.</p>
      </div>
    `;
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
  projectsContent.innerHTML = PROJECT_TEMPLATES.errorState(
    error.message || 'Erro desconhecido ao carregar projetos',
    employeeId,
  );
}

/**
 * Cache para armazenar temporariamente as respostas de projetos por ID de funcionário
 * Isso evita requisições repetidas para os mesmos dados em curtos períodos de tempo
 */
const projectsCache = new Map();

/**
 * Tempo de expiração do cache em milissegundos (5 minutos)
 */
const CACHE_EXPIRATION_TIME = 5 * 60 * 1000;

/**
 * URL base para a API de projetos de funcionários
 */
const API_URL_BASE = '/employee/';

/**
 * Função principal para buscar projetos de um funcionário
 * Implementa cache para melhorar performance
 *
 * @param {string|number} employeeId - ID do funcionário
 * @param {HTMLElement} projectsLoader - Elemento de loading
 * @param {HTMLElement} projectsContent - Elemento onde o conteúdo será renderizado
 * @returns {Promise} Promessa que resolve quando os dados são carregados
 */
function fetchEmployeeProjects(employeeId, projectsLoader, projectsContent) {
  // Verificar cache primeiro
  const now = Date.now();
  const cachedData = projectsCache.get(employeeId);

  if (cachedData && now - cachedData.timestamp < CACHE_EXPIRATION_TIME) {
    console.log('Usando dados do cache para funcionário:', employeeId);
    // Usar dados em cache
    setTimeout(() => {
      handleSuccessResponse(cachedData.data, projectsLoader, projectsContent);
    }, 300); // Pequeno delay para melhor experiência do usuário
    return Promise.resolve(cachedData.data);
  }

  // Se não estiver em cache ou expirado, fazer requisição
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
      // Armazenar no cache
      projectsCache.set(employeeId, {
        data,
        timestamp: Date.now(),
      });

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
