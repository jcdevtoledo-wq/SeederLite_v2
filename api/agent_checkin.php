<?php
require_once __DIR__ . 
'/../lib/db.php';

// Este endpoint não requer autenticação de sessão, mas pode ser protegido por API Key no futuro

$method = $_SERVER[
'REQUEST_METHOD'
];

if ($method === 
'POST'
) {
    $input = json_decode(file_get_contents(
'php://input'
), true);

    $hostname = validateInput($input[
'hostname'
] ?? 
''
, 'string', 'Hostname');
    $ip_address = validateInput($input[
'ip_address'
] ?? 
''
, 'ip', 'IP Address');
    $cpu_info = validateInput($input[
'cpu_info'
] ?? null, 'string', 'CPU Info');
    $ram_gb = validateInput($input[
'ram_gb'
] ?? null, 'int', 'RAM (GB)');
    $disk_gb = validateInput($input[
'disk_gb'
] ?? null, 'int', 'Disk (GB)');
    $agent_version = validateInput($input[
'agent_version'
] ?? 
'0.0'
, 'string', 'Agent Version');
    $org_acronym = validateInput($input[
'org_acronym'
] ?? null, 'string', 'Organization Acronym');

    if (empty($hostname) || empty($ip_address) || empty($org_acronym)) {
        jsonResponse([
'error'
 => 
'Missing required fields (hostname, ip_address, org_acronym)'
], 400);
    }

    // Tenta encontrar a OM pelo acrônimo
    $stmt_org = $pdo->prepare("SELECT id FROM organizations WHERE acronym = ?");
    $stmt_org->execute([$org_acronym]);
    $org = $stmt_org->fetch();

    $organization_id = $org[
'id'
] ?? null;

    // Atualiza ou insere o inventário da máquina
    $stmt = $pdo->prepare("INSERT INTO machine_inventory (organization_id, hostname, ip_address, cpu_info, ram_gb, disk_gb, agent_version)
                           VALUES (?, ?, ?, ?, ?, ?, ?)
                           ON CONFLICT (hostname) DO UPDATE SET
                               organization_id = EXCLUDED.organization_id,
                               ip_address = EXCLUDED.ip_address,
                               cpu_info = EXCLUDED.cpu_info,
                               ram_gb = EXCLUDED.ram_gb,
                               disk_gb = EXCLUDED.disk_gb,
                               agent_version = EXCLUDED.agent_version,
                               last_checkin = CURRENT_TIMESTAMP");
    try {
        $stmt->execute([$organization_id, $hostname, $ip_address, $cpu_info, $ram_gb, $disk_gb, $agent_version]);
        logActivity('Agent Checkin', 'Machine ' . $hostname . ' checked in. Org: ' . $org_acronym . ', Agent Version: ' . $agent_version);
        jsonResponse([
'message'
 => 
'Inventory updated successfully'
]);
    } catch (PDOException $e) {
        logActivity('Agent Checkin Failed', 'Database error for ' . $hostname . ': ' . $e->getMessage());
        jsonResponse([
'error'
 => 
'Database error: '
 . $e->getMessage()], 500);
    }
} elseif ($method === 
'GET'
) {
    // Endpoint para o agente verificar a versão mais recente
    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'agent_latest_version'");
    $latest_version = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'agent_min_version'");
    $min_version = $stmt->fetchColumn();

    jsonResponse([
'latest_version'
 => $latest_version ?? 
'1.0'
,
'min_version'
 => $min_version ?? 
'1.0'
,
'agent_download_url'
 => 
'/agent.py'
 // O agente pode baixar a si mesmo
]);
} else {
    jsonResponse([
'error'
 => 
'Method not allowed'
], 405);
}
?>
