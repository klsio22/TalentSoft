<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 text-center overflow-hidden">
        <div class="p-8">
            <h1 class="text-red-600 text-8xl font-bold mb-4">404</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Página não encontrada</h2>

            <p class="text-lg text-gray-600 mb-8">
                Oops! Parece que a página que você está procurando não existe ou foi movida.
            </p>

            <div class="flex flex-col md:flex-row justify-center gap-4 mb-8">
                <div class="bg-gray-100 p-4 rounded-lg border border-gray-200 max-w-sm mx-auto">
                    <p class="text-gray-800 mb-2 font-medium">URLs comuns:</p>
                    <ul class="text-left">
                        <li class="mb-2">
                            <a href="<?= route('employees.index') ?>" class="text-blue-600 hover:underline">
                                /employees - Listagem de funcionários
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= route('employees.create') ?>" class="text-blue-600 hover:underline">
                                /employees/create - Cadastrar funcionário
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-center items-center gap-4">
                <a href="<?= route('root') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center">
                    <i class="fas fa-home mr-2"></i> Ir para o início
                </a>
                <a href="javascript:history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                </a>
                <a href="<?= route('auth.login') ?>" class="border border-gray-300 hover:bg-gray-100 text-gray-800 px-6 py-2 rounded-lg flex items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Ir para o Login
                </a>
            </div>
        </div>
    </div>
</div>
