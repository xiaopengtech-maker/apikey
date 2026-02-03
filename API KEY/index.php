<?php
$keysFile = 'keys.json';

// Load keys
$keys = json_decode(file_get_contents($keysFile), true) ?? [];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $newKey = [
            'id' => count($keys) + 1,
            'key' => $_POST['key'],
            'expiration' => $_POST['expiration'] === 'permanent' ? 'permanent' : $_POST['expiration_date']
        ];
        $keys[] = $newKey;
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $keys = array_filter($keys, fn($k) => $k['id'] !== $id);
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        foreach ($keys as &$k) {
            if ($k['id'] === $id) {
                $k['key'] = $_POST['key'];
                $k['expiration'] = $_POST['expiration'] === 'permanent' ? 'permanent' : $_POST['expiration_date'];
                break;
            }
        }
    }
    file_put_contents($keysFile, json_encode(array_values($keys)));
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Check if editing
$editKey = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    foreach ($keys as $k) {
        if ($k['id'] === $id) {
            $editKey = $k;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Key Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .edit-form { display: none; }
    </style>
</head>
<body>
    <h1>API Key Manager</h1>

    <form method="post">
        <input type="hidden" name="action" value="<?php echo $editKey ? 'edit' : 'add'; ?>">
        <?php if ($editKey): ?>
            <input type="hidden" name="id" value="<?php echo $editKey['id']; ?>">
        <?php endif; ?>
        <label>Key: <input type="text" name="key" value="<?php echo $editKey['key'] ?? ''; ?>" required></label><br>
        <label>Expiration:
            <select name="expiration" onchange="toggleDate(this)">
                <option value="permanent" <?php echo ($editKey['expiration'] ?? '') === 'permanent' ? 'selected' : ''; ?>>Permanent</option>
                <option value="date" <?php echo is_numeric(strtotime($editKey['expiration'] ?? '')) ? 'selected' : ''; ?>>Date</option>
            </select>
        </label>
        <input type="date" name="expiration_date" id="exp_date" value="<?php echo is_numeric(strtotime($editKey['expiration'] ?? '')) ? $editKey['expiration'] : ''; ?>" style="display: <?php echo is_numeric(strtotime($editKey['expiration'] ?? '')) ? 'inline' : 'none'; ?>;"><br>
        <button type="submit"><?php echo $editKey ? 'Update' : 'Add'; ?> Key</button>
        <?php if ($editKey): ?>
            <a href="?">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>Keys</h2>
    <table>
        <tr><th>ID</th><th>Key</th><th>Expiration</th><th>Actions</th></tr>
        <?php foreach ($keys as $key): ?>
        <tr>
            <td><?php echo $key['id']; ?></td>
            <td><?php echo htmlspecialchars($key['key']); ?></td>
            <td><?php echo $key['expiration']; ?></td>
            <td>
                <a href="?edit=<?php echo $key['id']; ?>">Edit</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $key['id']; ?>">
                    <button type="submit" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <script>
        function toggleDate(sel) {
            document.getElementById('exp_date').style.display = sel.value === 'date' ? 'inline' : 'none';
        }
    </script>
</body>
</html>
