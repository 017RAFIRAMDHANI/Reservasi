<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin', 'dosen', 'mahasiswa']);

$pageTitle = 'Ajukan Reservasi';
$user = currentUser();
$rooms = db()->query("SELECT * FROM rooms WHERE status = 'aktif' ORDER BY name ASC");
$startTimes = generateTimeOptions('07:00', '20:30', 30);
$endTimes = generateTimeOptions('07:30', '21:00', 30);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $reservationDate = $_POST['reservation_date'] ?? '';
    $startTimeInput = $_POST['start_time'] ?? '';
    $endTimeInput = $_POST['end_time'] ?? '';
    $participants = (int)($_POST['participants'] ?? 0);
    $document = uploadDocument($_FILES['document'] ?? []);

    if ($roomId <= 0 || $title === '' || $purpose === '' || $reservationDate === '' || $startTimeInput === '' || $endTimeInput === '' || $participants <= 0) {
        setFlash('error', 'Semua data utama reservasi wajib diisi.');
        redirect('reservation_form.php');
    }

    if (!isValidTimeRange($startTimeInput, $endTimeInput, '07:00', '21:00', 30)) {
        setFlash('error', 'Jam mulai dan jam selesai harus berurutan serta hanya boleh memakai kelipatan 30 menit.');
        redirect('reservation_form.php');
    }

    if ($reservationDate < date('Y-m-d')) {
        setFlash('error', 'Tanggal reservasi tidak boleh kurang dari hari ini.');
        redirect('reservation_form.php');
    }

    $startTime = substr($startTimeInput, 0, 5) . ':00';
    $endTime = substr($endTimeInput, 0, 5) . ':00';

    $stmtCheck = db()->prepare("SELECT COUNT(*) FROM reservations
        WHERE room_id = ?
        AND reservation_date = ?
        AND status IN ('pending','verified','approved')
        AND (start_time < ? AND end_time > ?)");
    $stmtCheck->bind_param('isss', $roomId, $reservationDate, $endTime, $startTime);
    $stmtCheck->execute();
    $stmtCheck->bind_result($conflictCount);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($conflictCount > 0) {
        setFlash('error', 'Jadwal bentrok dengan reservasi lain pada ruangan yang sama.');
        redirect('reservation_form.php');
    }

    $status = $user['role'] === 'admin' ? 'approved' : 'pending';
    $adminNote = $user['role'] === 'admin' ? 'Reservasi dibuat oleh admin dan otomatis disetujui.' : null;

    $stmt = db()->prepare('INSERT INTO reservations (user_id, room_id, title, purpose, reservation_date, start_time, end_time, participants, document, status, admin_note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iisssssisss', $user['id'], $roomId, $title, $purpose, $reservationDate, $startTime, $endTime, $participants, $document, $status, $adminNote);
    $stmt->execute();
    $stmt->close();

    $message = $user['role'] === 'admin'
        ? 'Reservasi admin berhasil dibuat dan langsung disetujui.'
        : 'Pengajuan reservasi berhasil dikirim.';

    setFlash('success', $message);
    redirect('my_reservations.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <?php if ($user['role'] === 'admin'): ?>
        <div class="alert info">Reservasi yang dibuat admin akan langsung berstatus disetujui.</div>
    <?php endif; ?>
    <div class="alert warning">Gunakan jam mulai dan jam selesai dengan kelipatan 30 menit, misalnya 13:00 sampai 16:00. Waktu seperti 16:15 tidak diperbolehkan.</div>

    <form method="POST" enctype="multipart/form-data">
        <div class="grid-2">
            <div class="form-group">
                <label>Ruangan</label>
                <select name="room_id" required>
                    <option value="">Pilih ruangan</option>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <option value="<?= $room['id']; ?>"><?= e($room['name']); ?> - <?= e($room['location']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Kegiatan</label>
                <input type="text" name="title" required>
            </div>
        </div>

        <div class="form-group">
            <label>Tujuan / Keperluan</label>
            <textarea name="purpose" required></textarea>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="reservation_date" min="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Jam Mulai</label>
                <select name="start_time" id="start_time" data-start-time required>
                    <option value="">Pilih jam mulai</option>
                    <?php foreach ($startTimes as $timeValue => $timeLabel): ?>
                        <option value="<?= e($timeValue); ?>"><?= e($timeLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jam Selesai</label>
                <select name="end_time" id="end_time" data-end-time required>
                    <option value="">Pilih jam selesai</option>
                    <?php foreach ($endTimes as $timeValue => $timeLabel): ?>
                        <option value="<?= e($timeValue); ?>"><?= e($timeLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Jumlah Peserta</label>
                <input type="number" name="participants" min="1" required>
            </div>
            <div class="form-group">
                <label>Dokumen Pendukung (pdf/jpg/png)</label>
                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png">
            </div>
        </div>

        <div class="actions">
            <button type="submit">Kirim Pengajuan</button>
            <a class="btn light" href="my_reservations.php">Kembali</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
