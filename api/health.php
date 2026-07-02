<?php
require_once __DIR__ . 
'/../lib/db.php';

header('Content-Type: application/json');

$status = ['status' => 'ok', 'database' => 'ok'];

try {
    $pdo->query('SELECT 1');
} catch (PDOException $e) {
    $status['status'] = 'error';
    $status['database'] = 'error';
    $status['database_error'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($status);
?>
