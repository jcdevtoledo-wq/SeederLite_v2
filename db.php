<?php
// Database configuration
$host = 'localhost';
$db   = 'seederlinux';
$user = 'seeder';
$pass = 'seeder123';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

function jsonResponse($data, $code = 200) {
    header('Content-Type: application/json', true, $code);
    echo json_encode($data);
    exit;
}
?>
