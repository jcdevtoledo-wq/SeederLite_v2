<?php
require_once __DIR__ . '/../lib/db.php';
checkAuth();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM organizations ORDER BY name ASC");
    jsonResponse($stmt->fetchAll());
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['csrf_token'])) {
        jsonResponse(['error' => 'CSRF token missing'], 403);
    }
    verifyCSRFToken($input['csrf_token']);

    if (!isset($input['name']) || !isset($input['acronym'])) {
        jsonResponse(['error' => 'Missing required fields'], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO organizations (name, acronym, domain) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$input['name'], $input['acronym'], $input['domain'] ?? null]);
        jsonResponse(['id' => $pdo->lastInsertId(), 'message' => 'Organization created']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23505) { // unique violation
            jsonResponse(['error' => 'Acronym already exists'], 400);
        }
        jsonResponse(['error' => 'Database error'], 500);
    }
}
?>
