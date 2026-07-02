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
    $stmt = $pdo->query("SELECT setting_key, setting_value, description FROM system_settings ORDER BY setting_key ASC");
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
        logActivity('Update Settings Failed', 'CSRF token missing');
        jsonResponse([
'error'
 => 
'CSRF token missing'
], 403);
    }
    verifyCSRFToken($input[
'csrf_token'
]);

    if (!isset($input[
'setting_key'
]) || !isset($input[
'setting_value'
])) {
        logActivity('Update Settings Failed', 'Missing required fields');
        jsonResponse([
'error'
 => 
'Missing required fields'
], 400);
    }

    $setting_key = validateInput($input[
'setting_key'
], 'string', 'Setting Key');
    $setting_value = $input[
'setting_value'
]; // Value can be anything
    $description = validateInput($input[
'description'
] ?? null, 'string', 'Description');

    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) 
                           VALUES (?, ?, ?) 
                           ON CONFLICT (setting_key) 
                           DO UPDATE SET setting_value = EXCLUDED.setting_value, description = EXCLUDED.description, updated_at = CURRENT_TIMESTAMP");
    try {
        $stmt->execute([$setting_key, $setting_value, $description]);
        logActivity('System Setting Updated', 'Setting ' . $setting_key . ' updated.');
        jsonResponse([
'message'
 => 
'Setting saved successfully'
]);
    } catch (PDOException $e) {
        logActivity('Update Settings Failed', 'Database error: ' . $e->getMessage());
        jsonResponse([
'error'
 => 
'Database error'
], 500);
    }
} else {
    jsonResponse([
'error'
 => 
'Method not allowed'
], 405);
}
?>
