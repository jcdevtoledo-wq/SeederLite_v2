<?php
require_once __DIR__ . '/../lib/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM organizations ORDER BY name ASC");
    jsonResponse($stmt->fetchAll());
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['name']) || !isset($input['acronym'])) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }
    
    $stmt = $pdo->prepare("INSERT INTO organizations (name, acronym, domain) VALUES (?, ?, ?)");
    $stmt->execute([$input['name'], $input['acronym'], $input['domain'] ?? null]);
    jsonResponse(['id' => $pdo->lastInsertId(), 'message' => 'Organization created']);
}
?>
