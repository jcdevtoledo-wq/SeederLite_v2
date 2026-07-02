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
    $org_id = $_GET[
'org_id'
] ?? null;
    $script_id = $_GET[
'id'
] ?? null;

    if ($script_id) {
        $script_id = validateInput($script_id, 'int', 'Script ID');
        $stmt = $pdo->prepare("SELECT id, name, filename, content, is_core, created_at FROM scripts WHERE id = ?");
        $stmt->execute([$script_id]);
        jsonResponse($stmt->fetch());
    } elseif ($org_id) {
        $org_id = validateInput($org_id, 'int', 'Organization ID');
        $stmt = $pdo->prepare("SELECT id, name, filename, is_core, created_at FROM scripts WHERE organization_id = ? OR is_core = TRUE ORDER BY is_core DESC, name ASC");
        $stmt->execute([$org_id]);
        jsonResponse($stmt->fetchAll());
    } else {
        $stmt = $pdo->prepare("SELECT id, name, filename, is_core, created_at FROM scripts WHERE is_core = TRUE ORDER BY name ASC");
        $stmt->execute();
        jsonResponse($stmt->fetchAll());
    }
} elseif ($method === 
'POST'
) {
    $input = json_decode(file_get_contents(
'php://input'
), true);

    if (!isset($input[
'csrf_token'
])) {
        logActivity('Create Script Failed', 'CSRF token missing');
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
, 'string', 'Script Name');
    $content = $input[
'content'
] ?? 
''
;
    $filename = validateInput($input[
'filename'
] ?? null, 'string', 'Filename');

    if (empty($name) || empty($content) || empty($organization_id)) {
        logActivity('Create Script Failed', 'Missing required fields for script');
        jsonResponse([
'error'
 => 
'Missing required fields'
], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO scripts (organization_id, name, filename, content, is_core) VALUES (?, ?, ?, ?, FALSE)");
    try {
        $stmt->execute([$organization_id, $name, $filename, $content]);
        logActivity('Script Created', 'New custom script: ' . $name . ' for OM ' . $organization_id);
        jsonResponse([
'id'
 => $pdo->lastInsertId(), 
'message'
 => 
'Script created successfully'
]);
    } catch (PDOException $e) {
        logActivity('Create Script Failed', 'Database error: ' . $e->getMessage());
        jsonResponse([
'error'
 => 
'Database error'
], 500);
    }
} elseif ($method === 
'PUT'
) {
    $input = json_decode(file_get_contents(
'php://input'
), true);
    $script_id = validateInput($input[
'id'
] ?? null, 'int', 'Script ID');

    if (!isset($input[
'csrf_token'
])) {
        logActivity('Update Script Failed', 'CSRF token missing');
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
, 'string', 'Script Name');
    $content = $input[
'content'
] ?? 
''
;
    $filename = validateInput($input[
'filename'
] ?? null, 'string', 'Filename');

    if (empty($script_id) || empty($name) || empty($content)) {
        logActivity('Update Script Failed', 'Missing required fields for script update');
        jsonResponse([
'error'
 => 
'Missing required fields'
], 400);
    }

    $stmt = $pdo->prepare("UPDATE scripts SET name = ?, filename = ?, content = ?, version = version + 1 WHERE id = ? AND is_core = FALSE");
    try {
        $stmt->execute([$name, $filename, $content, $script_id]);
        if ($stmt->rowCount() > 0) {
            logActivity('Script Updated', 'Custom script ' . $name . ' (ID: ' . $script_id . ') updated.');
            jsonResponse([
'message'
 => 
'Script updated successfully'
]);
        } else {
            logActivity('Update Script Failed', 'Script not found or is a core script: ' . $script_id);
            jsonResponse([
'error'
 => 
'Script not found or is a core script'
], 404);
        }
    } catch (PDOException $e) {
        logActivity('Update Script Failed', 'Database error: ' . $e->getMessage());
        jsonResponse([
'error'
 => 
'Database error'
], 500);
    }
} elseif ($method === 
'DELETE'
) {
    $input = json_decode(file_get_contents(
'php://input'
), true);
    $script_id = validateInput($input[
'id'
] ?? null, 'int', 'Script ID');

    if (!isset($input[
'csrf_token'
])) {
        logActivity('Delete Script Failed', 'CSRF token missing');
        jsonResponse([
'error'
 => 
'CSRF token missing'
], 403);
    }
    verifyCSRFToken($input[
'csrf_token'
]);

    if (empty($script_id)) {
        logActivity('Delete Script Failed', 'Missing script ID for deletion');
        jsonResponse([
'error'
 => 
'Script ID required'
], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM scripts WHERE id = ? AND is_core = FALSE");
    try {
        $stmt->execute([$script_id]);
        if ($stmt->rowCount() > 0) {
            logActivity('Script Deleted', 'Custom script (ID: ' . $script_id . ') deleted.');
            jsonResponse([
'message'
 => 
'Script deleted successfully'
]);
        } else {
            logActivity('Delete Script Failed', 'Script not found or is a core script: ' . $script_id);
            jsonResponse([
'error'
 => 
'Script not found or is a core script'
], 404);
        }
    } catch (PDOException $e) {
        logActivity('Delete Script Failed', 'Database error: ' . $e->getMessage());
        jsonResponse([
'error'
 => 
'Database error'
], 500);
    }
}
?>
