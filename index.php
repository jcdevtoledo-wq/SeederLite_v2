<?php
// Simple router for SeederLinux Lite
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

session_start();

if (strpos($path, '/api/') === 0) {
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        require_once $file;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'API endpoint not found: ' . $path]);
    }
} elseif ($path === '/login') {
    include __DIR__ . '/painel/login.html';
} elseif ($path === '/logout') {
    session_destroy();
    header('Location: /');
    exit;
} elseif ($path === '/admin') {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: /login');
        exit;
    }
    include __DIR__ . '/painel/index.html';
} elseif ($path === '/agent.py') {
    header('Content-Type: text/x-python');
    readfile(__DIR__ . '/agent.py');
} elseif ($path === '/DOCUMENTACAO.md') {
    header('Content-Type: text/markdown');
    readfile(__DIR__ . '/DOCUMENTACAO.md');
} else {
    // Serve public landing page by default
    include __DIR__ . '/painel/public.html';
}
?>
