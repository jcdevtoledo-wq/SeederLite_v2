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
        .feature-card { transition: all 0.3s ease; }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .stat-item { transition: all 0.3s ease; }
        .stat-item:hover { transform: scale(1.05); }
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-brand-600 to-brand-700 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </div>
                <span class="font-bold text-lg text-brand-900">SeederLinux Lite</span>
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#funcionalidades" class="hover:text-brand-600 transition-colors duration-200">Funcionalidades</a>
                <a href="#scripts" class="hover:text-brand-600 transition-colors duration-200">Scripts Core</a>
                <a href="#downloads" class="hover:text-brand-600 transition-colors duration-200">Downloads</a>
                <a href="/login" class="bg-brand-600 text-white px-5 py-2.5 rounded-lg hover:bg-brand-700 transition-colors duration-200 font-semibold shadow-md">
                    Painel Admin
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-24 px-4 sm:py-32">
        <div class="max-w-5xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-4 py-2 text-sm mb-8 hover:bg-white/15 transition-colors">
                <span class="w-2.5 h-2.5 bg-green-400 rounded-full pulse-dot"></span>
                <span>Sistema de Provisionamento Linux</span>
            </div>
            <h1 class="text-5xl sm:text-6xl font-black mb-6 leading-tight">
                Provisionamento Linux<br><span class="bg-gradient-to-r from-blue-200 to-cyan-200 bg-clip-text text-transparent">Simplificado e Centralizado</span>
            </h1>
            <p class="text-lg sm:text-xl text-blue-100 max-w-3xl mx-auto mb-10 leading-relaxed">
                Gerenciamento centralizado de scripts e variáveis para múltiplas Organizações Militares. 
                Deploy offline-first, seguro, auditável e extensível.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="/login" class="bg-white text-brand-700 font-bold px-8 py-4 rounded-xl hover:bg-blue-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Acessar Painel Admin
                </a>
                <a href="#downloads" class="border-2 border-white/40 text-white font-bold px-8 py-4 rounded-xl hover:bg-white/10 transition-all duration-200 backdrop-blur-sm hover:border-white/60">
                    ↓ Baixar Agente
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="stat-item text-center">
                    <div class="text-4xl sm:text-5xl font-black text-brand-600 mb-2">15+</div>
                    <div class="text-sm sm:text-base text-gray-600 font-medium">Variáveis Configuráveis</div>
                    <p class="text-xs text-gray-500 mt-1">Por organização</p>
                </div>
                <div class="stat-item text-center">
                    <div class="text-4xl sm:text-5xl font-black text-green-600 mb-2">4</div>
                    <div class="text-sm sm:text-base text-gray-600 font-medium">Scripts Core</div>
                    <p class="text-xs text-gray-500 mt-1">Pré-configurados</p>
                </div>
                <div class="stat-item text-center">
                    <div class="text-4xl sm:text-5xl font-black text-purple-600 mb-2">100%</div>
                    <div class="text-sm sm:text-base text-gray-600 font-medium">Offline-First</div>
                    <p class="text-xs text-gray-500 mt-1">Sem dependências</p>
                </div>
                <div class="stat-item text-center">
                    <div class="text-4xl sm:text-5xl font-black text-orange-600 mb-2">PHP 8+</div>
                    <div class="text-sm sm:text-base text-gray-600 font-medium">Backend Moderno</div>
                    <p class="text-xs text-gray-500 mt-1">PostgreSQL 16</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="funcionalidades" class="py-20 px-4 sm:py-28">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">Funcionalidades Poderosas</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Tudo que você precisa para provisionar estações Linux em escala, com segurança e auditoria</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Gerenciamento Multi-OM</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Cada organização militar possui seu próprio conjunto de variáveis, branding e scripts personalizados. Isolamento total de dados.</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Substituição Dinâmica</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Placeholders como <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">{{DOMINIO}}</code> são substituídos em tempo real pelos valores específicos da OM.</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Provisionamento Offline</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Bundles gerados são scripts shell autônomos que funcionam sem conexão permanente com a rede central.</p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Integração Active Directory</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Configuração automatizada de SSSD/Winbind para ingresso em domínios AD corporativos com grupos de sudoers.</p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Inventário e Monitoramento</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Instalação e configuração automática de agentes OCS Inventory para coleta centralizada de informações de hardware.</p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-3 text-lg">Customização Visual</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Aplicação de wallpapers, temas XFCE e logotipos específicos para cada organização militar.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts Core Section -->
    <section id="scripts" class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-20 px-4 sm:py-28">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-black mb-4">Scripts Core Inclusos</h2>
                <p class="text-lg text-gray-300 max-w-2xl mx-auto">Módulos pré-configurados e testados incluídos em todo bundle gerado</p>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700 hover:border-blue-500/50 transition-colors">
                    <div class="flex items-start gap-4 mb-4">
                        <span class="bg-blue-500/20 text-blue-300 text-xs font-mono px-3 py-1.5 rounded-lg font-bold">core_domain.sh</span>
                        <span class="bg-green-500/20 text-green-300 text-xs font-mono px-3 py-1.5 rounded-lg">~250 linhas</span>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">Ingresso no Active Directory via SSSD/Winbind. Configura hosts, DNS, grupos de sudoers e permissões de acesso.</p>
                </div>

                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700 hover:border-green-500/50 transition-colors">
                    <div class="flex items-start gap-4 mb-4">
                        <span class="bg-green-500/20 text-green-300 text-xs font-mono px-3 py-1.5 rounded-lg font-bold">core_network.sh</span>
                        <span class="bg-green-500/20 text-green-300 text-xs font-mono px-3 py-1.5 rounded-lg">~180 linhas</span>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">Proxy HTTP corporativo, página inicial do navegador e configuração de servidor de impressão.</p>
                </div>

                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700 hover:border-yellow-500/50 transition-colors">
                    <div class="flex items-start gap-4 mb-4">
                        <span class="bg-yellow-500/20 text-yellow-300 text-xs font-mono px-3 py-1.5 rounded-lg font-bold">core_inventory.sh</span>
                        <span class="bg-green-500/20 text-green-300 text-xs font-mono px-3 py-1.5 rounded-lg">~120 linhas</span>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">Configuração do agente OCS Inventory com servidor central e TAG da organização para rastreamento.</p>
                </div>

                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-700 hover:border-purple-500/50 transition-colors">
                    <div class="flex items-start gap-4 mb-4">
                        <span class="bg-purple-500/20 text-purple-300 text-xs font-mono px-3 py-1.5 rounded-lg font-bold">core_branding.sh</span>
                        <span class="bg-green-500/20 text-green-300 text-xs font-mono px-3 py-1.5 rounded-lg">~95 linhas</span>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">Identidade visual: wallpaper, tema XFCE e logotipo específico da organização militar.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Downloads Section -->
    <section id="downloads" class="py-20 px-4 sm:py-28 bg-white">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">Área de Downloads</h2>
                <p class="text-lg text-gray-600">Componentes necessários para as estações de trabalho</p>
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Agent Download -->
                <div class="border-2 border-gray-200 rounded-2xl p-8 hover:border-brand-400 hover:shadow-lg transition-all duration-300 bg-gradient-to-br from-blue-50 to-white">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-14 h-14 bg-brand-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">Agente SeederLinux</h3>
                            <p class="text-gray-500 text-sm">v1.1 — Python 3.6+</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-6 leading-relaxed">Script Python para check-in automático, auto-discovery de OM e execução de bundles nas estações. Inclui auto-update.</p>
                    <a href="/agent.py" download class="inline-flex items-center gap-2 bg-brand-600 text-white font-bold py-3 px-5 rounded-lg hover:bg-brand-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Baixar agent.py
                    </a>
                </div>

                <!-- Documentation Download -->
                <div class="border-2 border-gray-200 rounded-2xl p-8 hover:border-green-400 hover:shadow-lg transition-all duration-300 bg-gradient-to-br from-green-50 to-white">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">Documentação Completa</h3>
                            <p class="text-gray-500 text-sm">Markdown + Diagrama</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-6 leading-relaxed">Guia de instalação, configuração, uso do painel administrativo e troubleshooting. Inclui exemplos práticos.</p>
                    <a href="/README.md" download class="inline-flex items-center gap-2 bg-green-600 text-white font-bold py-3 px-5 rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Baixar README.md
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final Section -->
    <section class="bg-gradient-to-r from-brand-600 to-brand-700 text-white py-16 px-4 sm:py-20">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl font-black mb-4">Pronto para Começar?</h2>
            <p class="text-lg text-blue-100 mb-8 max-w-2xl mx-auto">Acesse o painel administrativo agora e configure suas primeiras Organizações Militares.</p>
            <a href="/login" class="inline-flex items-center gap-2 bg-white text-brand-700 font-bold py-4 px-8 rounded-xl hover:bg-blue-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Acessar Painel Admin
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 px-4 border-t border-gray-800">
        <div class="max-w-7xl mx-auto text-center">
            <p class="text-sm">SeederLinux Lite © 2026 — Sistema de Provisionamento Centralizado para Organizações Militares</p>
            <p class="text-xs text-gray-500 mt-2">Desenvolvido com segurança, auditoria e escalabilidade em mente.</p>
        </div>
    </footer>

</body>
</html>
