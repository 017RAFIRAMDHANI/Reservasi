<?php
require_once __DIR__ . '/config/auth.php';
requireRole(['admin']);
$pageTitle = 'Kelola Akun Pengguna';

$result = db()->query('SELECT id, name, email, role, phone, nim_nidn, department, created_at FROM users ORDER BY id DESC');
require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="actions">
        <a class="btn" href="user_form.php">Tambah Pengguna</a>
    </div>

    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>No HP</th>
                    <th>NIM / NIDN</th>
                    <th>Prodi / Unit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= e($row['name']); ?></td>
                        <td><?= e($row['email']); ?></td>
                        <td><?= e(roleLabel($row['role'])); ?></td>
                        <td><?= e($row['phone'] ?: '-'); ?></td>
                        <td><?= e($row['nim_nidn'] ?: '-'); ?></td>
                        <td><?= e($row['department'] ?: '-'); ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn warning" href="user_form.php?id=<?= $row['id']; ?>">Edit</a>
                                <a class="btn danger" href="user_delete.php?id=<?= $row['id']; ?>" data-confirm="Hapus akun ini?">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
