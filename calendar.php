<?php
require_once __DIR__ . '/config/auth.php';
requireLogin();

$pageTitle = 'Kalender Ruangan';
$month = isset($_GET['month']) ? max(1, min(12, (int)$_GET['month'])) : (int)date('n');
$year = isset($_GET['year']) ? max(2024, min(2035, (int)$_GET['year'])) : (int)date('Y');
$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate = date('Y-m-t', strtotime($startDate));
$firstWeekDay = (int)date('N', strtotime($startDate));
$daysInMonth = (int)date('t', strtotime($startDate));

$rooms = db()->query("SELECT id, name FROM rooms WHERE status = 'aktif' ORDER BY name ASC");

$sql = "SELECT r.reservation_date, r.start_time, r.end_time, r.status, rm.name AS room_name, r.title
        FROM reservations r
        JOIN rooms rm ON rm.id = r.room_id
        WHERE r.reservation_date BETWEEN ? AND ?
        AND r.status IN ('pending','verified','approved')";

$params = [$startDate, $endDate];
$types = 'ss';
if ($roomId > 0) {
    $sql .= ' AND r.room_id = ?';
    $types .= 'i';
    $params[] = $roomId;
}
$sql .= ' ORDER BY r.reservation_date ASC, r.start_time ASC';

$stmt = db()->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$eventsByDate = [];
$eventRows = [];
while ($row = $result->fetch_assoc()) {
    $eventsByDate[$row['reservation_date']][] = $row;
    $eventRows[] = $row;
}
$stmt->close();

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
        <div class="form-group">
            <label>Ruangan</label>
            <select name="room_id">
                <option value="0">Semua Ruangan</option>
                <?php while ($room = $rooms->fetch_assoc()): ?>
                    <option value="<?= $room['id']; ?>" <?= $roomId === (int)$room['id'] ? 'selected' : ''; ?>><?= e($room['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit">Tampilkan</button>
    </form>
</div>

<div class="card">
    <h3><?= e(date('F Y', strtotime($startDate))); ?></h3>
    <div class="calendar mt-3">
        <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dayName): ?>
            <div class="day-name"><?= $dayName; ?></div>
        <?php endforeach; ?>

        <?php for ($i = 1; $i < $firstWeekDay; $i++): ?>
            <div class="day-cell muted"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
            <?php $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day); ?>
            <div class="day-cell">
                <div class="day-number"><?= $day; ?></div>
                <?php if (!empty($eventsByDate[$dateKey])): ?>
                    <?php foreach (array_slice($eventsByDate[$dateKey], 0, 3) as $event): ?>
                        <div class="day-event">
                            <?= e(substr($event['start_time'],0,5)); ?> | <?= e($event['room_name']); ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($eventsByDate[$dateKey]) > 3): ?>
                        <div class="small-text">+<?= count($eventsByDate[$dateKey]) - 3; ?> kegiatan</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="small-text">Kosong</div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<div class="card">
    <h3>Daftar Reservasi Bulan Ini</h3>
    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Ruangan</th>
                    <th>Kegiatan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($eventRows) > 0): ?>
                    <?php foreach ($eventRows as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?></td>
                            <td><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></td>
                            <td><?= e($row['room_name']); ?></td>
                            <td><?= e($row['title']); ?></td>
                            <td><span class="<?= e(statusBadgeClass($row['status'])); ?>"><?= e(statusLabel($row['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Belum ada data reservasi pada bulan ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
