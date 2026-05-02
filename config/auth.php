<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

date_default_timezone_set('Asia/Jakarta');

function db(): mysqli {
    return getDB();
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

function requireRole(array $roles): void {
    requireLogin();
    $user = currentUser();
    if (!$user || !in_array($user['role'], $roles, true)) {
        setFlash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect('dashboard.php');
    }
}

function refreshSessionUser(int $userId): void {
    $stmt = db()->prepare('SELECT id, name, email, role, phone, nim_nidn, department FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['user'] = $row;
    }
    $stmt->close();
}

function countRows(string $sql): int {
    $result = db()->query($sql);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_row();
    return (int)($row[0] ?? 0);
}

function statusBadgeClass(string $status): string {
    return match ($status) {
        'pending' => 'badge warning',
        'verified' => 'badge info',
        'approved' => 'badge success',
        'rejected' => 'badge danger',
        'cancelled' => 'badge secondary',
        default => 'badge secondary',
    };
}

function statusLabel(string $status): string {
    return match ($status) {
        'pending' => 'Menunggu',
        'verified' => 'Terverifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        default => ucfirst($status),
    };
}

function roleLabel(string $role): string {
    return match ($role) {
        'admin' => 'Admin',
        'dosen' => 'Dosen',
        'mahasiswa' => 'Mahasiswa',
        default => ucfirst($role),
    };
}

function canCancelReservation(array $reservation): bool {
    return in_array($reservation['status'], ['pending', 'verified', 'approved'], true);
}

function generateTimeOptions(string $start = '07:00', string $end = '21:00', int $intervalMinutes = 30, bool $includeEnd = true): array {
    $options = [];
    $current = strtotime($start);
    $endTimestamp = strtotime($end);

    while (($includeEnd && $current <= $endTimestamp) || (!$includeEnd && $current < $endTimestamp)) {
        $time = date('H:i', $current);
        $options[$time] = $time;
        $current = strtotime('+' . $intervalMinutes . ' minutes', $current);
    }

    return $options;
}

function isValidBoundaryTime(string $time, string $minTime = '07:00', string $maxTime = '21:00', int $intervalMinutes = 30): bool {
    $normalizedTime = substr($time, 0, 5);

    if (!preg_match('/^\d{2}:\d{2}$/', $normalizedTime)) {
        return false;
    }

    [$hour, $minute] = array_map('intval', explode(':', $normalizedTime));
    if ($minute % $intervalMinutes !== 0) {
        return false;
    }

    $timestamp = strtotime($normalizedTime);
    return $timestamp !== false
        && $timestamp >= strtotime($minTime)
        && $timestamp <= strtotime($maxTime);
}

function isValidTimeRange(string $startTime, string $endTime, string $minTime = '07:00', string $maxTime = '21:00', int $intervalMinutes = 30): bool {
    if (!isValidBoundaryTime($startTime, $minTime, $maxTime, $intervalMinutes) || !isValidBoundaryTime($endTime, $minTime, $maxTime, $intervalMinutes)) {
        return false;
    }

    $startTimestamp = strtotime(substr($startTime, 0, 5));
    $endTimestamp = strtotime(substr($endTime, 0, 5));
    if ($startTimestamp === false || $endTimestamp === false || $endTimestamp <= $startTimestamp) {
        return false;
    }

    return (($endTimestamp - $startTimestamp) % ($intervalMinutes * 60)) === 0;
}

function uploadDocument(array $file): ?string {
    if (!isset($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid('doc_', true) . '.' . $ext;
    $target = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }

    return null;
}
