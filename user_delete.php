<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current = currentUser();

if ($id > 0 && $id !== (int)$current['id']) {
    $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    setFlash('success', 'Akun berhasil dihapus.');
} else {
    setFlash('error', 'Akun ini tidak dapat dihapus.');
}
redirect('users.php');
