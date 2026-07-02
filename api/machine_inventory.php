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

    if ($org_id) {
        $org_id = validateInput($org_id, 'int', 'Organization ID');
        $stmt = $pdo->prepare("SELECT mi.*, o.acronym as organization_acronym FROM machine_inventory mi LEFT JOIN organizations o ON mi.organization_id = o.id WHERE mi.organization_id = ? ORDER BY mi.last_checkin DESC");
        $stmt->execute([$org_id]);
    } else {
        $stmt = $pdo->query("SELECT mi.*, o.acronym as organization_acronym FROM machine_inventory mi LEFT JOIN organizations o ON mi.organization_id = o.id ORDER BY mi.last_checkin DESC");
    }
    jsonResponse($stmt->fetchAll());
} else {
    jsonResponse([
'error'
 => 
'Method not allowed'
], 405);
}
?>
