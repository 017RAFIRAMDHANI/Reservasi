<?php
require_once __DIR__ . '/config/auth.php';
requireLogin();

$user = currentUser();
$pageTitle = 'Riwayat Reservasi';

if ($user['role'] === 'admin') {
    $sql = "SELECT r.*, u.name AS user_name, rm.name AS room_name
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN rooms rm ON rm.id = r.room_id
            ORDER BY r.reservation_date DESC, r.created_at DESC";
    $result = db()->query($sql);
} else {
    $stmt = db()->prepare("SELECT r.*, rm.name AS room_name
        FROM reservations r
        JOIN rooms rm ON rm.id = r.room_id
        WHERE r.user_id = ?
        ORDER BY r.reservation_date DESC, r.created_at DESC");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <?php if ($user['role'] === 'admin'): ?><th>Pemohon</th><?php endif; ?>
                    <th>Ruangan</th>
                    <th>Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <?php if ($user['role'] === 'admin'): ?><td><?= e($row['user_name']); ?></td><?php endif; ?>
                        <td><?= e($row['room_name']); ?></td>
                        <td><?= e($row['title']); ?></td>
                        <td><?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?></td>
                        <td><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></td>
                        <td><span class="<?= e(statusBadgeClass($row['status'])); ?>"><?= e(statusLabel($row['status'])); ?></span></td>
                        <td><?= e($row['admin_note'] ?: '-'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
