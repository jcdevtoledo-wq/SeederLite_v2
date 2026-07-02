<?php
require_once __DIR__ . 
'/../lib/db.php';
checkAuth();

if ($_SERVER[
'REQUEST_METHOD'
] !== 
'POST'
) {
    jsonResponse([
'error'
 => 
'Method not allowed'
], 405);
}

$input = json_decode(file_get_contents(
'php://input'
), true);

if (!isset($input[
'csrf_token'
])) {
    logActivity('Generate Bundle Failed', 'CSRF token missing');
    jsonResponse([
'error'
 => 
'CSRF token missing'
], 403);
}
verifyCSRFToken($input[
'csrf_token'
]);

$org_id = validateInput($input[
'organization_id'
] ?? null, 'int', 'Organization ID');
$selected_script_ids = $input[
'selected_script_ids'
] ?? []; // Array de IDs de scripts customizados

if (!$org_id) {
    logActivity('Generate Bundle Failed', 'Organization ID required');
    jsonResponse([
'error'
 => 
'Organization ID required'
], 400);
}

// 1. Get organization details
$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
$stmt->execute([$org_id]);
$org = $stmt->fetch();

if (!$org) {
    logActivity('Generate Bundle Failed', 'Organization not found: ' . $org_id);
    jsonResponse([
'error'
 => 
'Organization not found'
], 404);
}

// 2. Get variables for this organization
$stmt = $pdo->prepare("SELECT name, value FROM variables WHERE organization_id = ?");
$stmt->execute([$org_id]);
$vars = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 3. Get scripts (Core + Selected Custom Scripts)
$scripts_content = [];

// Fetch core scripts
$stmt_core = $pdo->prepare("SELECT content FROM scripts WHERE is_core = TRUE ORDER BY name ASC");
$stmt_core->execute();
foreach ($stmt_core->fetchAll(PDO::FETCH_COLUMN) as $content) {
    $scripts_content[] = $content;
}

// Fetch selected custom scripts
if (!empty($selected_script_ids)) {
    $placeholders = implode(',', array_fill(0, count($selected_script_ids), '?'));
    $stmt_custom = $pdo->prepare("SELECT content FROM scripts WHERE id IN ($placeholders) AND organization_id = ? AND is_core = FALSE ORDER BY name ASC");
    $stmt_custom->execute(array_merge($selected_script_ids, [$org_id]));
    foreach ($stmt_custom->fetchAll(PDO::FETCH_COLUMN) as $content) {
        $scripts_content[] = $content;
    }
}

// 4. Concatenate and Replace placeholders
$bundle_content = "#!/bin/bash\n";
$bundle_content .= "# Generated for: " . $org['name'] . " (" . $org['acronym'] . ")\n";
$bundle_content .= "# Date: " . date('Y-m-d H:i:s') . "\n\n";

// Base functions for logging in the bundle script itself
$bundle_content .= "log_info() { echo -e \"\\e[34m[*] \$1\\e[0m\"; }\n";
$bundle_content .= "log_success() { echo -e \"\\e[32m[+] \$1\\e[0m\"; }\n";
$bundle_content .= "log_error() { echo -e \"\\e[31m[!] \$1\\e[0m\"; }\n\n";

foreach ($scripts_content as $content) {
    // Remove o shebang de cada script individual para concatenar corretamente
    $content = preg_replace('/^#!\/bin\/bash\s*/', '', $content);
    $bundle_content .= $content . "\n\n";
}

// Replace variables
foreach ($vars as $name => $value) {
    $bundle_content = str_replace('{{' . $name . '}}', $value, $bundle_content);
}

// Default org variables
$bundle_content = str_replace('{{OM_NAME}}', $org['name'], $bundle_content);
$bundle_content = str_replace('{{OM_ACRONYM}}', $org['acronym'], $bundle_content);
$bundle_content = str_replace('{{DOMINIO}}', $org['domain'], $bundle_content);

// 5. Save bundle
$stmt = $pdo->prepare("INSERT INTO deploy_bundles (organization_id, content) VALUES (?, ?)");
try {
    $stmt->execute([$org_id, $bundle_content]);
    $bundle_id = $pdo->lastInsertId();
    logActivity('Bundle Generated', 'Bundle ID ' . $bundle_id . ' generated for OM ' . $org['acronym']);
    jsonResponse([
'id'
 => $bundle_id,
'message'
 => 
'Bundle generated successfully'
,
'download_url'
 => 
'/api/bundle.php?id='
 . $bundle_id
]);
} catch (PDOException $e) {
    logActivity('Generate Bundle Failed', 'Database error saving bundle: ' . $e->getMessage());
    jsonResponse([
'error'
 => 
'Database error saving bundle'
], 500);
}
?>
