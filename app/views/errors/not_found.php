<!-- Header Section -->
<div class="glass-effect rounded-2xl shadow-xl p-8 mb-8">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl mb-4 shadow-lg">
            <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
        </div>
        <h1 class="text-6xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent mb-2">
            404
        </h1>
        <p class="text-gray-600">A página que você procura não foi encontrada</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4">
    <div class="glass-effect rounded-2xl shadow-xl p-8 text-center">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Página não encontrada</h2>

        <p class="text-lg text-gray-600 mb-8">
            Oops! Parece que a página que você está procurando não existe ou foi movida.
        </p>

        <div class="glass-effect rounded-xl p-6 mb-8 bg-gradient-to-r from-blue-50 to-indigo-100 border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center justify-center">
                <i class="fas fa-sitemap mr-2"></i>Páginas disponíveis
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="<?= route('employees.index') ?>"
                   class="bg-white bg-opacity-60 p-4 rounded-lg border border-blue-200 hover:bg-opacity-80 transition-all duration-200 text-left">
                    <div class="flex items-center">
                        <i class="fas fa-users text-blue-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-blue-900">Funcionários</p>
                            <p class="text-sm text-blue-700">Gerenciar funcionários</p>
                        </div>
                    </div>
                </a>
                <a href="<?= route('employees.create') ?>"
                   class="bg-white bg-opacity-60 p-4 rounded-lg border border-blue-200 hover:bg-opacity-80 transition-all duration-200 text-left">
                    <div class="flex items-center">
                        <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-blue-900">Novo Funcionário</p>
                            <p class="text-sm text-blue-700">Cadastrar funcionário</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-center items-center gap-4">
            <a href="<?= route('root') ?>"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="fas fa-home mr-2"></i> Ir para o início
            </a>
            <a href="javascript:history.back()"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <a href="<?= route('auth.login') ?>"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="fas fa-sign-in-alt mr-2"></i> Ir para o Login
            </a>
        </div>
    </div>
</div>
