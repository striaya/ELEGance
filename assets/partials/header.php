<?php

if (!isset($pageTitle)) $pageTitle = 'Elegance Shop';
if (!isset($activePage)) $activePage = '';
if (!isset($basePath)) $basePath = '/EcomersPakHikmat/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — Elegance Shop</title>
  <meta name="description" content="Temukan koleksi fashion premium kami yang dipilih dengan cermat.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="container">
    <a href="<?= $basePath ?>index.php" class="navbar__logo">
      ELÉG<span>ance</span>
    </a>

    <ul class="navbar__links">
      <li><a href="<?= $basePath ?>index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Beranda</a></li>
      <li><a href="<?= $basePath ?>produk/index.php" class="<?= $activePage === 'produk' ? 'active' : '' ?>">Koleksi</a></li>
      <li><a href="#tentang" class="<?= $activePage === 'tentang' ? 'active' : '' ?>">Tentang</a></li>
      <li><a href="#kontak" class="<?= $activePage === 'kontak' ? 'active' : '' ?>">Kontak</a></li>
    </ul>

    <div class="navbar__actions">
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="<?= $basePath ?>admin/index.php" class="btn btn--outline btn--sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
          </a>
        <?php endif; ?>
        <a href="<?= $basePath ?>login/logout.php" class="btn btn--ghost btn--sm">Keluar</a>
      <?php else: ?>
        <a href="<?= $basePath ?>login/index.php" class="btn btn--outline btn--sm">Masuk</a>
        <a href="<?= $basePath ?>login/register.php" class="btn btn--primary btn--sm">Daftar</a>
      <?php endif; ?>

      <button class="navbar__icon-btn" id="menuToggle" aria-label="Menu" style="display:none">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>
  </div>
</nav>
