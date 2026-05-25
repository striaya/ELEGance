<?php
require_once '../config/session.php';
require_once '../config/database.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize($_POST['nama'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi'] ?? '';

    if (empty($nama) || empty($email) || empty($password) || empty($konfirm)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } elseif ($password !== $konfirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        // Check email unique
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();

        if ($existing) {
            $error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insStmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'user')");
            $insStmt->bind_param('sss', $nama, $email, $hashed);

            if ($insStmt->execute()) {
                flashMessage('success', 'Akun berhasil dibuat. Silakan masuk.');
                redirect('index.php');
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
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
  <title>Daftar — Elegance Shop</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-visual">
    <div>
      <a href="/index.php" class="auth-visual__logo">ELÉGance</a>
      <div class="auth-visual__tagline">Bergabunglah Bersama Kami</div>
      <div class="auth-visual__deco"></div>
      <p class="auth-visual__quote">
        "Mulailah perjalanan gaya Anda bersama Elegance Shop hari ini."
      </p>
    </div>
  </div>

  <div class="auth-form-side">
    <div class="auth-form-box">
      <div style="margin-bottom:36px">
        <h1 class="auth-form-box__title">Buat Akun</h1>
        <p class="auth-form-box__subtitle">Daftarkan diri Anda dan mulai berbelanja</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="form-group">
          <label for="nama">Nama Lengkap</label>
          <input type="text" id="nama" name="nama"
                 value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                 placeholder="Nama Anda" required autofocus>
        </div>

        <div class="form-group">
          <label for="email">Alamat Email</label>
          <input type="email" id="email" name="email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 placeholder="nama@email.com" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password"
                   placeholder="Min. 6 karakter" required>
          </div>
          <div class="form-group">
            <label for="konfirmasi">Konfirmasi Sandi</label>
            <input type="password" id="konfirmasi" name="konfirmasi"
                   placeholder="Ulangi sandi" required>
          </div>
        </div>

        <button type="submit" class="btn btn--primary btn--full" style="margin-top:4px">
          Buat Akun Sekarang
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </button>
      </form>

      <div style="margin-top:28px;padding-top:24px;border-top:1px solid var(--border);text-align:center">
        <p style="font-size:0.85rem;color:var(--text-light)">
          Sudah punya akun?
          <a href="index.php" style="color:var(--accent);font-weight:500">Masuk di sini</a>
        </p>
      </div>

      <div style="margin-top:16px;text-align:center">
        <a href="/index.php" style="font-size:0.8rem;color:var(--text-light);display:inline-flex;align-items:center;gap:6px" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-light)'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali ke Beranda
        </a>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
