<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);

$pageTitle = 'Form Pengguna';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = [
    'name' => '',
    'email' => '',
    'role' => 'mahasiswa',
    'phone' => '',
    'nim_nidn' => '',
    'department' => '',
];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc() ?: $data;
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'mahasiswa';
    $phone = trim($_POST['phone'] ?? '');
    $nimNidn = trim($_POST['nim_nidn'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if ($name === '' || $email === '' || $role === '') {
        setFlash('error', 'Nama, email, dan role wajib diisi.');
        redirect($id > 0 ? 'user_form.php?id=' . $id : 'user_form.php');
    }

    if ($id > 0) {
        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE users SET name=?, email=?, password=?, role=?, phone=?, nim_nidn=?, department=? WHERE id=?');
            $stmt->bind_param('sssssssi', $name, $email, $hashed, $role, $phone, $nimNidn, $department, $id);
        } else {
            $stmt = db()->prepare('UPDATE users SET name=?, email=?, role=?, phone=?, nim_nidn=?, department=? WHERE id=?');
            $stmt->bind_param('ssssssi', $name, $email, $role, $phone, $nimNidn, $department, $id);
        }
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Akun pengguna berhasil diperbarui.');
    } else {
        if ($password === '') {
            setFlash('error', 'Password wajib diisi untuk akun baru.');
            redirect('user_form.php');
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO users (name, email, password, role, phone, nim_nidn, department) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssss', $name, $email, $hashed, $role, $phone, $nimNidn, $department);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Akun pengguna berhasil ditambahkan.');
    }

    redirect('users.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= e($data['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($data['email']); ?>" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Password <?= $id > 0 ? '(kosongkan jika tidak diubah)' : ''; ?></label>
                <input type="password" name="password" <?= $id === 0 ? 'required' : ''; ?>>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="dosen" <?= $data['role'] === 'dosen' ? 'selected' : ''; ?>>Dosen</option>
                    <option value="mahasiswa" <?= $data['role'] === 'mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                </select>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="phone" value="<?= e($data['phone']); ?>">
            </div>
            <div class="form-group">
                <label>NIM / NIDN</label>
                <input type="text" name="nim_nidn" value="<?= e($data['nim_nidn']); ?>">
            </div>
            <div class="form-group">
                <label>Program Studi / Unit</label>
                <input type="text" name="department" value="<?= e($data['department']); ?>">
            </div>
        </div>

        <div class="actions">
            <button type="submit">Simpan</button>
            <a class="btn light" href="users.php">Kembali</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
