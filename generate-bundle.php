<?php
require_once __DIR__ . '/../lib/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$org_id = $input['organization_id'] ?? null;
$selected_scripts = $input['scripts'] ?? []; // Array of script IDs

if (!$org_id) {
    jsonResponse(['error' => 'Organization ID required'], 400);
}

// 1. Get organization details
$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
$stmt->execute([$org_id]);
$org = $stmt->fetch();

if (!$org) {
    jsonResponse(['error' => 'Organization not found'], 404);
}

// 2. Get variables for this organization
$stmt = $pdo->prepare("SELECT name, value FROM variables WHERE organization_id = ?");
$stmt->execute([$org_id]);
$vars = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 3. Get scripts (Core + Selected)
$placeholders = implode(',', array_fill(0, count($selected_scripts), '?'));
$query = "SELECT content FROM scripts WHERE is_core = TRUE";
if (!empty($selected_scripts)) {
    $query .= " OR id IN ($placeholders)";
}
$stmt = $pdo->prepare($query);
$stmt->execute($selected_scripts);
$scripts = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Concatenate and Replace placeholders
$bundle_content = "#!/bin/bash\n";
$bundle_content .= "# Generated for: " . $org['name'] . " (" . $org['acronym'] . ")\n";
$bundle_content .= "# Date: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($scripts as $content) {
    $bundle_content .= $content . "\n\n";
}

foreach ($vars as $name => $value) {
    $bundle_content = str_replace('{{' . $name . '}}', $value, $bundle_content);
}

// Also replace some default org variables if they exist in script
$bundle_content = str_replace('{{OM_NAME}}', $org['name'], $bundle_content);
$bundle_content = str_replace('{{OM_ACRONYM}}', $org['acronym'], $bundle_content);
$bundle_content = str_replace('{{DOMINIO}}', $org['domain'], $bundle_content);

// 5. Save bundle
$stmt = $pdo->prepare("INSERT INTO deploy_bundles (organization_id, content) VALUES (?, ?)");
$stmt->execute([$org_id, $bundle_content]);
$bundle_id = $pdo->lastInsertId();

jsonResponse([
    'id' => $bundle_id,
    'message' => 'Bundle generated successfully',
    'download_url' => '/api/bundle.php?id=' . $bundle_id
]);
?>
