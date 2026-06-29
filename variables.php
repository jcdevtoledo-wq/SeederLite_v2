<?php
require_once __DIR__ . '/../lib/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$org_id = $_GET['org_id'] ?? null;

if ($method === 'GET') {
    if (!$org_id) {
        jsonResponse(['error' => 'Organization ID required'], 400);
    }
    $stmt = $pdo->prepare("SELECT * FROM variables WHERE organization_id = ? ORDER BY name ASC");
    $stmt->execute([$org_id]);
    jsonResponse($stmt->fetchAll());
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['organization_id']) || !isset($input['name']) || !isset($input['value'])) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }
    
    // Check if variable exists for this org
    $stmt = $pdo->prepare("SELECT id FROM variables WHERE organization_id = ? AND name = ?");
    $stmt->execute([$input['organization_id'], $input['name']]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE variables SET value = ?, type = ?, description = ? WHERE id = ?");
        $stmt->execute([
            $input['value'], 
            $input['type'] ?? 'string', 
            $input['description'] ?? null,
            $existing['id']
        ]);
        jsonResponse(['id' => $existing['id'], 'message' => 'Variable updated']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO variables (organization_id, name, value, type, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $input['organization_id'], 
            $input['name'], 
            $input['value'], 
            $input['type'] ?? 'string', 
            $input['description'] ?? null
        ]);
        jsonResponse(['id' => $pdo->lastInsertId(), 'message' => 'Variable created']);
    }
}
?>
