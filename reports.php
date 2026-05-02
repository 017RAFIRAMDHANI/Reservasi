<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);

$pageTitle = 'Laporan Penggunaan Ruangan';
$month = isset($_GET['month']) ? max(1, min(12, (int)$_GET['month'])) : (int)date('n');
$year = isset($_GET['year']) ? max(2024, min(2035, (int)$_GET['year'])) : (int)date('Y');
$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate = date('Y-m-t', strtotime($startDate));

$stmt = db()->prepare("SELECT rm.name AS room_name, COUNT(*) AS total_penggunaan
    FROM reservations r
    JOIN rooms rm ON rm.id = r.room_id
    WHERE r.status = 'approved' AND r.reservation_date BETWEEN ? AND ?
    GROUP BY r.room_id, rm.name
    ORDER BY total_penggunaan DESC, rm.name ASC");
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$summary = $stmt->get_result();

$stmt2 = db()->prepare("SELECT r.*, u.name AS user_name, rm.name AS room_name
    FROM reservations r
    JOIN users u ON u.id = r.user_id
    JOIN rooms rm ON rm.id = r.room_id
    WHERE r.status = 'approved' AND r.reservation_date BETWEEN ? AND ?
    ORDER BY r.reservation_date ASC, r.start_time ASC");
$stmt2->bind_param('ss', $startDate, $endDate);
$stmt2->execute();
$details = $stmt2->get_result();

$totalApproved = 0;
$totalRoomsUsed = 0;
$summaryRows = [];
while ($row = $summary->fetch_assoc()) {
    $summaryRows[] = $row;
    $totalApproved += (int)$row['total_penggunaan'];
}
$totalRoomsUsed = count($summaryRows);

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <form method="GET" class="inline-form">
        <div class="form-group">
            <label>Bulan</label>
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m; ?>" <?= $m === $month ? 'selected' : ''; ?>><?= date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Tahun</label>
            <select name="year">
                <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?= $y; ?>" <?= $y === $year ? 'selected' : ''; ?>><?= $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit">Filter</button>
    </form>
</div>

<div class="grid-3">
    <div class="stat-card">
        <h3>Total Penggunaan Disetujui</h3>
        <div class="number"><?= $totalApproved; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Ruangan Terpakai</h3>
        <div class="number"><?= $totalRoomsUsed; ?></div>
    </div>
    <div class="stat-card">
        <h3>Periode Laporan</h3>
        <div class="number" style="font-size:22px;"><?= e(date('F Y', strtotime($startDate))); ?></div>
    </div>
</div>

<div class="card mt-4">
    <h3>Rekap Penggunaan per Ruangan</h3>
    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Ruangan</th>
                    <th>Total Penggunaan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($summaryRows): ?>
                    <?php foreach ($summaryRows as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1; ?></td>
                            <td><?= e($row['room_name']); ?></td>
                            <td><?= e($row['total_penggunaan']); ?> kali</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">Belum ada data laporan pada periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>Detail Penggunaan</h3>
    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Ruangan</th>
                    <th>Pemohon</th>
                    <th>Kegiatan</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $details->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?></td>
                        <td><?= e($row['room_name']); ?></td>
                        <td><?= e($row['user_name']); ?></td>
                        <td><?= e($row['title']); ?></td>
                        <td><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
