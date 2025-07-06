document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('projectsModal');
  const modalEmployeeName = document.getElementById('modal-employee-name');
  const projectsLoader = document.getElementById('projects-loader');
  const projectsContent = document.getElementById('projects-content');
  const closeButtons = document.querySelectorAll('.close-modal');

  // Adicionar ouvintes para os botões que abrem o modal de projetos
  const showProjectsButtons = document.querySelectorAll('.show-projects-btn');

  showProjectsButtons.forEach((button) => {
    button.addEventListener('click', function () {
      const employeeId = this.dataset.employeeId;
      const employeeName = this.parentElement
        .querySelector('div')
        .textContent.trim();

      // Mostrar o modal
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Impedir rolagem no fundo

      // Atualizar o título com o nome do funcionário
      modalEmployeeName.textContent = 'Projetos de ' + employeeName;

      // Mostrar o loader e esconder o conteúdo
      projectsLoader.classList.remove('hidden');
      projectsContent.classList.add('hidden');

      // Buscar os projetos via AJAX
      fetch(`/employee/${employeeId}/projects`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
      })
        .then((response) => {
          console.log('Status da resposta:', response.status);
          console.log('Response OK:', response.ok);

          if (!response.ok) {
            // Tentar ler a resposta como texto para ver o erro específico
            return response.text().then((text) => {
              console.error('Erro na resposta:', text);
              throw new Error(`Erro ${response.status}: ${text}`);
            });
          }
          return response.json();
        })
        .then((data) => {
          // Esconder o loader
          projectsLoader.classList.add('hidden');
          projectsContent.classList.remove('hidden');

          // Renderizar os projetos
          if (data.projects && data.projects.length > 0) {
            let html = '<ul class="space-y-3">';

            data.projects.forEach((project) => {
              const statusClass =
                project.status === 'Active' || project.status === 'Ativo'
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-800';

              html += `
                  <li class="bg-white/60 p-3 rounded-lg border border-gray-100">
                    <div class="flex items-center justify-between">
                      <span class="font-semibold text-gray-800">${
                        project.name
                      }</span>
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                        ${
                          project.status === 'Active'
                            ? 'Ativo'
                            : project.status || 'Inativo'
                        }
                      </span>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm">${
                      project.description || 'Sem descrição'
                    }</p>
                    ${
                      project.role
                        ? `<p class="text-blue-600 mt-1 text-xs font-medium">Papel: ${project.role}</p>`
                        : ''
                    }
                  </li>
                `;
            });

            html += '</ul>';
            projectsContent.innerHTML = html;
          } else {
            projectsContent.innerHTML = `
                <div class="flex flex-col items-center justify-center py-8 text-center">
                  <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-briefcase text-blue-500 text-xl"></i>
                  </div>
                  <h4 class="text-gray-800 font-medium mb-1">Nenhum projeto encontrado</h4>
                  <p class="text-gray-500 text-sm">Este funcionário não está associado a nenhum projeto.</p>
                </div>
              `;
          }
        })
        .catch((error) => {
          console.error('Erro:', error);

          // Esconder o loader
          projectsLoader.classList.add('hidden');
          projectsContent.classList.remove('hidden');

          // Mostrar mensagem de erro
          projectsContent.innerHTML = `
              <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                  <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <h4 class="text-gray-800 font-medium mb-1">Erro ao carregar projetos</h4>
                <p class="text-red-600 text-sm">${error.message}</p>
                <p class="text-gray-500 text-xs mt-2">ID do funcionário: ${employeeId}</p>
              </div>
            `;
          console.error('Erro:', error);
        });
    });
  });

  // Fechar o modal quando clicar nos botões de fechar
  closeButtons.forEach((button) => {
    button.addEventListener('click', function () {
      modal.classList.add('hidden');
      document.body.style.overflow = ''; // Restaurar rolagem
    });
  });

  // Fechar o modal quando clicar fora dele
  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      modal.classList.add('hidden');
      document.body.style.overflow = ''; // Restaurar rolagem
    }
  });

  // Fechar o modal com a tecla ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      modal.classList.add('hidden');
      document.body.style.overflow = ''; // Restaurar rolagem
    }
  });
});
