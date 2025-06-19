<?php

/**
 * View para exibir os projetos associados ao usuário atual
 *
 * @var array $projectsWithDetails Lista de projetos com detalhes adicionais
 * @var string $title Título da página
 */
?>

<div class="min-h-[70vh] py-8">
  <!-- Cabeçalho da página -->
  <div class="glass-effect rounded-2xl shadow-xl p-8 mb-8">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent mb-2">
          <?= htmlspecialchars($title) ?>
        </h1>
        <p class="text-gray-600">Visualize todos os projetos aos quais você está associado</p>
      </div>
      <div class="flex-shrink-0">
        <div
          class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl shadow-lg">
          <i class="fas fa-project-diagram text-white text-xl"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista de projetos -->
  <?php if (empty($projectsWithDetails)) : ?>
    <div class="glass-effect rounded-2xl shadow-xl p-8 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4">
        <i class="fas fa-exclamation-circle text-yellow-600 text-2xl"></i>
      </div>
      <h2 class="text-2xl font-semibold text-gray-800 mb-2">Nenhum projeto encontrado</h2>
      <p class="text-gray-600">Você não está associado a nenhum projeto no momento.</p>
    </div>
  <?php else : ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($projectsWithDetails as $projectData) : ?>
            <?php $project = $projectData['project']; ?>
        <div class="glass-effect rounded-xl shadow-lg overflow-hidden transform transition-all hover:scale-105">
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-800 mb-1 truncate">
                  <?= htmlspecialchars($project->name) ?>
                </h2>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-500 mr-2">Sua função:</span>
                  <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                    <?= htmlspecialchars($projectData['role']) ?>
                  </span>
                </div>
              </div>
              <div class="flex-shrink-0">
                <?php
                $statusColors = [
                  'Em aberto' => 'bg-blue-100 text-blue-800',
                  'Em teste' => 'bg-purple-100 text-purple-800',
                  'Interno' => 'bg-gray-100 text-gray-800',
                  'Em andamento' => 'bg-green-100 text-green-800',
                  'Em aprovação cliente' => 'bg-yellow-100 text-yellow-800',
                  'Em aprovação interna' => 'bg-yellow-100 text-yellow-800',
                  'Em revisão' => 'bg-orange-100 text-orange-800',
                  'Em cache' => 'bg-gray-100 text-gray-800',
                  'Em espera' => 'bg-gray-100 text-gray-800',
                  'Cancelado' => 'bg-red-100 text-red-800',
                  'Em pausa' => 'bg-gray-100 text-gray-800',
                  'Concluído' => 'bg-green-100 text-green-800',
                  'Colocar em produção' => 'bg-blue-100 text-blue-800',
                  'Em Produção' => 'bg-green-100 text-green-800'
                ];
                $statusClass = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="px-2 py-1 <?= $statusClass ?> text-xs rounded-full">
                  <?= htmlspecialchars($project->status) ?>
                </span>
              </div>
            </div>

            <div class="mb-4">
              <h3 class="text-gray-700 font-bold">Descrição</h3>
              <p class="text-gray-600">
                <?= nl2br(htmlspecialchars($project->description ?? 'Nenhuma descrição fornecida')) ?>
              </p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <h3 class="text-gray-700 font-bold">Data de Início</h3>
                <p class="text-gray-600">
                  <?= $project->start_date ? date('d/m/Y', strtotime($project->start_date)) : 'Não definida' ?>
                </p>
              </div>
              <div>
                <h3 class="text-gray-700 font-bold">Data de Término</h3>
                <p class="text-gray-600">
                  <?= $project->end_date ? date('d/m/Y', strtotime($project->end_date)) : 'Não definida' ?>
                </p>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                  <i class="fas fa-users text-blue-600 text-xs"></i>
                </div>
                <span class="text-sm text-gray-600 ml-2">
                  <?= $projectData['team_count'] ?? 0 ?> membros
                </span>
              </div>
              <a href="<?= route('projects.show', ['id' => $project->id]) ?>"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Ver detalhes <i class="fas fa-arrow-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
  // Adiciona classe para truncar descrições longas
  document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('.line-clamp-3')) {
      const style = document.createElement('style');
      style.textContent = `
        .line-clamp-3 {
          display: -webkit-box;
          -webkit-line-clamp: 3;
          -webkit-box-orient: vertical;
          overflow: hidden;
        }
      `;
      document.head.appendChild(style);
    }
  });
</script>