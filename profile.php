<?php
require_once __DIR__ . '/config/auth.php';
requireLogin();

$pageTitle = 'Kelola Profil';
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $nimNidn = trim($_POST['nim_nidn'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '') {
        setFlash('error', 'Nama dan email wajib diisi.');
        redirect('profile.php');
    }

    if ($password !== '') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare('UPDATE users SET name=?, email=?, phone=?, nim_nidn=?, department=?, password=? WHERE id=?');
        $stmt->bind_param('ssssssi', $name, $email, $phone, $nimNidn, $department, $hashed, $user['id']);
    } else {
        $stmt = db()->prepare('UPDATE users SET name=?, email=?, phone=?, nim_nidn=?, department=? WHERE id=?');
        $stmt->bind_param('sssssi', $name, $email, $phone, $nimNidn, $department, $user['id']);
    }

    $stmt->execute();
    $stmt->close();
    refreshSessionUser((int)$user['id']);
    setFlash('success', 'Profil berhasil diperbarui.');
    redirect('profile.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= e($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($user['email']); ?>" required>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="phone" value="<?= e($user['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>NIM / NIDN</label>
                <input type="text" name="nim_nidn" value="<?= e($user['nim_nidn'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Program Studi / Unit</label>
                <input type="text" name="department" value="<?= e($user['department'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Password Baru (opsional)</label>
            <input type="password" name="password" placeholder="Isi jika ingin mengubah password">
        </div>

        <div class="actions">
            <button type="submit">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
