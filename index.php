<?php
require_once 'config/session.php';
require_once 'config/database.php';
$flash = getFlash();

$pageTitle  = 'Beranda';
$activePage = 'home';
$basePath   = '/EcomersPakHikmat/';

$sql      = "SELECT * FROM products ORDER BY created_at DESC LIMIT 6";
$result   = $conn->query($sql);
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

include 'assets/partials/header.php';
?>

<?php if ($flash): ?>
  <div style="
    position:fixed;
    top:90px;
    right:28px;
    z-index:9999;
    background:var(--white);
    border:1px solid var(--border);
    border-left:3px solid var(--success);
    padding:18px 24px;
    box-shadow:var(--shadow-lg);
    max-width:360px;
    font-family:var(--font-sans);
    animation: slideIn 0.4s ease;
  " id="toastNotif">
    <div style="display:flex;align-items:flex-start;gap:14px">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4a7c6b" stroke-width="2" style="flex-shrink:0;margin-top:1px">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      <div>
        <div style="font-size:0.78rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:#4a7c6b;margin-bottom:4px">Berhasil</div>
        <div style="font-size:0.88rem;color:var(--text-dark);line-height:1.5"><?= htmlspecialchars($flash['msg']) ?></div>
      </div>
      <button onclick="document.getElementById('toastNotif').remove()" style="background:none;border:none;cursor:pointer;color:var(--text-light);padding:0;margin-left:8px;font-size:1rem;line-height:1">&times;</button>
    </div>
  </div>
  <style>
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(20px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
  </style>
  <script>
    setTimeout(function() {
      var el = document.getElementById('toastNotif');
      if (el) {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(function() {
          el.remove();
        }, 500);
      }
    }, 4000);
  </script>
<?php endif; ?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="container">
    <div class="hero__content">
      <p class="hero__label">Koleksi Terbaru 2025</p>
      <h1 class="hero__title">
        Keanggunan<br>
        yang <em>Abadi</em>
      </h1>
      <p class="hero__desc">
        Temukan koleksi fashion premium kami — setiap helai kain dipilih dengan teliti, setiap detail dirancang untuk menghadirkan keanggunan sejati.
      </p>
      <div class="hero__cta">
        <a href="produk/index.php" class="btn btn--primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          Jelajahi Koleksi
        </a>
        <a href="#tentang" class="btn btn--ghost">Tentang Kami</a>
      </div>
    </div>

    <div class="hero__image">
      <img src="/EcomersPakHikmat/assets/images/shopelegane.jpg"
        alt="Hero"
        class="hero__image-main">

      <div class="hero__image-badge">
        <strong><?= count($products) ?>+</strong>
        <span>Produk Premium</span>
      </div>
    </div>
  </div>
</section>

<!-- ============ FEATURES ============ -->
<section class="section--sm" style="background:var(--white)">
  <div class="container">
    <div class="features-grid">
      <div class="feature-item">
        <div class="feature-item__icon">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
          </svg>
        </div>
        <div class="feature-item__title">Kualitas Terjamin</div>
        <p class="feature-item__text">Setiap produk melewati seleksi ketat untuk memastikan standar premium yang konsisten.</p>
      </div>
      <div class="feature-item">
        <div class="feature-item__icon">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="1" y="3" width="15" height="13" />
            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
            <circle cx="5.5" cy="18.5" r="2.5" />
            <circle cx="18.5" cy="18.5" r="2.5" />
          </svg>
        </div>
        <div class="feature-item__title">Pengiriman Aman</div>
        <p class="feature-item__text">Dikemas dengan hati-hati dan dikirim ke seluruh Indonesia dalam waktu 2–5 hari kerja.</p>
      </div>
      <div class="feature-item">
        <div class="feature-item__icon">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
          </svg>
        </div>
        <div class="feature-item__title">Dukungan Penuh</div>
        <p class="feature-item__text">Tim kami siap membantu Anda setiap hari mulai pukul 08.00 hingga 20.00 WIB.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ PRODUCTS ============ -->
<section class="section" style="background:var(--off-white)">
  <div class="container">
    <div class="section-header">
      <p class="section-header__label">Pilihan Kami</p>
      <h2 class="section-header__title">Koleksi Unggulan</h2>
      <p class="section-header__subtitle">Rangkaian produk terpilih yang mencerminkan keindahan dan ketelitian dalam setiap detail.</p>
    </div>

    <?php if (empty($products)): ?>
      <div class="empty-state">
        <svg class="empty-state__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
          <line x1="3" y1="6" x2="21" y2="6" />
          <path d="M16 10a4 4 0 01-8 0" />
        </svg>
        <div class="empty-state__title">Belum Ada Produk</div>
        <p class="empty-state__text">Produk akan segera hadir. Silakan kunjungi kembali.</p>
      </div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($products as $p): ?>
          <a href="produk/detail.php?id=<?= $p['id'] ?>" class="product-card" data-name="<?= htmlspecialchars($p['nama_produk']) ?>">
            <div class="product-card__image">
              <?php if ($p['gambar'] && file_exists('assets/images/' . $p['gambar'])): ?>
                <img src="assets/images/<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
              <?php else: ?>
                <div class="product-card__image-placeholder">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <polyline points="21 15 16 10 5 21" />
                  </svg>
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

      <div class="text-center mt-8">
        <a href="produk/index.php" class="btn btn--outline">
          Lihat Semua Koleksi
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============ ABOUT ============ -->
<section class="section" id="tentang" style="background:var(--white)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center">
      <div>
        <img src="/EcomersPakHikmat/assets/images/elegance.jpeg"
          alt="Tentang Kami"
          style="width:100%;height:100%;object-fit:cover">
      </div>
      <div>
        <p class="hero__label">Tentang Kami</p>
        <h2 style="font-family:var(--font-serif);font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:300;margin-bottom:20px;line-height:1.2">
          Dedikasi pada<br><em style="font-style:italic;color:var(--accent)">Keindahan</em>
        </h2>
        <p style="font-size:0.92rem;color:var(--text-mid);line-height:1.85;margin-bottom:16px">
          Elegance Shop lahir dari keyakinan bahwa fashion bukan sekadar pakaian, melainkan ekspresi karakter dan nilai diri. Sejak 2020, kami hadir menghadirkan koleksi yang merayakan keindahan dalam kesederhanaan.
        </p>
        <p style="font-size:0.92rem;color:var(--text-mid);line-height:1.85;margin-bottom:36px">
          Setiap produk kami dipilih dari bahan-bahan terbaik, dikerjakan oleh pengrajin berpengalaman, dan dihadirkan untuk Anda yang menghargai kualitas sejati.
        </p>
        <a href="produk/index.php" class="btn btn--primary">Jelajahi Koleksi</a>
      </div>
    </div>
  </div>
</section>

<?php include 'assets/partials/footer.php'; ?>