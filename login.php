<?php
session_start();
require_once __DIR__ . '/../lib/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple hardcoded check for MVP
    // In production, check against a 'users' table in the database
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header('Location: /admin');
        exit;
    } else {
        echo "Usuário ou senha inválidos. <a href='/login'>Tentar novamente</a>";
    }
}
?>
