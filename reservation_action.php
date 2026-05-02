<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('reservations.php');
}

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$note = trim($_POST['admin_note'] ?? '');

$status = match ($action) {
    'verify' => 'verified',
    'approve' => 'approved',
    'reject' => 'rejected',
    default => '',
};

if ($id <= 0 || $status === '') {
    setFlash('error', 'Aksi tidak valid.');
    redirect('reservations.php');
}

$stmt = db()->prepare('UPDATE reservations SET status = ?, admin_note = ? WHERE id = ?');
$stmt->bind_param('ssi', $status, $note, $id);
$stmt->execute();
$stmt->close();

setFlash('success', 'Status reservasi berhasil diperbarui.');
redirect('reservations.php');
