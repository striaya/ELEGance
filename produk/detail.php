<?php
require_once '../config/session.php';
require_once '../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if (!$produk) {
    header('Location: index.php');
    exit;
}

$pageTitle  = htmlspecialchars($produk['nama_produk']);
$activePage = 'produk';
$basePath   = '/EcomersPakHikmat/';

// Related products
$relStmt = $conn->prepare("SELECT * FROM products WHERE kategori = ? AND id != ? LIMIT 4");
$relStmt->bind_param('si', $produk['kategori'], $id);
$relStmt->execute();
$related = $relStmt->get_result()->fetch_all(MYSQLI_ASSOC);

include '../assets/partials/header.php';
?>

<div class="product-detail">
  <div class="container">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <a href="/">Beranda</a>
      <span class="breadcrumb__sep"></span>
      <a href="index.php">Koleksi</a>
      <?php if ($produk['kategori']): ?>
        <span class="breadcrumb__sep"></span>
        <a href="index.php?kategori=<?= urlencode($produk['kategori']) ?>"><?= htmlspecialchars($produk['kategori']) ?></a>
      <?php endif; ?>
      <span class="breadcrumb__sep"></span>
      <span style="color:var(--text-dark)"><?= htmlspecialchars($produk['nama_produk']) ?></span>
    </nav>

    <div class="product-detail__grid">
      <!-- Image -->
      <div class="product-detail__image">
        <?php if ($produk['gambar'] && file_exists('../assets/images/' . $produk['gambar'])): ?>
          <img src="../assets/images/<?= htmlspecialchars($produk['gambar']) ?>"
               alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
        <?php else: ?>
          <div style="width:100%;height:100%;min-height:480px;background:var(--cream);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px;color:var(--text-light)">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span style="font-size:0.8rem;letter-spacing:0.1em">Foto Produk</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div>
        <?php if ($produk['kategori']): ?>
          <div class="product-detail__label"><?= htmlspecialchars($produk['kategori']) ?></div>
        <?php endif; ?>

        <h1 class="product-detail__name"><?= htmlspecialchars($produk['nama_produk']) ?></h1>
        <div class="product-detail__price"><?= formatRupiah($produk['harga']) ?></div>

        <div class="product-detail__divider"></div>

        <?php if ($produk['deskripsi']): ?>
          <p class="product-detail__desc"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
        <?php endif; ?>

        <div class="product-detail__meta">
          <div class="product-detail__meta-row">
            <span>Stok</span>
            <span><?= $produk['stok'] > 0 ? $produk['stok'] . ' tersedia' : '<span style="color:var(--danger)">Habis</span>' ?></span>
          </div>
          <?php if ($produk['kategori']): ?>
          <div class="product-detail__meta-row">
            <span>Kategori</span>
            <span><?= htmlspecialchars($produk['kategori']) ?></span>
          </div>
          <?php endif; ?>
          <div class="product-detail__meta-row">
            <span>Kode Produk</span>
            <span style="font-family:monospace;font-size:0.82rem">ELG-<?= str_pad($produk['id'], 4, '0', STR_PAD_LEFT) ?></span>
          </div>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <?php if ($produk['stok'] > 0): ?>
            <?php if (isLoggedIn()): ?>
              <a href="../orders/tambah.php?produk_id=<?= $produk['id'] ?>" class="btn btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Pesan Sekarang
              </a>
            <?php else: ?>
              <a href="../login/index.php" class="btn btn--primary">
                Masuk untuk Memesan
              </a>
            <?php endif; ?>
          <?php else: ?>
            <button class="btn btn--ghost" disabled style="opacity:0.5;cursor:not-allowed">Stok Habis</button>
          <?php endif; ?>
          <a href="index.php" class="btn btn--ghost">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
          </a>
        </div>
      </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
      <div style="margin-top:80px">
        <div class="section-header" style="text-align:left;margin-bottom:32px">
          <p class="section-header__label">Mungkin Anda Suka</p>
          <h2 class="section-header__title" style="font-size:2rem">Produk Terkait</h2>
        </div>
        <div class="products-grid" style="grid-template-columns:repeat(4,1fr)">
          <?php foreach ($related as $r): ?>
            <a href="detail.php?id=<?= $r['id'] ?>" class="product-card">
              <div class="product-card__image">
                <?php if ($r['gambar'] && file_exists('../assets/images/' . $r['gambar'])): ?>
                  <img src="../assets/images/<?= htmlspecialchars($r['gambar']) ?>" alt="<?= htmlspecialchars($r['nama_produk']) ?>">
                <?php else: ?>
                  <div class="product-card__image-placeholder">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  </div>
                <?php endif; ?>
              </div>
              <div class="product-card__body">
                <div class="product-card__name"><?= htmlspecialchars($r['nama_produk']) ?></div>
                <div class="product-card__price"><?= formatRupiah($r['harga']) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include '../assets/partials/footer.php'; ?>
