<?php
if (!isset($adminPage)) $adminPage = '';
?>
<aside class="sidebar" id="adminSidebar">
  <div class="sidebar__logo">
    <div class="sidebar__logo-text">ELÉGance</div>
    <div class="sidebar__logo-sub">Admin Panel</div>
  </div>
  <nav class="sidebar__nav">
    <div class="sidebar__nav-label">Utama</div>
    <a href="/EcomersPakHikmat/admin/index.php" class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" />
        <rect x="14" y="3" width="7" height="7" />
        <rect x="14" y="14" width="7" height="7" />
        <rect x="3" y="14" width="7" height="7" />
      </svg>
      Dashboard
    </a>
    <div class="sidebar__nav-label">Toko</div>
    <a href="/EcomersPakHikmat/admin/produk/index.php" class="<?= $adminPage === 'produk' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
        <line x1="3" y1="6" x2="21" y2="6" />
        <path d="M16 10a4 4 0 01-8 0" />
      </svg>
      Kelola Produk
    </a>
    <a href="/EcomersPakHikmat/admin/produk/tambah.php" class="<?= $adminPage === 'tambah-produk' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="12" y1="5" x2="12" y2="19" />
        <line x1="5" y1="12" x2="19" y2="12" />
      </svg>
      Tambah Produk
    </a>
    <a href="/EcomersPakHikmat/admin/orders/index.php" class="<?= $adminPage === 'orders' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="1" y="3" width="15" height="13" />
        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
        <circle cx="5.5" cy="18.5" r="2.5" />
        <circle cx="18.5" cy="18.5" r="2.5" />
      </svg>
      Kelola Pesanan
    </a>
    <div class="sidebar__nav-label">Pengguna</div>
    <a href="/EcomersPakHikmat/admin/user/index.php" class="<?= $adminPage === 'user' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M23 21v-2a4 4 0 00-3-3.87" />
        <path d="M16 3.13a4 4 0 010 7.75" />
      </svg>
      Kelola Pengguna
    </a>
    <div class="sidebar__nav-label">Lainnya</div>
    <a href="/EcomersPakHikmat/" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <line x1="2" y1="12" x2="22" y2="12" />
        <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" />
      </svg>
      Lihat Website
    </a>
    <a href="/EcomersPakHikmat/login/logout.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
        <polyline points="16 17 21 12 16 7" />
        <line x1="21" y1="12" x2="9" y2="12" />
      </svg>
      Keluar
    </a>
  </nav>
</aside>