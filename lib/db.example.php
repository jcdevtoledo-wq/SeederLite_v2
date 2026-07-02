<?php
// lib/db.example.php
// Copie este arquivo para lib/db.php e ajuste as credenciais.

$host = 'localhost';
$db   = 'seederlinux';
$user = 'seeder';
$pass = 'ALTERE_ESTA_SENHA';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

function jsonResponse($data, $code = 200) {
    header('Content-Type: application/json', true, $code);
    echo json_encode($data);
    exit;
}

function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        jsonResponse(['error' => 'Invalid CSRF token'], 403);
    }
}
?>
