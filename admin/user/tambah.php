<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

requireAdmin();
$adminPage = 'user';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize($_POST['nama'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()) {
            $error = 'Email sudah digunakan.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins    = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            $ins->bind_param('ssss', $nama, $email, $hashed, $role);
            if ($ins->execute()) {
                flashMessage('success', "Pengguna '$nama' berhasil ditambahkan.");
                redirect('/admin/user/index.php');
            } else {
                $error = 'Gagal menyimpan pengguna.';
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
  <title>Tambah Pengguna — Admin</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<div class="admin-layout">
  <?php include '../partials/sidebar.php'; ?>
  <main class="admin-main">
    <header class="admin-topbar">
      <span class="admin-topbar__title">Tambah Pengguna</span>
      <div class="admin-topbar__user">
        <div class="admin-topbar__avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
      </div>
    </header>
    <div class="admin-content">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">Tambah Pengguna</h1>
          <p class="page-header__sub">Buat akun pengguna baru</p>
        </div>
        <a href="index.php" class="btn btn--ghost btn--sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali
        </a>
      </div>
      <?php if ($error): ?><div class="alert alert--danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <div class="form-page">
        <div class="form-card">
          <form method="POST" action="tambah.php">
            <div class="form-group">
              <label for="nama">Nama Lengkap *</label>
              <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
              <label for="email">Email *</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" placeholder="Min. 6 karakter" required>
              </div>
              <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                  <option value="user" <?= (($_POST['role'] ?? 'user') === 'user') ? 'selected' : '' ?>>User</option>
                  <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
              </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px">
              <button type="submit" class="btn btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan Pengguna
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
