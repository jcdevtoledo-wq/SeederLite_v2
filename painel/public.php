<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeederLinux Lite — Provisionamento Centralizado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-brand-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </div>
                <span class="font-bold text-lg text-brand-900">SeederLinux Lite</span>
            </div>
            <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                <a href="#funcionalidades" class="hover:text-brand-600 transition-colors">Funcionalidades</a>
                <a href="#scripts" class="hover:text-brand-600 transition-colors">Scripts Core</a>
                <a href="#downloads" class="hover:text-brand-600 transition-colors">Downloads</a>
                <a href="/login" class="bg-brand-600 text-white px-4 py-2 rounded-lg hover:bg-brand-700 transition-colors">
                    Login Gerente
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-gradient text-white py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-1.5 text-sm mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                Sistema Operacional
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight">
                Provisionamento Linux<br>Simplificado
            </h1>
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto mb-8">
                Gerenciamento centralizado de scripts e variáveis para múltiplas Organizações Militares. 
                Offline-first, seguro e extensível.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/login" class="bg-white text-brand-700 font-semibold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors shadow-lg">
                    Acessar Painel
                </a>
                <a href="#downloads" class="border border-white/40 text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/10 transition-colors">
                    Baixar Agente
                </a>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-3xl font-bold text-brand-600">15+</div>
                <div class="text-sm text-gray-500 mt-1">Variáveis Configuráveis</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-brand-600">4</div>
                <div class="text-sm text-gray-500 mt-1">Scripts Core</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-brand-600">100%</div>
                <div class="text-sm text-gray-500 mt-1">Offline-First</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-brand-600">PHP 8+</div>
                <div class="text-sm text-gray-500 mt-1">Backend Moderno</div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="funcionalidades" class="py-16 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">Funcionalidades</h2>
                <p class="text-gray-500 mt-2">Tudo que você precisa para provisionar estações Linux em escala</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Gerenciamento Multi-OM</h3>
                    <p class="text-gray-500 text-sm">Cada organização possui seu próprio conjunto de variáveis, branding e scripts personalizados.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Substituição Dinâmica</h3>
                    <p class="text-gray-500 text-sm">Placeholders como <code class="bg-gray-100 px-1 rounded text-xs">{{DOMINIO}}</code> são substituídos em tempo real pelos valores da OM.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Provisionamento Offline</h3>
                    <p class="text-gray-500 text-sm">Bundles gerados são scripts shell autônomos que funcionam sem conexão permanente com a rede.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Integração AD</h3>
                    <p class="text-gray-500 text-sm">Configuração automatizada de SSSD/Winbind para ingresso em domínios Active Directory.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Inventário e Monitoramento</h3>
                    <p class="text-gray-500 text-sm">Instalação e configuração automática de agentes OCS Inventory para coleta de informações.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Customização Visual</h3>
                    <p class="text-gray-500 text-sm">Aplicação de wallpapers, temas e logotipos específicos para cada organização.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts Core -->
    <section id="scripts" class="bg-gray-900 text-white py-16 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold">Scripts Core</h2>
                <p class="text-gray-400 mt-2">Módulos pré-configurados incluídos em todo bundle</p>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="bg-blue-500/20 text-blue-400 text-xs font-mono px-2 py-1 rounded">core_domain.sh</span>
                    </div>
                    <p class="text-gray-300 text-sm">Ingresso no Active Directory via SSSD/Winbind. Configura hosts, DNS e grupos de sudoers.</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="bg-green-500/20 text-green-400 text-xs font-mono px-2 py-1 rounded">core_network.sh</span>
                    </div>
                    <p class="text-gray-300 text-sm">Proxy HTTP corporativo, página inicial do navegador e servidor de impressão.</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="bg-yellow-500/20 text-yellow-400 text-xs font-mono px-2 py-1 rounded">core_inventory.sh</span>
                    </div>
                    <p class="text-gray-300 text-sm">Configuração do agente OCS Inventory com servidor e TAG da organização.</p>
                </div>
                <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="bg-purple-500/20 text-purple-400 text-xs font-mono px-2 py-1 rounded">core_branding.sh</span>
                    </div>
                    <p class="text-gray-300 text-sm">Identidade visual: wallpaper, tema XFCE e logotipo específico da organização.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Downloads -->
    <section id="downloads" class="py-16 px-4 bg-white">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">Área de Downloads</h2>
                <p class="text-gray-500 mt-2">Componentes necessários para as estações de trabalho</p>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-brand-300 hover:shadow-md transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Agente SeederLinux</h3>
                            <p class="text-gray-500 text-sm mt-1">Script Python para check-in e execução de bundles nas estações.</p>
                            <a href="/agent.py" download class="inline-flex items-center gap-2 mt-3 text-brand-600 hover:text-brand-700 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Baixar agent.py
                            </a>
                        </div>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-brand-300 hover:shadow-md transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Manual de Operação</h3>
                            <p class="text-gray-500 text-sm mt-1">Guia rápido para administradores e técnicos de campo.</p>
                            <a href="/DOCUMENTACAO.md" download class="inline-flex items-center gap-2 mt-3 text-green-600 hover:text-green-700 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Baixar Manual (.md)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 px-4">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-brand-600 rounded-md flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </div>
                <span class="font-semibold text-white">SeederLinux Lite</span>
            </div>
            <p class="text-sm">Desenvolvido para agilizar o provisionamento de estações Linux em Organizações Militares.</p>
        </div>
    </footer>

</body>
</html>
