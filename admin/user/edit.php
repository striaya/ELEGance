<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

requireAdmin();
$adminPage = 'user';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) redirect('/admin/user/index.php');

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { flashMessage('danger','Pengguna tidak ditemukan.'); redirect('/admin/user/index.php'); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize($_POST['nama'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : $user['role'];

    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email wajib diisi.';
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $chk->bind_param('si', $email, $id);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()) {
            $error = 'Email sudah digunakan oleh pengguna lain.';
        } else {
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $error = 'Password baru minimal 6 karakter.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE users SET nama=?, email=?, password=?, role=? WHERE id=?");
                    $upd->bind_param('ssssi', $nama, $email, $hashed, $role, $id);
                    $ok = $upd->execute();
                }
            } else {
                $upd = $conn->prepare("UPDATE users SET nama=?, email=?, role=? WHERE id=?");
                $upd->bind_param('sssi', $nama, $email, $role, $id);
                $ok = $upd->execute();
            }
            if (!$error) {
                if ($ok) {
                    flashMessage('success', "Pengguna '$nama' berhasil diperbarui.");
                    redirect('/admin/user/index.php');
                } else {
                    $error = 'Gagal memperbarui pengguna.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Pengguna — Admin</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<div class="admin-layout">
  <?php include '../partials/sidebar.php'; ?>
  <main class="admin-main">
    <header class="admin-topbar">
      <span class="admin-topbar__title">Edit Pengguna</span>
      <div class="admin-topbar__user">
        <div class="admin-topbar__avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
      </div>
    </header>
    <div class="admin-content">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">Edit Pengguna</h1>
          <p class="page-header__sub"><?= htmlspecialchars($user['nama']) ?></p>
        </div>
        <a href="index.php" class="btn btn--ghost btn--sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali
        </a>
      </div>
      <?php if ($error): ?><div class="alert alert--danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <div class="form-page">
        <div class="form-card">
          <form method="POST" action="edit.php?id=<?= $id ?>">
            <div class="form-group">
              <label for="nama">Nama Lengkap *</label>
              <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? $user['nama']) ?>" required>
            </div>
            <div class="form-group">
              <label for="email">Email *</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah">
              </div>
              <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                  <option value="user" <?= ($user['role']==='user')?'selected':'' ?>>User</option>
                  <option value="admin" <?= ($user['role']==='admin')?'selected':'' ?>>Admin</option>
                </select>
              </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
              <button type="submit" class="btn btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Perbarui Pengguna
              </button>
              <a href="index.php" class="btn btn--ghost">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="../../assets/js/script.js"></script>
</body>
</html>
