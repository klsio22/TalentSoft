<?php
/**
 * Página de erro 500 - Erro interno do servidor
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 500 - Erro Interno do Servidor | TalentSoft</title>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 text-center overflow-hidden">
            <div class="p-8">
                <h1 class="text-red-600 text-8xl font-bold mb-4">500</h1>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Erro Interno do Servidor</h2>

                <p class="text-lg text-gray-600 mb-8">
                    Oops! Algo deu errado em nosso servidor. Nossa equipe técnica já foi notificada e está trabalhando para resolver o problema.
                </p>

                <div class="flex flex-col md:flex-row justify-center gap-4 mb-8">
                    <div class="bg-gray-100 p-4 rounded-lg border border-gray-200 max-w-sm mx-auto">
                        <p class="text-gray-800 mb-2 font-medium">Dicas de solução:</p>
                        <ul class="text-left text-sm text-gray-700">
                            <li class="mb-1">• Tente recarregar a página</li>
                            <li class="mb-1">• Verifique sua conexão com a internet</li>
                            <li>• Volte em alguns instantes</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="/" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-home mr-2"></i> Voltar para a página inicial
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
