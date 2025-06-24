<?php

/**
 * Adaptador para o script populate_projects.php
 * Este arquivo garante que as variáveis e funções necessárias estejam disponíveis
 * para o script populate_projects.php quando ele for incluído na nova estrutura
 */

// Função para executar o script de população de projetos
function populateProjectsAndNotifications()
{
  echo "\n=== Populando projetos e notificações ===\n";

  // Incluir o script de população de projetos
  require_once dirname(__DIR__) . '/populate_projects.php';

  echo "\n=== Projetos e notificações populados com sucesso ===\n";
}
