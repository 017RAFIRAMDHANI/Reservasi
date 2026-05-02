<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);

$pageTitle = 'Form Ruangan';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$room = [
    'name' => '',
    'location' => '',
    'capacity' => '',
    'status' => 'aktif',
    'description' => '',
];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM rooms WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc() ?: $room;
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $status = $_POST['status'] ?? 'aktif';
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $location === '' || $capacity <= 0) {
        setFlash('error', 'Nama ruangan, lokasi, dan kapasitas wajib diisi.');
        redirect($id > 0 ? 'room_form.php?id=' . $id : 'room_form.php');
    }

    if ($id > 0) {
        $stmt = db()->prepare('UPDATE rooms SET name=?, location=?, capacity=?, status=?, description=? WHERE id=?');
        $stmt->bind_param('ssissi', $name, $location, $capacity, $status, $description, $id);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Data ruangan berhasil diperbarui.');
    } else {
        $stmt = db()->prepare('INSERT INTO rooms (name, location, capacity, status, description) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('ssiss', $name, $location, $capacity, $status, $description);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Data ruangan berhasil ditambahkan.');
    }

    redirect('rooms.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Nama Ruangan</label>
                <input type="text" name="name" value="<?= e($room['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="location" value="<?= e($room['location']); ?>" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Kapasitas</label>
                <input type="number" name="capacity" min="1" value="<?= e($room['capacity']); ?>" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="aktif" <?= $room['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="nonaktif" <?= $room['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description"><?= e($room['description']); ?></textarea>
        </div>

        <div class="actions">
            <button type="submit">Simpan</button>
            <a class="btn light" href="rooms.php">Kembali</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
