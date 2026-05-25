<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

requireAdmin();

$adminPage = 'produk';

// Handle delete
if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    // Delete image file
    $imgStmt = $conn->prepare("SELECT gambar FROM products WHERE id = ?");
    $imgStmt->bind_param('i', $hapusId);
    $imgStmt->execute();
    $imgRow = $imgStmt->get_result()->fetch_assoc();
    if ($imgRow && $imgRow['gambar']) {
        $imgPath = '../../assets/images/' . $imgRow['gambar'];
        if (file_exists($imgPath)) unlink($imgPath);
    }

    $delStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delStmt->bind_param('i', $hapusId);
    $delStmt->execute();

    flashMessage('success', 'Produk berhasil dihapus.');
    redirect('/admin/produk/index.php');
}

// Search
$search  = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$perPage = 10;
$page    = isset($_GET['halaman']) ? max(1, (int)$_GET['halaman']) : 1;
$offset  = ($page - 1) * $perPage;

$where = '1=1';
$params = [];
$types  = '';

if ($search !== '') {
    $where  .= ' AND (nama_produk LIKE ? OR kategori LIKE ?)';
    $like    = '%' . $search . '%';
    $params  = [$like, $like];
    $types   = 'ss';
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE $where");
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows  = $countStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalRows / $perPage);

$fetchParams   = $params;
$fetchParams[] = $perPage;
$fetchParams[] = $offset;
$fetchTypes    = $types . 'ii';

$stmt = $conn->prepare("SELECT * FROM products WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Produk — Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="admin-layout">
  <?php include '../partials/sidebar.php'; ?>

  <main class="admin-main">
    <header class="admin-topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="menuToggle" class="navbar__icon-btn" style="display:none">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="admin-topbar__title">Kelola Produk</span>
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
          <h1 class="page-header__title">Produk</h1>
          <p class="page-header__sub"><?= $totalRows ?> produk terdaftar</p>
        </div>
        <a href="tambah.php" class="btn btn--primary btn--sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Produk
        </a>
      </div>

      <!-- Search -->
      <div style="margin-bottom:20px">
        <form class="search-bar" method="GET" action="index.php">
          <input type="text" name="q" placeholder="Cari nama atau kategori..." value="<?= htmlspecialchars($search) ?>">
          <button type="submit" aria-label="Cari">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </button>
        </form>
      </div>

      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Gambar</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
              <tr><td colspan="7" style="text-align:center;padding:48px;color:var(--text-light)">
                Belum ada produk. <a href="tambah.php" style="color:var(--accent)">Tambah sekarang</a>
              </td></tr>
            <?php else: ?>
              <?php foreach ($products as $i => $p): ?>
                <tr>
                  <td style="color:var(--text-light)"><?= $offset + $i + 1 ?></td>
                  <td>
                    <?php if ($p['gambar'] && file_exists('../../assets/images/' . $p['gambar'])): ?>
                      <img src="../../assets/images/<?= htmlspecialchars($p['gambar']) ?>"
                           style="width:46px;height:46px;object-fit:cover;border:1px solid var(--border)">
                    <?php else: ?>
                      <div style="width:46px;height:46px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-light)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td style="font-weight:500;color:var(--text-dark)"><?= htmlspecialchars($p['nama_produk']) ?></td>
                  <td>
                    <?php if ($p['kategori']): ?>
                      <span class="badge badge--user"><?= htmlspecialchars($p['kategori']) ?></span>
                    <?php else: ?>
                      <span style="color:var(--text-light);font-size:0.8rem">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="color:var(--accent)"><?= formatRupiah($p['harga']) ?></td>
                  <td><?= $p['stok'] ?></td>
                  <td>
                    <div class="data-table__actions">
                      <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn--ghost btn--sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                      </a>
                      <a href="index.php?hapus=<?= $p['id'] ?>" class="btn btn--danger btn--sm"
                         data-confirm="Hapus produk '<?= htmlspecialchars($p['nama_produk']) ?>'?">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                        Hapus
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['halaman' => $page - 1])) ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
              <span class="active"><?= $i ?></span>
            <?php else: ?>
              <a href="?<?= http_build_query(array_merge($_GET, ['halaman' => $i])) ?>"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['halaman' => $page + 1])) ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<script src="../../assets/js/script.js"></script>
</body>
</html>
