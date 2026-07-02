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
    $stmt = $pdo->query("SELECT * FROM organizations ORDER BY name ASC");
    jsonResponse($stmt->fetchAll());
} elseif ($method === 
'POST'
) {
    $input = json_decode(file_get_contents(
'php://input'
), true);
    
    if (!isset($input[
'csrf_token'
])) {
        logActivity(
'Create Organization Failed'
, 
'CSRF token missing'
);
        jsonResponse([
'error'
 => 
'CSRF token missing'
], 403);
    }
    verifyCSRFToken($input[
'csrf_token'
]);

    $name = validateInput($input[
'name'
] ?? 
''
, 
'string'
, 
'Nome da OM'
);
    $acronym = validateInput($input[
'acronym'
] ?? 
''
, 
'string'
, 
'Sigla da OM'
);
    $domain = validateInput($input[
'domain'
] ?? 
''
, 
'domain'
, 
'Domínio AD'
);

    if (empty($name) || empty($acronym)) {
        logActivity(
'Create Organization Failed'
, 
'Missing required fields for OM'
);
        jsonResponse([
'error'
 => 
'Missing required fields'
], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO organizations (name, acronym, domain) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$name, $acronym, $domain]);
        logActivity(
'Organization Created'
, 
'New OM: '
 . $name . 
' ('
 . $acronym . 
')'
);
        jsonResponse([
'id'
 => $pdo->lastInsertId(), 
'message'
 => 
'Organization created'
]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23505) { // unique violation
            logActivity(
'Create Organization Failed'
, 
'Acronym already exists: '
 . $acronym);
            jsonResponse([
'error'
 => 
'Acronym already exists'
], 400);
        }
        logActivity(
'Create Organization Failed'
, 
'Database error: '
 . $e->getMessage());
        jsonResponse([
'error'
 => 
'Database error'
], 500);
    }
}
?>
