<?php
require_once __DIR__ . 
'/../lib/db.php';
checkAuth();

$method = $_SERVER[
'REQUEST_METHOD'
];
$org_id = $_GET[
'org_id'
] ?? null;

if ($method === 
'GET'
) {
    $org_id = validateInput($org_id, 'int', 'Organization ID');
    $stmt = $pdo->prepare("SELECT * FROM variables WHERE organization_id = ? ORDER BY name ASC");
    $stmt->execute([$org_id]);
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
        logActivity('Save Variable Failed', 'CSRF token missing');
        jsonResponse([
'error'
 => 
'CSRF token missing'
], 403);
    }
    verifyCSRFToken($input[
'csrf_token'
]);

    $organization_id = validateInput($input[
'organization_id'
] ?? null, 'int', 'Organization ID');
    $name = validateInput($input[
'name'
] ?? 
''
, 'string', 'Variable Name');
    $value = $input[
'value'
] ?? null; // Value can be anything, no specific validation type yet
    $type = validateInput($input[
'type'
] ?? 'string', 'string', 'Variable Type');
    $description = validateInput($input[
'description'
] ?? null, 'string', 'Variable Description');

    if (empty($organization_id) || empty($name)) {
        logActivity('Save Variable Failed', 'Missing required fields for variable');
        jsonResponse([
'error'
 => 
'Missing required fields'
], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO variables (organization_id, name, value, type, description) 
                           VALUES (?, ?, ?, ?, ?) 
                           ON CONFLICT (organization_id, name) 
                           DO UPDATE SET value = EXCLUDED.value, type = EXCLUDED.type, description = EXCLUDED.description");
    try {
        $stmt->execute([
            $organization_id, 
            $name, 
            $value, 
            $type, 
            $description
        ]);
        logActivity('Variable Saved', 'Variable ' . $name . ' for OM ' . $organization_id . ' saved/updated.');
        jsonResponse([
'message'
 => 
'Variable saved successfully'
]);
    } catch (PDOException $e) {
        logActivity('Save Variable Failed', 'Database error: ' . $e->getMessage());
        jsonResponse([
'error'
 => 
'Database error'
], 500);
    }
}
?>
