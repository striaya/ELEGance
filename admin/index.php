<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireAdmin();

$adminPage = 'dashboard';

// Stats
$totalProduk = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$totalUser   = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0];
$totalOrder  = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalRev    = $conn->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='done'")->fetch_row()[0];

// Recent products
$recentProduk = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Recent users
$recentUser = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin — Elegance Shop</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

  <div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
      <!-- Topbar -->
      <header class="admin-topbar">
        <div style="display:flex;align-items:center;gap:12px">
          <button id="menuToggle" class="navbar__icon-btn" style="display:none" aria-label="Menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="3" y1="6" x2="21" y2="6" />
              <line x1="3" y1="12" x2="21" y2="12" />
              <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
          </button>
          <span class="admin-topbar__title">Dashboard</span>
        </div>
        <div class="admin-topbar__user">
          <div class="admin-topbar__avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
          <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
        </div>
      </header>

      <div class="admin-content">

        <?php if ($flash): ?>
          <div class="alert alert--<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
        <?php endif; ?>

        <div class="page-header">
          <div>
            <h1 class="page-header__title">Selamat Datang, <?= htmlspecialchars(explode(' ', $_SESSION['nama'])[0]) ?></h1>
            <p class="page-header__sub">Ringkasan aktivitas toko Anda hari ini</p>
          </div>
          <a href="produk/tambah.php" class="btn btn--primary btn--sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Tambah Produk
          </a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card__label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                <line x1="3" y1="6" x2="21" y2="6" />
              </svg>
              Total Produk
            </div>
            <div class="stat-card__value"><?= $totalProduk ?></div>
            <div class="stat-card__sub">Produk aktif di toko</div>
          </div>

          <div class="stat-card">
            <div class="stat-card__label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                <circle cx="9" cy="7" r="4" />
              </svg>
              Pengguna
            </div>
            <div class="stat-card__value"><?= $totalUser ?></div>
            <div class="stat-card__sub">Pelanggan terdaftar</div>
          </div>

          <div class="stat-card">
            <div class="stat-card__label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13" />
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                <circle cx="5.5" cy="18.5" r="2.5" />
                <circle cx="18.5" cy="18.5" r="2.5" />
              </svg>
              Total Pesanan
            </div>
            <div class="stat-card__value"><?= $totalOrder ?></div>
            <div class="stat-card__sub">Semua pesanan masuk</div>
          </div>

          <div class="stat-card">
            <div class="stat-card__label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
              </svg>
              Pendapatan
            </div>
            <div class="stat-card__value" style="font-size:1.4rem"><?= formatRupiah($totalRev) ?></div>
            <div class="stat-card__sub">Dari pesanan selesai</div>
          </div>
        </div>

        <!-- Tables row -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

          <!-- Recent Products -->
          <div class="table-card">
            <div class="table-card__header">
              <h3 class="table-card__title">Produk Terbaru</h3>
              <a href="produk/index.php" class="btn btn--ghost btn--sm">Lihat Semua</a>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Nama Produk</th>
                  <th>Harga</th>
                  <th>Stok</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentProduk)): ?>
                  <tr>
                    <td colspan="3" style="text-align:center;color:var(--text-light);padding:28px">Belum ada produk</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentProduk as $p): ?>
                    <tr>
                      <td>
                        <a href="produk/edit.php?id=<?= $p['id'] ?>" style="color:var(--text-dark);font-weight:500">
                          <?= htmlspecialchars($p['nama_produk']) ?>
                        </a>
                      </td>
                      <td style="color:var(--accent)"><?= formatRupiah($p['harga']) ?></td>
                      <td><?= $p['stok'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Recent Users -->
          <div class="table-card">
            <div class="table-card__header">
              <h3 class="table-card__title">Pengguna Terbaru</h3>
              <a href="user/index.php" class="btn btn--ghost btn--sm">Lihat Semua</a>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Role</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentUser)): ?>
                  <tr>
                    <td colspan="3" style="text-align:center;color:var(--text-light);padding:28px">Belum ada pengguna</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentUser as $u): ?>
                    <tr>
                      <td style="font-weight:500;color:var(--text-dark)"><?= htmlspecialchars($u['nama']) ?></td>
                      <td style="font-size:0.82rem"><?= htmlspecialchars($u['email']) ?></td>
                      <td><span class="badge badge--<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        </div>

      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>