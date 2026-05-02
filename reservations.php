<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);

$pageTitle = 'Verifikasi dan Persetujuan Reservasi';
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT r.*, u.name AS user_name, u.role AS user_role, rm.name AS room_name
        FROM reservations r
        JOIN users u ON u.id = r.user_id
        JOIN rooms rm ON rm.id = r.room_id";

$params = [];
$types = '';
if ($statusFilter !== '' && in_array($statusFilter, ['pending', 'verified', 'approved', 'rejected', 'cancelled'], true)) {
    $sql .= ' WHERE r.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}
$sql .= ' ORDER BY r.created_at DESC';

if ($params) {
    $stmt = db()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = db()->query($sql);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <form method="GET" class="inline-form">
        <div class="form-group">
            <label>Filter Status</label>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="verified" <?= $statusFilter === 'verified' ? 'selected' : ''; ?>>Terverifikasi</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
            </select>
        </div>
        <button type="submit">Tampilkan</button>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pemohon</th>
                    <th>Role</th>
                    <th>Ruangan</th>
                    <th>Kegiatan</th>
                    <th>Jadwal</th>
                    <th>Dokumen</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Aksi Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= e($row['user_name']); ?></td>
                        <td><?= e(roleLabel($row['user_role'])); ?></td>
                        <td><?= e($row['room_name']); ?></td>
                        <td>
                            <strong><?= e($row['title']); ?></strong>
                            <div class="small-text"><?= e($row['purpose']); ?></div>
                        </td>
                        <td>
                            <?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?><br>
                            <span class="small-text"><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></span>
                        </td>
                        <td>
                            <?php if ($row['document']): ?>
                                <a class="btn light" target="_blank" href="uploads/<?= e($row['document']); ?>">Lihat File</a>
                            <?php else: ?>
                                <span class="small-text">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="<?= e(statusBadgeClass($row['status'])); ?>"><?= e(statusLabel($row['status'])); ?></span></td>
                        <td><?= e($row['admin_note'] ?: '-'); ?></td>
                        <td>
                            <?php if (in_array($row['status'], ['pending', 'verified'], true)): ?>
                                <form method="POST" action="reservation_action.php" class="mt-2">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <div class="form-group">
                                        <input type="text" name="admin_note" placeholder="Catatan admin">
                                    </div>
                                    <div class="actions">
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <button class="btn info" type="submit" name="action" value="verify">Verifikasi</button>
                                        <?php endif; ?>
                                        <button class="btn success" type="submit" name="action" value="approve">Setujui</button>
                                        <button class="btn danger" type="submit" name="action" value="reject">Tolak</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <span class="small-text">Selesai diproses</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
