<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin', 'dosen', 'mahasiswa']);

$user = currentUser();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT id, status FROM reservations WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->bind_param('ii', $id, $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();
$stmt->close();

if ($reservation && in_array($reservation['status'], ['pending', 'verified', 'approved'], true)) {
    $status = 'cancelled';
    $note = 'Dibatalkan oleh pengguna.';
    $stmt = db()->prepare('UPDATE reservations SET status = ?, admin_note = ? WHERE id = ?');
    $stmt->bind_param('ssi', $status, $note, $id);
    $stmt->execute();
    $stmt->close();
    setFlash('success', 'Reservasi berhasil dibatalkan.');
} else {
    setFlash('error', 'Reservasi tidak dapat dibatalkan.');
}

redirect('my_reservations.php');
