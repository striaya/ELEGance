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

// Cek apakah user sudah pernah beli dan belum review
$bisa_review   = false;
$sudah_review  = false;
$order_id_user = null;

if (isLoggedIn()) {
    $user_id = (int)$_SESSION['user_id'];

    // Cek sudah pernah beli
    $beliStmt = $conn->prepare("SELECT id FROM orders WHERE user_id = ? AND product_id = ? AND status IN ('paid','shipped','done') LIMIT 1");
    $beliStmt->bind_param('ii', $user_id, $id);
    $beliStmt->execute();
    $beli = $beliStmt->get_result()->fetch_assoc();

    if ($beli) {
        $order_id_user = $beli['id'];

        // Cek sudah pernah review
        $revCek = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $revCek->bind_param('ii', $user_id, $id);
        $revCek->execute();
        $sudah_review = (bool)$revCek->get_result()->fetch_assoc();
        $bisa_review  = !$sudah_review;
    }
}

// Handle submit review
$review_error   = '';
$review_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && $bisa_review) {
    $rating   = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $komentar = sanitize($_POST['komentar'] ?? '');
    $user_id  = (int)$_SESSION['user_id'];

    if (empty($komentar)) {
        $review_error = 'Komentar tidak boleh kosong.';
    } else {
        $insRev = $conn->prepare("INSERT INTO reviews (user_id, product_id, order_id, rating, komentar) VALUES (?, ?, ?, ?, ?)");
        $insRev->bind_param('iiiis', $user_id, $id, $order_id_user, $rating, $komentar);

        if ($insRev->execute()) {
            $review_success = 'Ulasan berhasil dikirim. Terima kasih!';
            $bisa_review    = false;
            $sudah_review   = true;
        } else {
            $review_error = 'Gagal menyimpan ulasan. Silakan coba lagi.';
        }
    }
}

// Ambil semua review produk ini
$revList = $conn->prepare("
    SELECT r.*, u.nama AS nama_user
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$revList->bind_param('i', $id);
$revList->execute();
$reviews = $revList->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung rata-rata rating
$avg_rating  = 0;
$total_rev   = count($reviews);
if ($total_rev > 0) {
    $avg_rating = array_sum(array_column($reviews, 'rating')) / $total_rev;
}

include '../assets/partials/header.php';
?>

<div class="product-detail">
  <div class="container">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <a href="/EcomersPakHikmat/">Beranda</a>
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
          <img src="/EcomersPakHikmat/assets/images/<?= htmlspecialchars($produk['gambar']) ?>"
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

        <!-- Rating summary -->
        <?php if ($total_rev > 0): ?>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
            <div style="display:flex;gap:3px">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <svg width="16" height="16" viewBox="0 0 24 24"
                     fill="<?= $s <= round($avg_rating) ? '#c9a96e' : 'none' ?>"
                     stroke="#c9a96e" stroke-width="2">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              <?php endfor; ?>
            </div>
            <span style="font-size:0.85rem;color:var(--text-mid)"><?= number_format($avg_rating, 1) ?> / 5</span>
            <span style="font-size:0.78rem;color:var(--text-light)">(<?= $total_rev ?> ulasan)</span>
          </div>
        <?php endif; ?>

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
              <a href="/EcomersPakHikmat/orders/tambah.php?produk_id=<?= $produk['id'] ?>" class="btn btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Pesan Sekarang
              </a>
            <?php else: ?>
              <a href="/EcomersPakHikmat/login/index.php" class="btn btn--primary">Masuk untuk Memesan</a>
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

    <!-- ===== ULASAN PRODUK ===== -->
    <div style="margin-top:72px;padding-top:56px;border-top:1px solid var(--border)">

      <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:16px">
        <div>
          <p style="font-size:0.72rem;font-weight:500;letter-spacing:0.22em;text-transform:uppercase;color:var(--accent);margin-bottom:10px;display:flex;align-items:center;gap:10px">
            <span style="display:inline-block;width:24px;height:1px;background:var(--accent)"></span>
            Ulasan Pembeli
          </p>
          <h2 style="font-family:var(--font-serif);font-size:2rem;font-weight:300">
            Yang Mereka Katakan
          </h2>
        </div>

        <?php if ($total_rev > 0): ?>
          <div style="text-align:center;padding:20px 28px;border:1px solid var(--border);background:var(--off-white)">
            <div style="font-family:var(--font-serif);font-size:2.5rem;color:var(--accent);line-height:1"><?= number_format($avg_rating, 1) ?></div>
            <div style="display:flex;gap:3px;justify-content:center;margin:8px 0">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <svg width="14" height="14" viewBox="0 0 24 24"
                     fill="<?= $s <= round($avg_rating) ? '#c9a96e' : 'none' ?>"
                     stroke="#c9a96e" stroke-width="2">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              <?php endfor; ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-light);letter-spacing:0.08em"><?= $total_rev ?> ulasan</div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Form Tulis Ulasan -->
      <?php if ($bisa_review): ?>
        <div style="background:var(--off-white);border:1px solid var(--border);padding:32px;margin-bottom:40px">
          <h3 style="font-family:var(--font-serif);font-size:1.3rem;font-weight:400;margin-bottom:6px">Tulis Ulasan Anda</h3>
          <p style="font-size:0.8rem;color:var(--text-light);margin-bottom:24px;letter-spacing:0.04em">Bagikan pengalaman Anda dengan produk ini</p>

          <?php if ($review_error): ?><div class="alert alert--danger"><?= htmlspecialchars($review_error) ?></div><?php endif; ?>

          <form method="POST" action="detail.php?id=<?= $id ?>">
            <!-- Rating bintang interaktif -->
            <div class="form-group">
              <label style="display:block;font-size:0.72rem;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-mid);margin-bottom:12px">Rating</label>
              <div class="star-rating" id="starRating">
                <?php for ($s = 5; $s >= 1; $s--): ?>
                  <input type="radio" id="star<?= $s ?>" name="rating" value="<?= $s ?>" <?= $s === 5 ? 'checked' : '' ?>>
                  <label for="star<?= $s ?>" title="<?= $s ?> bintang">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                  </label>
                <?php endfor; ?>
              </div>
            </div>

            <div class="form-group">
              <label for="komentar">Komentar</label>
              <textarea id="komentar" name="komentar" placeholder="Ceritakan pengalaman Anda menggunakan produk ini..." style="min-height:100px"><?= htmlspecialchars($_POST['komentar'] ?? '') ?></textarea>
            </div>

            <button type="submit" name="submit_review" class="btn btn--primary">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
              Kirim Ulasan
            </button>
          </form>
        </div>

      <?php elseif ($review_success): ?>
        <div class="alert alert--success" style="margin-bottom:32px"><?= htmlspecialchars($review_success) ?></div>

      <?php elseif ($sudah_review): ?>
        <div style="background:var(--off-white);border:1px solid var(--border);padding:20px 24px;margin-bottom:40px;display:flex;align-items:center;gap:14px">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4a7c6b" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <span style="font-size:0.85rem;color:var(--text-mid)">Anda sudah memberikan ulasan untuk produk ini.</span>
        </div>

      <?php elseif (isLoggedIn()): ?>
        <div style="background:var(--off-white);border:1px solid var(--border);padding:20px 24px;margin-bottom:40px;display:flex;align-items:center;gap:14px">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span style="font-size:0.85rem;color:var(--text-mid)">Hanya pembeli yang sudah menerima produk yang dapat memberikan ulasan.</span>
        </div>

      <?php else: ?>
        <div style="background:var(--off-white);border:1px solid var(--border);padding:20px 24px;margin-bottom:40px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <span style="font-size:0.85rem;color:var(--text-mid)">Masuk untuk menulis ulasan produk ini.</span>
          <a href="/EcomersPakHikmat/login/index.php" class="btn btn--outline btn--sm">Masuk</a>
        </div>
      <?php endif; ?>

      <!-- Daftar Ulasan -->
      <?php if (empty($reviews)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--text-light)">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin:0 auto 16px;display:block;opacity:0.4"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          <div style="font-family:var(--font-serif);font-size:1.3rem;color:var(--text-mid);margin-bottom:6px">Belum Ada Ulasan</div>
          <p style="font-size:0.83rem">Jadilah yang pertama mengulas produk ini.</p>
        </div>
      <?php else: ?>
        <div style="display:grid;gap:1px;background:var(--border);border:1px solid var(--border)">
          <?php foreach ($reviews as $rev): ?>
            <div style="background:var(--white);padding:28px 32px">
              <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:10px">
                <div style="display:flex;align-items:center;gap:12px">
                  <div style="width:38px;height:38px;border-radius:50%;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-family:var(--font-serif);font-size:1rem;color:var(--accent);flex-shrink:0">
                    <?= strtoupper(substr($rev['nama_user'], 0, 1)) ?>
                  </div>
                  <div>
                    <div style="font-weight:500;font-size:0.9rem;color:var(--text-dark)"><?= htmlspecialchars($rev['nama_user']) ?></div>
                    <div style="font-size:0.72rem;color:var(--text-light);margin-top:2px"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
                  </div>
                </div>
                <!-- Bintang -->
                <div style="display:flex;gap:3px">
                  <?php for ($s = 1; $s <= 5; $s++): ?>
                    <svg width="14" height="14" viewBox="0 0 24 24"
                         fill="<?= $s <= $rev['rating'] ? '#c9a96e' : 'none' ?>"
                         stroke="#c9a96e" stroke-width="2">
                      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                  <?php endfor; ?>
                </div>
              </div>
              <p style="font-size:0.88rem;color:var(--text-mid);line-height:1.75"><?= nl2br(htmlspecialchars($rev['komentar'])) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
    <!-- End Ulasan -->

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
      <div style="margin-top:80px">
        <div style="margin-bottom:32px">
          <p style="font-size:0.72rem;font-weight:500;letter-spacing:0.22em;text-transform:uppercase;color:var(--accent);margin-bottom:10px;display:flex;align-items:center;gap:10px">
            <span style="display:inline-block;width:24px;height:1px;background:var(--accent)"></span>
            Mungkin Anda Suka
          </p>
          <h2 style="font-family:var(--font-serif);font-size:2rem;font-weight:300">Produk Terkait</h2>
        </div>
        <div class="products-grid" style="grid-template-columns:repeat(4,1fr)">
          <?php foreach ($related as $r): ?>
            <a href="detail.php?id=<?= $r['id'] ?>" class="product-card">
              <div class="product-card__image">
                <?php if ($r['gambar'] && file_exists('../assets/images/' . $r['gambar'])): ?>
                  <img src="/EcomersPakHikmat/assets/images/<?= htmlspecialchars($r['gambar']) ?>" alt="<?= htmlspecialchars($r['nama_produk']) ?>">
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

<!-- CSS Star Rating -->
<style>
.star-rating {
  display: flex;
  flex-direction: row-reverse;
  gap: 4px;
  width: fit-content;
}
.star-rating input { display: none; }
.star-rating label {
  cursor: pointer;
  width: 32px;
  height: 32px;
  color: #ddd;
  transition: color 0.15s;
}
.star-rating label svg { width: 100%; height: 100%; }
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
  color: #c9a96e;
}
.star-rating input:checked ~ label svg,
.star-rating label:hover svg,
.star-rating label:hover ~ label svg {
  fill: #c9a96e;
  stroke: #c9a96e;
}
</style>

<?php include '../assets/partials/footer.php'; ?>