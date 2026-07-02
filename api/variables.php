<?php
require_once __DIR__ . '/../lib/db.php';
checkAuth();

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
    
    if (!isset($input['csrf_token'])) {
        jsonResponse(['error' => 'CSRF token missing'], 403);
    }
    verifyCSRFToken($input['csrf_token']);

    if (!isset($input['organization_id']) || !isset($input['name']) || !isset($input['value'])) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO variables (organization_id, name, value, type, description) 
                           VALUES (?, ?, ?, ?, ?) 
                           ON CONFLICT (organization_id, name) 
                           DO UPDATE SET value = EXCLUDED.value, type = EXCLUDED.type, description = EXCLUDED.description");
    $stmt->execute([
        $input['organization_id'], 
        $input['name'], 
        $input['value'], 
        $input['type'] ?? 'string', 
        $input['description'] ?? null
    ]);
    
    jsonResponse(['message' => 'Variable saved successfully']);
}
?>
