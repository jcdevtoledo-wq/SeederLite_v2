<?php
// api/login.php
session_start();
require_once __DIR__ . 
'/../lib/db.php';

if ($_SERVER[
'REQUEST_METHOD'
] === 
'POST'
) {
    $username = $_POST[
'username'
] ?? 
''
;
    $password = $_POST[
'password'
] ?? 
''
;
    
    // Check CSRF token for web login
    $csrf_token = $_POST[
'csrf_token'
] ?? 
''
;
    if (!isset($_SESSION[
'csrf_token'
]) || !hash_equals($_SESSION[
'csrf_token'
], $csrf_token)) {
        logActivity(
'Login Failed'
, 
'Invalid CSRF token for user: '
 . $username);
        die("Token CSRF inválido. <a href=\'/login\'>Tentar novamente</a>");
    }

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user[
'password_hash'
])) {
        $_SESSION[
'loggedin'
] = true;
        $_SESSION[
'user_id'
] = $user[
'id'
];
        $_SESSION[
'username'
] = $user[
'username'
];
        $_SESSION[
'role'
] = $user[
'role'
];
        logActivity(
'Login Success'
, 
'User '
 . $username . 
' logged in successfully.'
);
        header(
'Location: /admin'
);
        exit;
    } else {
        logActivity(
'Login Failed'
, 
'Invalid credentials for user: '
 . $username);
        echo "Usuário ou senha inválidos. <a href=\'/login\'>Tentar novamente</a>";
    }
}
?>
