<?php
require_once __DIR__ . 
'/../lib/db.php';
checkAuth();

$method = $_SERVER[
'REQUEST_METHOD'
];

if ($method === 
'GET'
) {
    $limit = validateInput($_GET[
'limit'
] ?? 50, 'int', 'Limit');
    $offset = validateInput($_GET[
'offset'
] ?? 0, 'int', 'Offset');

    $stmt = $pdo->prepare("SELECT al.id, u.username, al.action, al.details, al.ip_address, al.timestamp 
                           FROM activity_log al
                           LEFT JOIN users u ON al.user_id = u.id
                           ORDER BY al.timestamp DESC
                           LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    jsonResponse($stmt->fetchAll());
} else {
    jsonResponse([
'error'
 => 
'Method not allowed'
], 405);
}
?>
