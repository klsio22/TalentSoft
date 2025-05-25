<!-- filepath: /media/klsio27/outher-files/documentos/utfpr/TalentSoft/app/views/profile/show.php -->
<div class="max-w-4xl mx-auto">
  <div class="bg-white shadow-xl rounded-lg">
    <!-- Header do perfil -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 rounded-t-lg">
      <div class="flex items-center space-x-4">
        <div class="bg-white p-3 rounded-full">
          <i class="fas fa-user text-blue-600 text-2xl"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-white">Meu Perfil</h1>
          <p class="text-blue-100">Informações pessoais e profissionais</p>
        </div>
      </div>
    </div>

    <!-- Conteúdo do perfil -->
    <div class="p-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Informações Pessoais -->
        <div class="space-y-6">
          <div class="border-b pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-user mr-2 text-blue-600"></i>
              Informações Pessoais
            </h2>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900"><?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900"><?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900"><?= htmlspecialchars($user->cpf, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900">
                  <?php if (isset($user->birth_date) && $user->birth_date): ?>
                    <?= date('d/m/Y', strtotime($user->birth_date)) ?>
                  <?php else: ?>
                    Não informado
                  <?php endif; ?>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900">
                  <?php if ($user->address): ?>
                    <?= htmlspecialchars($user->address) ?>
                    <?php if ($user->city || $user->state): ?>
                      <br><?= htmlspecialchars($user->city ?? '') ?> - <?= htmlspecialchars($user->state ?? '') ?>
                    <?php endif; ?>
                    <?php if ($user->zipcode): ?>
                      <br>CEP: <?= htmlspecialchars($user->zipcode) ?>
                    <?php endif; ?>
                  <?php else: ?>
                    Não informado
                  <?php endif; ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações Profissionais -->
        <div class="space-y-6">
          <div class="border-b pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-briefcase mr-2 text-blue-600"></i>
              Informações Profissionais
            </h2>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900">
                  <?php if ($user->role()): ?>
                    <?= htmlspecialchars($user->role()->description) ?>
                  <?php else: ?>
                    Não informado
                  <?php endif; ?>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $user->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                  <?= $user->status === 'Active' ? 'Ativo' : 'Inativo' ?>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Salário</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900">
                  <?php if (isset($user->salary) && $user->salary > 0): ?>
                    R$ <?= number_format($user->salary, 2, ',', '.') ?>
                  <?php else: ?>
                    Não informado
                  <?php endif; ?>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nível de Acesso</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <?php
                $roleColor = 'bg-green-100 text-green-800';
                $roleIcon = 'user';
                $roleLabel = 'Funcionário';

                if ($user->role()) {
                  $roleName = strtolower($user->role()->name);
                  if ($roleName === 'admin') {
                    $roleColor = 'bg-red-100 text-red-800';
                    $roleIcon = 'crown';
                    $roleLabel = 'Administrador';
                  } elseif ($roleName === 'hr') {
                    $roleColor = 'bg-yellow-100 text-yellow-800';
                    $roleIcon = 'user-tie';
                    $roleLabel = 'Recursos Humanos';
                  }
                }
                ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $roleColor ?>">
                  <i class="fas fa-<?= $roleIcon ?> mr-1"></i>
                  <?= $roleLabel ?>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Data de Contratação</label>
              <div class="bg-gray-50 px-3 py-2 rounded-md border">
                <span class="text-gray-900">
                  <?php if (isset($user->hire_date)): ?>
                    <?= date('d/m/Y', strtotime($user->hire_date)) ?>
                  <?php else: ?>
                    Não informado
                  <?php endif; ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Observações (se existirem) -->
      <?php if (isset($user->notes) && !empty($user->notes)): ?>
      <div class="mt-8 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-sticky-note mr-2 text-blue-600"></i>
          Observações
        </h3>
        <div class="bg-gray-50 px-4 py-3 rounded-md border">
          <p class="text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($user->notes) ?></p>
        </div>
      </div>
      <?php endif; ?>

      <!-- Rodapé com informações adicionais -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
          <div class="flex items-center">
            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
            <span>Cadastrado em:
              <?php if (isset($user->created_at) && $user->created_at): ?>
                <?= date('d/m/Y H:i', strtotime($user->created_at)) ?>
              <?php else: ?>
                Não informado
              <?php endif; ?>
            </span>
          </div>
          <div class="flex items-center">
            <i class="fas fa-briefcase mr-2 text-blue-600"></i>
            <span>Data de contratação:
              <?php if (isset($user->hire_date) && $user->hire_date): ?>
                <?= date('d/m/Y', strtotime($user->hire_date)) ?>
              <?php else: ?>
                Não informado
              <?php endif; ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Nota informativa para usuários comuns -->
      <?php if ($user->role() && strtolower($user->role()->name) === 'user'): ?>
      <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-400"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">
              Informação
            </h3>
            <div class="mt-2 text-sm text-blue-700">
              <p>Para alterar suas informações pessoais ou profissionais, entre em contato com o departamento de Recursos Humanos.</p>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
