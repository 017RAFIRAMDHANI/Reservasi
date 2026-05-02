<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM rooms WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    setFlash('success', 'Data ruangan berhasil dihapus.');
}
redirect('rooms.php');
