<?php
// painel/index.php
if (session_status() === PHP_SESSION_NONE) session_start();
$csrf_token = $_SESSION['csrf_token'] ?? '';
$username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel — SeederLinux Lite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-item.active { background-color: #eff6ff; color: #1d4ed8; font-weight: 600; }
        .sidebar-item.active svg { color: #1d4ed8; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-white shadow-sm flex flex-col min-h-screen fixed top-0 left-0 z-30 transition-transform">
        <!-- Logo -->
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </div>
                <span class="font-bold text-gray-900 text-sm">SeederLinux Lite</span>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2 mb-2">Geral</p>
            <a href="#" onclick="showSection('dashboard')" class="sidebar-item active flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="#" onclick="showSection('organizations')" class="sidebar-item flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Organizações (OMs)
            </a>

            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2 mb-2 mt-4">OMs Cadastradas</p>
            <div id="om-list-nav" class="space-y-1">
                <p class="text-xs text-gray-400 px-3 py-1">Carregando...</p>
            </div>
        </nav>

        <!-- User -->
        <div class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-700 text-xs font-bold"><?= strtoupper(substr($username, 0, 1)) ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($username) ?></p>
                    <p class="text-xs text-gray-500">Administrador</p>
                </div>
                <a href="/logout" class="text-gray-400 hover:text-red-500 transition-colors" title="Sair">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h1 id="page-title" class="text-lg font-semibold text-gray-900">Dashboard</h1>
                <p id="page-subtitle" class="text-xs text-gray-500">Visão geral do sistema</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                    Sistema Online
                </span>
            </div>
        </header>

        <!-- Dashboard Section -->
        <section id="section-dashboard" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm text-gray-500">Total de OMs</p>
                        <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <p id="stat-orgs" class="text-3xl font-bold text-gray-900">—</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm text-gray-500">Bundles Gerados</p>
                        <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p id="stat-bundles" class="text-3xl font-bold text-gray-900">—</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm text-gray-500">Scripts Core</p>
                        <div class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <p id="stat-scripts" class="text-3xl font-bold text-gray-900">4</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Organizações Recentes</h3>
                <div id="recent-orgs-table">
                    <p class="text-gray-400 text-sm">Carregando...</p>
                </div>
            </div>
        </section>

        <!-- Organizations Section -->
        <section id="section-organizations" class="p-6 hidden">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">Organizações Militares</h2>
                <button onclick="openNewOmModal()" class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nova OM
                </button>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sigla</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Domínio AD</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="orgs-table-body" class="divide-y divide-gray-50">
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- OM Detail Section (Variables + Bundle) -->
        <section id="section-om-detail" class="p-6 hidden">
            <div class="flex items-center gap-3 mb-5">
                <button onclick="showSection('organizations')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h2 id="om-detail-title" class="text-lg font-semibold text-gray-900">—</h2>
                    <p id="om-detail-subtitle" class="text-xs text-gray-500">—</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-5">
                <!-- Variables Panel -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-900 mb-4">Variáveis de Provisionamento</h3>
                    <p class="text-xs text-gray-500 mb-4">Preencha os valores para personalizar os scripts desta OM. Use o formato <code class="bg-gray-100 px-1 rounded">{{NOME_VARIAVEL}}</code> nos scripts.</p>
                    <form id="variables-form" class="space-y-3">
                        <input type="hidden" id="var-org-id" value="">
                        <div id="variables-grid" class="grid md:grid-cols-2 gap-3">
                            <p class="text-gray-400 text-sm col-span-2">Selecione uma OM para editar as variáveis.</p>
                        </div>
                        <div class="flex justify-end pt-3 border-t border-gray-100">
                            <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Salvar Variáveis
                            </button>
                        </div>
                    </form>
                    <div id="vars-message" class="hidden mt-3 text-sm rounded-lg px-3 py-2"></div>
                </div>

                <!-- Bundle Panel -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-900 mb-2">Gerar Bundle</h3>
                    <p class="text-xs text-gray-500 mb-4">Gera um script .sh com todos os scripts core e as variáveis desta OM substituídas.</p>
                    
                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Scripts Core incluídos:</p>
                        <ul class="space-y-1">
                            <li class="text-xs text-gray-500 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                                core_domain.sh
                            </li>
                            <li class="text-xs text-gray-500 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                                core_network.sh
                            </li>
                            <li class="text-xs text-gray-500 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></span>
                                core_inventory.sh
                            </li>
                            <li class="text-xs text-gray-500 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-purple-400 rounded-full"></span>
                                core_branding.sh
                            </li>
                        </ul>
                    </div>

                    <button onclick="generateBundle()" id="btn-generate"
                        class="w-full bg-green-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Gerar e Baixar Bundle
                    </button>

                    <div id="bundle-result" class="hidden mt-3">
                        <a id="bundle-download-link" href="#" download
                            class="block w-full text-center bg-blue-50 border border-blue-200 text-blue-700 text-sm font-medium py-2.5 rounded-lg hover:bg-blue-100 transition-colors">
                            Baixar Bundle Gerado
                        </a>
                    </div>
                    <div id="bundle-message" class="hidden mt-3 text-sm rounded-lg px-3 py-2"></div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal: Nova OM -->
    <div id="modal-new-om" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Nova Organização</h3>
                <button onclick="closeNewOmModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="form-new-om" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                    <input type="text" id="om-name" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: Comando Aéreo Regional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sigla <span class="text-red-500">*</span></label>
                    <input type="text" id="om-acronym" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: COMARA">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Domínio AD</label>
                    <input type="text" id="om-domain"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: comara.intraer">
                </div>
                <div id="om-form-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeNewOmModal()" class="flex-1 border border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                        Criar OM
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = '<?= htmlspecialchars($csrf_token) ?>';
        let currentOmId = null;

        // Variable definitions with descriptions
        const VARIABLE_DEFS = [
            { name: 'DOMINIO', desc: 'Domínio AD completo', placeholder: 'comara.intraer' },
            { name: 'DOMINIO_NETBIOS', desc: 'Nome NetBIOS do domínio', placeholder: 'COMARA' },
            { name: 'DC_IP', desc: 'IP do Controlador de Domínio', placeholder: '10.108.64.51' },
            { name: 'DNS_INTERNET', desc: 'DNS para internet (fallback)', placeholder: '10.108.64.27' },
            { name: 'BASE_URL', desc: 'URL base do repositório', placeholder: 'https://softwarelivre.comara.intraer' },
            { name: 'OCS_SERVER', desc: 'Servidor OCS Inventory', placeholder: 'http://ocs.comara.intraer/ocsinventory' },
            { name: 'OCS_TAG', desc: 'Tag OCS da organização', placeholder: 'GAPBE-COMARA' },
            { name: 'PRINT_SERVER', desc: 'Servidor de impressão', placeholder: '10.108.64.20' },
            { name: 'PROXY_HTTP', desc: 'Proxy HTTP corporativo', placeholder: '10.108.88.4' },
            { name: 'PROXY_PORTA', desc: 'Porta do proxy', placeholder: '8080' },
            { name: 'HOMEPAGE', desc: 'Página inicial do portal', placeholder: 'www.comara.intraer' },
            { name: 'PROXY_URL', desc: 'URL completa do proxy', placeholder: 'http://proxy.comara.intraer:8080' },
            { name: 'GRUPO_ADMIN_AD', desc: 'Grupo admin no AD para sudo', placeholder: 'Dominio Admins' },
            { name: 'GRUPO_ADMIN_LINUX', desc: 'Grupo local para sudo', placeholder: 'linux-admins' },
            { name: 'GRUPO_DASTI', desc: 'Grupo DASTI para sudo', placeholder: '_DASTI' },
            { name: 'WALLPAPER_URL', desc: 'URL do wallpaper da OM', placeholder: 'https://...' },
        ];

        // ---- Section management ----
        function showSection(name) {
            document.querySelectorAll('section[id^="section-"]').forEach(s => s.classList.add('hidden'));
            document.getElementById('section-' + name).classList.remove('hidden');
            document.querySelectorAll('.sidebar-item').forEach(a => a.classList.remove('active'));

            const titles = {
                dashboard: ['Dashboard', 'Visão geral do sistema'],
                organizations: ['Organizações (OMs)', 'Gerenciar organizações militares'],
                'om-detail': ['Detalhes da OM', 'Variáveis e geração de bundle']
            };
            document.getElementById('page-title').textContent = titles[name][0];
            document.getElementById('page-subtitle').textContent = titles[name][1];

            if (name === 'dashboard') {
                document.querySelector('[onclick="showSection(\'dashboard\')"]').classList.add('active');
                loadDashboard();
            } else if (name === 'organizations') {
                document.querySelector('[onclick="showSection(\'organizations\')"]').classList.add('active');
                loadOrganizations();
            }
        }

        // ---- Load Dashboard ----
        async function loadDashboard() {
            const orgs = await apiFetch('/api/organizations.php');
            document.getElementById('stat-orgs').textContent = orgs.length;
            document.getElementById('stat-bundles').textContent = '—';

            const tbody = document.getElementById('recent-orgs-table');
            if (orgs.length === 0) {
                tbody.innerHTML = '<p class="text-gray-400 text-sm">Nenhuma organização cadastrada.</p>';
                return;
            }
            tbody.innerHTML = `<table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="text-left px-4 py-2 text-xs text-gray-500">Nome</th>
                    <th class="text-left px-4 py-2 text-xs text-gray-500">Sigla</th>
                    <th class="text-left px-4 py-2 text-xs text-gray-500">Domínio</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                ${orgs.slice(0, 5).map(o => `<tr>
                    <td class="px-4 py-2 text-gray-800">${escHtml(o.name)}</td>
                    <td class="px-4 py-2"><span class="bg-blue-50 text-blue-700 text-xs font-mono px-2 py-0.5 rounded">${escHtml(o.acronym)}</span></td>
                    <td class="px-4 py-2 text-gray-500">${escHtml(o.domain || '—')}</td>
                </tr>`).join('')}
                </tbody></table>`;
        }

        // ---- Load Organizations ----
        async function loadOrganizations() {
            const orgs = await apiFetch('/api/organizations.php');
            const tbody = document.getElementById('orgs-table-body');
            if (orgs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Nenhuma organização cadastrada. Clique em "Nova OM" para começar.</td></tr>';
                return;
            }
            tbody.innerHTML = orgs.map(o => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900">${escHtml(o.name)}</td>
                    <td class="px-5 py-3"><span class="bg-blue-50 text-blue-700 text-xs font-mono font-semibold px-2 py-1 rounded">${escHtml(o.acronym)}</span></td>
                    <td class="px-5 py-3 text-gray-500 text-sm">${escHtml(o.domain || '—')}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1 ${o.active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'} text-xs font-medium px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 ${o.active ? 'bg-green-500' : 'bg-gray-400'} rounded-full"></span>
                            ${o.active ? 'Ativa' : 'Inativa'}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <button onclick="openOmDetail(${o.id}, '${escHtml(o.name)}', '${escHtml(o.acronym)}')"
                            class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">
                            Gerenciar
                        </button>
                    </td>
                </tr>
            `).join('');
            loadOmNavList(orgs);
        }

        function loadOmNavList(orgs) {
            const nav = document.getElementById('om-list-nav');
            if (orgs.length === 0) {
                nav.innerHTML = '<p class="text-xs text-gray-400 px-3 py-1">Nenhuma OM.</p>';
                return;
            }
            nav.innerHTML = orgs.map(o => `
                <a href="#" onclick="openOmDetail(${o.id}, '${escHtml(o.name)}', '${escHtml(o.acronym)}')"
                    class="sidebar-item flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                    <span class="w-6 h-6 bg-blue-100 text-blue-700 rounded text-xs font-bold flex items-center justify-center">${escHtml(o.acronym.substring(0,2))}</span>
                    ${escHtml(o.acronym)}
                </a>
            `).join('');
        }

        // ---- OM Detail ----
        async function openOmDetail(id, name, acronym) {
            currentOmId = id;
            document.getElementById('om-detail-title').textContent = name;
            document.getElementById('om-detail-subtitle').textContent = 'Sigla: ' + acronym;
            document.getElementById('var-org-id').value = id;
            showSection('om-detail');
            document.getElementById('bundle-result').classList.add('hidden');
            document.getElementById('bundle-message').classList.add('hidden');
            await loadVariables(id);
        }

        async function loadVariables(orgId) {
            const vars = await apiFetch('/api/variables.php?org_id=' + orgId);
            const varMap = {};
            vars.forEach(v => varMap[v.name] = v.value || '');

            const grid = document.getElementById('variables-grid');
            grid.innerHTML = VARIABLE_DEFS.map(def => `
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        <code class="bg-gray-100 px-1 rounded text-blue-700">{{${def.name}}}</code>
                    </label>
                    <input type="text" name="${def.name}" value="${escHtml(varMap[def.name] || '')}"
                        placeholder="${escHtml(def.placeholder)}"
                        title="${escHtml(def.desc)}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-0.5">${escHtml(def.desc)}</p>
                </div>
            `).join('');
        }

        // ---- Save Variables ----
        document.getElementById('variables-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const orgId = document.getElementById('var-org-id').value;
            const inputs = document.querySelectorAll('#variables-grid input[name]');
            const msg = document.getElementById('vars-message');
            let errors = 0;

            for (const input of inputs) {
                if (!input.value.trim()) continue;
                const res = await apiFetch('/api/variables.php', 'POST', {
                    csrf_token: CSRF_TOKEN,
                    organization_id: parseInt(orgId),
                    name: input.name,
                    value: input.value.trim(),
                    type: 'string'
                });
                if (res.error) errors++;
            }

            msg.classList.remove('hidden');
            if (errors === 0) {
                msg.className = 'mt-3 text-sm rounded-lg px-3 py-2 bg-green-50 text-green-700 border border-green-200';
                msg.textContent = 'Variáveis salvas com sucesso!';
            } else {
                msg.className = 'mt-3 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
                msg.textContent = 'Erro ao salvar algumas variáveis.';
            }
            setTimeout(() => msg.classList.add('hidden'), 4000);
        });

        // ---- Generate Bundle ----
        async function generateBundle() {
            const btn = document.getElementById('btn-generate');
            const msg = document.getElementById('bundle-message');
            const result = document.getElementById('bundle-result');
            btn.disabled = true;
            btn.textContent = 'Gerando...';

            const res = await apiFetch('/api/generate-bundle.php', 'POST', {
                csrf_token: CSRF_TOKEN,
                organization_id: parseInt(currentOmId),
                scripts: []
            });

            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Gerar e Baixar Bundle`;

            if (res.download_url) {
                result.classList.remove('hidden');
                document.getElementById('bundle-download-link').href = res.download_url;
                msg.classList.remove('hidden');
                msg.className = 'mt-3 text-sm rounded-lg px-3 py-2 bg-green-50 text-green-700 border border-green-200';
                msg.textContent = 'Bundle gerado com sucesso! Clique para baixar.';
            } else {
                msg.classList.remove('hidden');
                msg.className = 'mt-3 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
                msg.textContent = res.error || 'Erro ao gerar bundle.';
            }
        }

        // ---- Modal: Nova OM ----
        function openNewOmModal() {
            document.getElementById('modal-new-om').classList.remove('hidden');
        }
        function closeNewOmModal() {
            document.getElementById('modal-new-om').classList.add('hidden');
            document.getElementById('form-new-om').reset();
            document.getElementById('om-form-error').classList.add('hidden');
        }

        document.getElementById('form-new-om').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errDiv = document.getElementById('om-form-error');
            const res = await apiFetch('/api/organizations.php', 'POST', {
                csrf_token: CSRF_TOKEN,
                name: document.getElementById('om-name').value.trim(),
                acronym: document.getElementById('om-acronym').value.trim().toUpperCase(),
                domain: document.getElementById('om-domain').value.trim()
            });

            if (res.error) {
                errDiv.textContent = res.error;
                errDiv.classList.remove('hidden');
            } else {
                closeNewOmModal();
                loadOrganizations();
                showSection('organizations');
            }
        });

        // ---- Utilities ----
        async function apiFetch(url, method = 'GET', body = null) {
            const opts = { method, headers: { 'Content-Type': 'application/json' } };
            if (body) opts.body = JSON.stringify(body);
            try {
                const res = await fetch(url, opts);
                return await res.json();
            } catch (e) {
                return { error: 'Erro de comunicação com o servidor.' };
            }
        }

        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // ---- Init ----
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboard();
            fetch('/api/organizations.php').then(r => r.json()).then(orgs => loadOmNavList(orgs));
        });
    </script>
</body>
</html>
