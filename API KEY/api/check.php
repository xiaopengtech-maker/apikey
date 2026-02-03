<?php
header('Content-Type: application/json');

// Load keys
$keysFile = '../keys.json';
$keys = json_decode(file_get_contents($keysFile), true) ?? [];

$key = $_GET['key'] ?? '';

$valid = false;
foreach ($keys as $k) {
    if ($k['key'] === $key) {
        if ($k['expiration'] === 'permanent') {
            $valid = true;
        } elseif (strtotime($k['expiration']) > time()) {
            $valid = true;
        }
        break;
    }
}

echo json_encode(['valid' => $valid]);
?>
