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
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-sm flex flex-col min-h-screen fixed top-0 left-0 z-30">
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

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2 mb-2">Menu</p>
            <button onclick="showSection('dashboard')" class="nav-item active w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </button>
            <button onclick="showSection('organizations')" class="nav-item w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Organizações
            </button>
            <button onclick="showSection('logs')" class="nav-item w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Logs de Atividade
            </button>
            <button onclick="showSection('settings')" class="nav-item w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configurações
            </button>
        </nav>

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
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h1 id="page-title" class="text-lg font-semibold text-gray-900">Dashboard</h1>
                <p id="page-subtitle" class="text-xs text-gray-500">Visão geral do sistema</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                    Sistema Online
                </span>
            </div>
        </header>

        <!-- Dashboard Section -->
        <section id="section-dashboard" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 mb-2">Total de OMs</p>
                    <p id="stat-orgs" class="text-3xl font-bold text-gray-900">—</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 mb-2">Bundles Gerados</p>
                    <p id="stat-bundles" class="text-3xl font-bold text-gray-900">—</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 mb-2">Scripts Core</p>
                    <p id="stat-scripts" class="text-3xl font-bold text-gray-900">4</p>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 mb-2">Atividades Recentes</p>
                    <p id="stat-activities" class="text-3xl font-bold text-gray-900">—</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Atividades Recentes</h3>
                <div id="recent-activities-table" class="text-gray-400 text-sm">Carregando...</div>
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
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nome</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sigla</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Domínio</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="orgs-table-body" class="divide-y divide-gray-50">
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Logs Section -->
        <section id="section-logs" class="p-6 hidden">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">Logs de Atividade</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Data/Hora</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Usuário</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Ação</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Detalhes</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody id="logs-table-body" class="divide-y divide-gray-50">
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="section-settings" class="p-6 hidden">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">Configurações do Sistema</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form id="settings-form" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <div id="settings-grid" class="space-y-4">
                        <p class="text-gray-400 text-sm">Carregando...</p>
                    </div>
                    <div class="flex justify-end pt-3 border-t border-gray-100">
                        <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
                <div id="settings-message" class="hidden mt-3 text-sm rounded-lg px-3 py-2"></div>
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
                    <input type="text" id="om-name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Comando Aéreo Regional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sigla <span class="text-red-500">*</span></label>
                    <input type="text" id="om-acronym" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: COMARA">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Domínio AD</label>
                    <input type="text" id="om-domain" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: comara.intraer">
                </div>
                <div id="om-form-error" class="hidden text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeNewOmModal()" class="flex-1 border border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-lg hover:bg-gray-50 transition-colors">Cancelar</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-blue-700 transition-colors">Criar OM</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = '<?= htmlspecialchars($csrf_token) ?>';

        function showSection(name) {
            document.querySelectorAll('section[id^="section-"]').forEach(s => s.classList.add('hidden'));
            document.getElementById('section-' + name).classList.remove('hidden');
            document.querySelectorAll('.nav-item').forEach(a => a.classList.remove('active'));
            event.target.classList.add('active');

            const titles = {
                dashboard: ['Dashboard', 'Visão geral do sistema'],
                organizations: ['Organizações (OMs)', 'Gerenciar organizações militares'],
                logs: ['Logs de Atividade', 'Histórico de ações do sistema'],
                settings: ['Configurações', 'Parâmetros do sistema']
            };
            document.getElementById('page-title').textContent = titles[name][0];
            document.getElementById('page-subtitle').textContent = titles[name][1];

            if (name === 'dashboard') loadDashboard();
            else if (name === 'organizations') loadOrganizations();
            else if (name === 'logs') loadLogs();
            else if (name === 'settings') loadSettings();
        }

        async function loadDashboard() {
            const orgs = await apiFetch('/api/organizations.php');
            document.getElementById('stat-orgs').textContent = orgs.length;
            
            const logs = await apiFetch('/api/activity_log.php?limit=10');
            document.getElementById('stat-activities').textContent = logs.length;

            const tbody = document.getElementById('recent-activities-table');
            if (logs.length === 0) {
                tbody.innerHTML = '<p class="text-gray-400 text-sm">Nenhuma atividade registrada.</p>';
                return;
            }
            tbody.innerHTML = `<table class="w-full text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="text-left px-4 py-2 text-xs text-gray-500">Data/Hora</th>
                    <th class="text-left px-4 py-2 text-xs text-gray-500">Usuário</th>
                    <th class="text-left px-4 py-2 text-xs text-gray-500">Ação</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                ${logs.slice(0, 5).map(l => `<tr>
                    <td class="px-4 py-2 text-gray-500 text-xs">${new Date(l.timestamp).toLocaleString('pt-BR')}</td>
                    <td class="px-4 py-2 font-medium text-gray-800">${escHtml(l.username || 'Sistema')}</td>
                    <td class="px-4 py-2 text-gray-700">${escHtml(l.action)}</td>
                </tr>`).join('')}
                </tbody></table>`;
        }

        async function loadOrganizations() {
            const orgs = await apiFetch('/api/organizations.php');
            const tbody = document.getElementById('orgs-table-body');
            if (orgs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Nenhuma organização cadastrada.</td></tr>';
                return;
            }
            tbody.innerHTML = orgs.map(o => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900">${escHtml(o.name)}</td>
                    <td class="px-5 py-3"><span class="bg-blue-50 text-blue-700 text-xs font-mono font-semibold px-2 py-1 rounded">${escHtml(o.acronym)}</span></td>
                    <td class="px-5 py-3 text-gray-500 text-sm">${escHtml(o.domain || '—')}</td>
                    <td class="px-5 py-3 text-right">
                        <button onclick="alert('Funcionalidade de edição em desenvolvimento')" class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">Gerenciar</button>
                    </td>
                </tr>
            `).join('');
        }

        async function loadLogs() {
            const logs = await apiFetch('/api/activity_log.php?limit=100');
            const tbody = document.getElementById('logs-table-body');
            if (logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Nenhum log registrado.</td></tr>';
                return;
            }
            tbody.innerHTML = logs.map(l => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 text-xs text-gray-500">${new Date(l.timestamp).toLocaleString('pt-BR')}</td>
                    <td class="px-5 py-3 font-medium text-gray-900">${escHtml(l.username || 'Sistema')}</td>
                    <td class="px-5 py-3"><span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-1 rounded">${escHtml(l.action)}</span></td>
                    <td class="px-5 py-3 text-gray-500 text-sm max-w-xs truncate" title="${escHtml(l.details || '')}">${escHtml(l.details || '—')}</td>
                    <td class="px-5 py-3 text-gray-500 text-xs font-mono">${escHtml(l.ip_address)}</td>
                </tr>
            `).join('');
        }

        async function loadSettings() {
            const settings = await apiFetch('/api/settings.php');
            const grid = document.getElementById('settings-grid');
            grid.innerHTML = settings.map(s => `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">${escHtml(s.setting_key)}</label>
                    <input type="text" name="${escHtml(s.setting_key)}" value="${escHtml(s.setting_value || '')}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-0.5">${escHtml(s.description || '')}</p>
                </div>
            `).join('');
        }

        document.getElementById('form-new-om').addEventListener('submit', async (e) => {
            e.preventDefault();
            const res = await apiFetch('/api/organizations.php', 'POST', {
                csrf_token: CSRF_TOKEN,
                name: document.getElementById('om-name').value.trim(),
                acronym: document.getElementById('om-acronym').value.trim().toUpperCase(),
                domain: document.getElementById('om-domain').value.trim()
            });

            if (res.error) {
                document.getElementById('om-form-error').textContent = res.error;
                document.getElementById('om-form-error').classList.remove('hidden');
            } else {
                closeNewOmModal();
                loadOrganizations();
                showSection('organizations');
            }
        });

        document.getElementById('settings-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = document.getElementById('settings-message');
            const inputs = document.querySelectorAll('#settings-grid input[name]');
            let errors = 0;

            for (const input of inputs) {
                const res = await apiFetch('/api/settings.php', 'POST', {
                    csrf_token: CSRF_TOKEN,
                    setting_key: input.name,
                    setting_value: input.value.trim()
                });
                if (res.error) errors++;
            }

            msg.classList.remove('hidden');
            if (errors === 0) {
                msg.className = 'mt-3 text-sm rounded-lg px-3 py-2 bg-green-50 text-green-700 border border-green-200';
                msg.textContent = 'Configurações salvas com sucesso!';
            } else {
                msg.className = 'mt-3 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
                msg.textContent = 'Erro ao salvar algumas configurações.';
            }
            setTimeout(() => msg.classList.add('hidden'), 4000);
        });

        function openNewOmModal() { document.getElementById('modal-new-om').classList.remove('hidden'); }
        function closeNewOmModal() { document.getElementById('modal-new-om').classList.add('hidden'); document.getElementById('form-new-om').reset(); }

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

        document.addEventListener('DOMContentLoaded', () => {
            loadDashboard();
        });
    </script>
</body>
</html>
