<?php
require_once '../config/session.php';
require_once '../config/database.php';

$pageTitle  = 'Koleksi';
$activePage = 'produk';
$basePath   = '/EcomersPakHikmat/';

// Search & filter
$search    = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$kategori  = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : '';

// Pagination
$perPage = 9;
$page    = isset($_GET['halaman']) ? max(1, (int)$_GET['halaman']) : 1;
$offset  = ($page - 1) * $perPage;

// Build query
$where  = '1=1';
$params = [];
$types  = '';

if ($search !== '') {
    $where   .= ' AND (nama_produk LIKE ? OR deskripsi LIKE ? OR kategori LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = [$like, $like, $like];
    $types    = 'sss';
}

if ($kategori !== '') {
    $where   .= ' AND kategori = ?';
    $params[] = $kategori;
    $types   .= 's';
}

// Count total
$countSql  = "SELECT COUNT(*) FROM products WHERE $where";
$countStmt = $conn->prepare($countSql);
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalRows / $perPage);

// Fetch products
$sql  = "SELECT * FROM products WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$fetchParams = $params;
$fetchTypes  = $types . 'ii';
$fetchParams[] = $perPage;
$fetchParams[] = $offset;
$stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Kategori list
$katResult  = $conn->query("SELECT DISTINCT kategori FROM products WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori");
$kategoriList = $katResult ? $katResult->fetch_all(MYSQLI_ASSOC) : [];

include '../assets/partials/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1 class="page-hero__title">Koleksi Kami</h1>
    <p class="page-hero__sub">Temukan produk premium pilihan untuk melengkapi gaya hidup Anda</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <!-- Filters & Search -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:40px;padding-bottom:32px;border-bottom:1px solid var(--border)">
      <!-- Category filter -->
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <a href="index.php"
           class="btn btn--sm <?= $kategori === '' ? 'btn--primary' : 'btn--ghost' ?>">
          Semua
        </a>
        <?php foreach ($kategoriList as $kat): ?>
          <a href="index.php?kategori=<?= urlencode($kat['kategori']) ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
             class="btn btn--sm <?= $kategori === $kat['kategori'] ? 'btn--primary' : 'btn--ghost' ?>">
            <?= htmlspecialchars($kat['kategori']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Search -->
      <form class="search-bar" method="GET" action="index.php">
        <?php if ($kategori): ?>
          <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
        <?php endif; ?>
        <input type="text" name="q" id="searchInput"
               placeholder="Cari produk..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" aria-label="Cari">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </button>
      </form>
    </div>

    <!-- Result info -->
    <p style="font-size:0.8rem;color:var(--text-light);letter-spacing:0.04em;margin-bottom:28px">
      Menampilkan <strong style="color:var(--text-dark)"><?= $totalRows ?></strong> produk
      <?= $search ? 'untuk "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
      <?= $kategori ? ' dalam kategori <strong>' . htmlspecialchars($kategori) . '</strong>' : '' ?>
    </p>

    <!-- Products grid -->
    <?php if (empty($products)): ?>
      <div class="empty-state">
        <svg class="empty-state__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <div class="empty-state__title">Produk Tidak Ditemukan</div>
        <p class="empty-state__text">Coba kata kunci lain atau hapus filter yang aktif.</p>
        <a href="index.php" class="btn btn--outline">Lihat Semua Produk</a>
      </div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($products as $p): ?>
          <a href="detail.php?id=<?= $p['id'] ?>" class="product-card" data-name="<?= htmlspecialchars($p['nama_produk']) ?>">
            <div class="product-card__image">
              <?php if ($p['gambar'] && file_exists('../assets/images/' . $p['gambar'])): ?>
                <img src="../assets/images/<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
              <?php else: ?>
                <div class="product-card__image-placeholder">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  <span>Foto Produk</span>
                </div>
              <?php endif; ?>
              <?php if ($p['kategori']): ?>
                <span class="product-card__badge"><?= htmlspecialchars($p['kategori']) ?></span>
              <?php endif; ?>
              <div class="product-card__overlay">
                <span class="btn btn--outline" style="background:rgba(255,255,255,0.9);border-color:transparent;color:var(--text-dark)">
                  Lihat Detail
                </span>
              </div>
            </div>
            <div class="product-card__body">
              <?php if ($p['kategori']): ?>
                <div class="product-card__kategori"><?= htmlspecialchars($p['kategori']) ?></div>
              <?php endif; ?>
              <div class="product-card__name"><?= htmlspecialchars($p['nama_produk']) ?></div>
              <div class="product-card__price"><?= formatRupiah($p['harga']) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['halaman' => $page - 1])) ?>" aria-label="Sebelumnya">
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
            <a href="?<?= http_build_query(array_merge($_GET, ['halaman' => $page + 1])) ?>" aria-label="Berikutnya">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php include '../assets/partials/footer.php'; ?>
