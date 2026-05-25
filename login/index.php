<?php
require_once '../config/session.php';
require_once '../config/database.php';

if (isLoggedIn()) {
  redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = sanitize($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($email) || empty($password)) {
    $error = 'Email dan password wajib diisi.';
  } else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['nama']    = $user['nama'];
      $_SESSION['email']   = $user['email'];
      $_SESSION['role']    = $user['role'];

      flashMessage('success', 'Selamat datang kembali, ' . $user['nama'] . '!');

      if ($user['role'] === 'admin') {
        redirect('/EcomersPakHikmat/admin/index.php');
      } else {
        redirect('/EcomersPakHikmat/index.php');
      }
    } else {
      $error = 'Email atau password salah.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — Elegance Shop</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

  <div class="auth-page">
    <!-- Visual side -->
    <div class="auth-visual">
      <div>
        <a href="/index.php" class="auth-visual__logo">ELÉGance</a>
        <div class="auth-visual__tagline">Premium Fashion Store</div>
        <div class="auth-visual__deco"></div>
        <p class="auth-visual__quote">
          "Keanggunan bukan tentang apa yang Anda kenakan, melainkan bagaimana Anda memakainya."
        </p>
      </div>
    </div>

    <!-- Form side -->
    <div class="auth-form-side">
      <div class="auth-form-box">
        <div style="margin-bottom:36px">
          <h1 class="auth-form-box__title">Masuk</h1>
          <p class="auth-form-box__subtitle">Selamat datang kembali di Elegance Shop</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert--danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php $flash = getFlash();
        if ($flash): ?>
          <div class="alert alert--<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
          <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              placeholder="nama@email.com" required autofocus>
          </div>

          <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password"
              placeholder="Masukkan kata sandi" required>
          </div>

          <button type="submit" class="btn btn--primary btn--full" style="margin-top:8px">
            Masuk ke Akun
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4" />
              <polyline points="10 17 15 12 10 7" />
              <line x1="15" y1="12" x2="3" y2="12" />
            </svg>
          </button>
        </form>

        <div style="margin-top:28px;padding-top:24px;border-top:1px solid var(--border);text-align:center">
          <p style="font-size:0.85rem;color:var(--text-light)">
            Belum punya akun?
            <a href="register.php" style="color:var(--accent);font-weight:500">Daftar sekarang</a>
          </p>
        </div>

        <div style="margin-top:16px;text-align:center">
          <a href="/index.php" style="font-size:0.8rem;color:var(--text-light);display:inline-flex;align-items:center;gap:6px;transition:color 0.2s" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-light)'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="15 18 9 12 15 6" />
            </svg>
            Kembali ke Beranda
          </a>
        </div>

        <!-- Demo credentials hint -->
        <div style="margin-top:32px;padding:16px;background:var(--off-white);border:1px solid var(--border);font-size:0.78rem;color:var(--text-light)">
          <div style="font-weight:500;color:var(--text-mid);margin-bottom:8px;letter-spacing:0.06em;text-transform:uppercase;font-size:0.68rem">Demo Akses</div>
          <div>Admin: <code style="color:var(--accent)">admin@elegance.com</code> / <code style="color:var(--accent)">password</code></div>
          <div style="margin-top:4px">User: <code style="color:var(--accent)">budi@example.com</code> / <code style="color:var(--accent)">password</code></div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>