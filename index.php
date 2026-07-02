<?php
// index.php - Main Router
$request = $_SERVER[
'REQUEST_URI'
];
$path = parse_url($request, PHP_URL_PATH);

session_start();
require_once __DIR__ . 
'/lib/db.php'
;

// Generate CSRF token for views
generateCSRFToken();

// API routes
if (strpos($path, 
'/api/'
) === 0) {
    $api_endpoint = str_replace(
'/api/'
, 
''
, $path);
    $file = __DIR__ . 
'/api/'
 . $api_endpoint;

    // Handle specific API endpoints
    if ($api_endpoint === 
'login.php'
 || 
    $api_endpoint === 
'organizations.php'
 || 
    $api_endpoint === 
'variables.php'
 || 
    $api_endpoint === 
'generate-bundle.php'
 || 
    $api_endpoint === 
'bundle.php'
 ||
    $api_endpoint === 
'scripts.php'
 ||
    $api_endpoint === 
'activity_log.php'
 ||
    $api_endpoint === 
'settings.php'
 ||
    $api_endpoint === 
'health.php'
) {
        if (file_exists($file)) {
            require_once $file;
        } else {
            jsonResponse([
'error'
 => 
'API endpoint not found: '
 . $api_endpoint], 404);
        }
    } else {
        jsonResponse([
'error'
 => 
'Invalid API endpoint: '
 . $api_endpoint], 404);
    }
} elseif ($path === 
'/login'
) {
    if (isset($_SESSION[
'loggedin'
]) && $_SESSION[
'loggedin'
] === true) {
        header(
'Location: /admin'
);
        exit;
    }
    include __DIR__ . 
'/painel/login.php'
;
} elseif ($path === 
'/logout'
) {
    logActivity(
'Logout'
, 
'User '
 . ($_SESSION[
'username'
] ?? 
'unknown'
) . 
' logged out.'
);
    session_destroy();
    header(
'Location: /'
);
    exit;
} elseif ($path === 
'/admin'
) {
    if (!isset($_SESSION[
'loggedin'
]) || $_SESSION[
'loggedin'
] !== true) {
        header(
'Location: /login'
);
        exit;
    }
    include __DIR__ . 
'/painel/index.php'
;
} elseif ($path === 
'/agent.py'
) {
    header(
'Content-Type: text/x-python'
);
    readfile(__DIR__ . 
'/agent.py'
);
} elseif ($path === 
'/DOCUMENTACAO.md'
) {
    header(
'Content-Type: text/markdown'
);
    readfile(__DIR__ . 
'/Documentação do SeederLinux Lite.md'
);
} else {
    // Serve public landing page by default
    include __DIR__ . 
'/painel/public.php'
;
}
?>
