<?php
require_once __DIR__ . '/../lib/db.php';

// Allow agent.py to download without auth, but validate ID
$id = $_GET['id'] ?? null;

if (!$id) {
    jsonResponse(['error' => 'Bundle ID required'], 400);
}

$stmt = $pdo->prepare("SELECT content FROM deploy_bundles WHERE id = ?");
$stmt->execute([$id]);
$bundle = $stmt->fetch();

if (!$bundle) {
    jsonResponse(['error' => 'Bundle not found'], 404);
}

header('Content-Type: text/x-shellscript');
header('Content-Disposition: attachment; filename="seeder_bundle_' . $id . '.sh"');
echo $bundle['content'];
?>
