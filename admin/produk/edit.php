<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

requireAdmin();
$adminPage = 'produk';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) redirect('/admin/produk/index.php');

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();
if (!$produk) {
    flashMessage('danger', 'Produk tidak ditemukan.');
    redirect('/admin/produk/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = sanitize($_POST['nama_produk'] ?? '');
    $harga       = (int)($_POST['harga'] ?? 0);
    $deskripsi   = sanitize($_POST['deskripsi'] ?? '');
    $stok        = (int)($_POST['stok'] ?? 0);
    $kategori    = sanitize($_POST['kategori'] ?? '');

    if (empty($nama_produk) || $harga < 1) {
        $error = 'Nama produk dan harga wajib diisi.';
    } else {
        $gambar = $produk['gambar']; // keep existing

        if (!empty($_FILES['gambar']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($ext, $allowed)) {
                $error = 'Format gambar harus JPG, PNG, atau WebP.';
            } elseif ($_FILES['gambar']['size'] > $maxSize) {
                $error = 'Ukuran gambar maksimal 2MB.';
            } else {
                $filename = uniqid('prod_') . '.' . $ext;
                $destPath = '../../assets/images/' . $filename;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $destPath)) {
                    // Delete old image
                    if ($produk['gambar'] && file_exists('../../assets/images/' . $produk['gambar'])) {
                        unlink('../../assets/images/' . $produk['gambar']);
                    }
                    $gambar = $filename;
                } else {
                    $error = 'Gagal mengunggah gambar.';
                }
            }
        }

        if (!$error) {
            $updStmt = $conn->prepare("UPDATE products SET nama_produk=?, harga=?, deskripsi=?, gambar=?, stok=?, kategori=? WHERE id=?");
            $updStmt->bind_param('siisssi', $nama_produk, $harga, $deskripsi, $gambar, $stok, $kategori, $id);

            if ($updStmt->execute()) {
                flashMessage('success', "Produk '$nama_produk' berhasil diperbarui.");
                redirect('/admin/produk/index.php');
            } else {
                $error = 'Gagal memperbarui produk.';
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
  <title>Edit Produk — Admin</title>
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
        <span class="admin-topbar__title">Edit Produk</span>
      </div>
      <div class="admin-topbar__user">
        <div class="admin-topbar__avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
      </div>
    </header>

    <div class="admin-content">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">Edit Produk</h1>
          <p class="page-header__sub"><?= htmlspecialchars($produk['nama_produk']) ?></p>
        </div>
        <a href="index.php" class="btn btn--ghost btn--sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali
        </a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="form-page">
        <div class="form-card">
          <form method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data">

            <div class="form-group">
              <label for="nama_produk">Nama Produk <span style="color:var(--danger)">*</span></label>
              <input type="text" id="nama_produk" name="nama_produk"
                     value="<?= htmlspecialchars($_POST['nama_produk'] ?? $produk['nama_produk']) ?>"
                     required>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="harga">Harga (Rp) <span style="color:var(--danger)">*</span></label>
                <input type="number" id="harga" name="harga"
                       value="<?= htmlspecialchars($_POST['harga'] ?? $produk['harga']) ?>"
                       min="0" required>
              </div>
              <div class="form-group">
                <label for="stok">Stok</label>
                <input type="number" id="stok" name="stok"
                       value="<?= htmlspecialchars($_POST['stok'] ?? $produk['stok']) ?>"
                       min="0">
              </div>
            </div>

            <div class="form-group">
              <label for="kategori">Kategori</label>
              <input type="text" id="kategori" name="kategori"
                     value="<?= htmlspecialchars($_POST['kategori'] ?? $produk['kategori']) ?>">
            </div>

            <div class="form-group">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" name="deskripsi"><?= htmlspecialchars($_POST['deskripsi'] ?? $produk['deskripsi']) ?></textarea>
            </div>

            <div class="form-group">
              <label>Gambar Produk</label>
              <?php if ($produk['gambar'] && file_exists('../../assets/images/' . $produk['gambar'])): ?>
                <div style="margin-bottom:12px">
                  <img src="../../assets/images/<?= htmlspecialchars($produk['gambar']) ?>"
                       style="max-width:120px;border:1px solid var(--border)">
                  <p style="font-size:0.76rem;color:var(--text-light);margin-top:6px">Gambar saat ini</p>
                </div>
              <?php endif; ?>
              <div class="upload-area">
                <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp">
                <div class="upload-area__icon">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div class="upload-area__text">
                  <strong>Ganti gambar</strong> (opsional)<br>
                  <span>JPG, PNG, WebP — Maks. 2MB</span>
                </div>
              </div>
              <img id="image-preview" alt="Preview" style="margin-top:12px;max-width:160px;border:1px solid var(--border)">
            </div>

            <div style="display:flex;gap:12px;margin-top:8px">
              <button type="submit" class="btn btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Perbarui Produk
              </button>
              <a href="index.php" class="btn btn--ghost">Batal</a>
            </div>

          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="../../assets/js/script.js"></script>
</body>
</html>
