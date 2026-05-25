<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$produk_id = isset($_GET['produk_id']) ? (int)$_GET['produk_id'] : 0;
if ($produk_id < 1) redirect('/EcomersPakHikmat/produk/index.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $produk_id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();
if (!$produk || $produk['stok'] < 1) {
    flashMessage('danger','Produk tidak tersedia.');
    redirect('/EcomersPakHikmat/produk/index.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    if ($qty > $produk['stok']) {
        $error = 'Jumlah melebihi stok yang tersedia (' . $produk['stok'] . ').';
    } else {
        $total    = $qty * $produk['harga'];
        $user_id  = (int)$_SESSION['user_id'];

        $ins = $conn->prepare("INSERT INTO orders (user_id, product_id, qty, total, status) VALUES (?, ?, ?, ?, 'pending')");
        $ins->bind_param('iiid', $user_id, $produk_id, $qty, $total);

        // Kurangi stok
        $updStok = $conn->prepare("UPDATE products SET stok = stok - ? WHERE id = ? AND stok >= ?");
        $updStok->bind_param('iii', $qty, $produk_id, $qty);

        if ($ins->execute() && $updStok->execute()) {
            flashMessage('success', 'Pesanan berhasil dibuat! Tim kami akan segera menghubungi Anda.');
            redirect('/EcomersPakHikmat/index.php');
        } else {
            $error = 'Gagal membuat pesanan. Silakan coba lagi.';
        }
    }
}

$pageTitle  = 'Pesan ' . htmlspecialchars($produk['nama_produk']);
$activePage = 'produk';
$basePath   = '/EcomersPakHikmat/';
include '../assets/partials/header.php';
?>

<div style="padding-top:calc(var(--nav-h) + 60px);padding-bottom:80px;background:var(--off-white);min-height:100vh">
  <div class="container" style="max-width:680px">

    <nav class="breadcrumb">
      <a href="/EcomersPakHikmat/">Beranda</a>
      <span class="breadcrumb__sep"></span>
      <a href="/EcomersPakHikmat/produk/index.php">Koleksi</a>
      <span class="breadcrumb__sep"></span>
      <a href="/EcomersPakHikmat/produk/detail.php?id=<?= $produk['id'] ?>"><?= htmlspecialchars($produk['nama_produk']) ?></a>
      <span class="breadcrumb__sep"></span>
      <span style="color:var(--text-dark)">Pesan</span>
    </nav>

    <div style="font-family:var(--font-serif);font-size:2rem;font-weight:300;margin-bottom:8px">Konfirmasi Pesanan</div>
    <p style="color:var(--text-light);font-size:0.85rem;margin-bottom:36px">Periksa detail pesanan Anda sebelum mengonfirmasi</p>

    <?php if ($error): ?><div class="alert alert--danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Product summary -->
    <div style="background:var(--white);border:1px solid var(--border);padding:24px;margin-bottom:24px;display:flex;gap:20px;align-items:center">
      <div style="width:80px;height:80px;background:var(--cream);border:1px solid var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;color:var(--text-light)">
        <?php if ($produk['gambar'] && file_exists('assets/images/' . $produk['gambar'])): ?>
          <img src="/assets/images/<?= htmlspecialchars($produk['gambar']) ?>" style="width:100%;height:100%;object-fit:cover">
        <?php else: ?>
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        <?php endif; ?>
      </div>
      <div style="flex:1">
        <?php if ($produk['kategori']): ?>
          <div style="font-size:0.68rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-light);margin-bottom:4px"><?= htmlspecialchars($produk['kategori']) ?></div>
        <?php endif; ?>
        <div style="font-family:var(--font-serif);font-size:1.3rem"><?= htmlspecialchars($produk['nama_produk']) ?></div>
        <div style="color:var(--accent);font-size:1rem;margin-top:4px"><?= formatRupiah($produk['harga']) ?> / pcs</div>
      </div>
      <div style="font-size:0.78rem;color:var(--text-light)">Stok: <?= $produk['stok'] ?></div>
    </div>

    <!-- Order form -->
    <div class="form-card" style="border:1px solid var(--border)">
      <form method="POST" action="/EcomersPakHikmat/orders/tambah.php?produk_id=<?= $produk_id ?>">
        <div class="form-group">
          <label for="qty">Jumlah Pesanan</label>
          <input type="number" id="qty" name="qty"
                 value="<?= (int)($_POST['qty'] ?? 1) ?>"
                 min="1" max="<?= $produk['stok'] ?>" required
                 oninput="updateTotal(this.value)">
        </div>

        <div style="background:var(--off-white);border:1px solid var(--border);padding:18px 20px;margin-bottom:24px">
          <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:var(--text-light);margin-bottom:8px">
            <span>Harga satuan</span>
            <span><?= formatRupiah($produk['harga']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:var(--text-light);margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)">
            <span>Jumlah</span>
            <span id="qtyDisplay">1</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-family:var(--font-serif);font-size:1.3rem;color:var(--accent)">
            <span>Total</span>
            <span id="totalDisplay"><?= formatRupiah($produk['harga']) ?></span>
          </div>
        </div>

        <div style="display:flex;gap:12px">
          <button type="submit" class="btn btn--primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Konfirmasi Pesanan
          </button>
          <a href="/EcomersPakHikmat/produk/detail.php?id=<?= $produk_id ?>" class="btn btn--ghost">Batal</a>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
var harga = <?= $produk['harga'] ?>;
function updateTotal(qty) {
  qty = parseInt(qty) || 1;
  var total = harga * qty;
  document.getElementById('qtyDisplay').textContent = qty;
  document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
document.getElementById('qty').addEventListener('input', function(){ updateTotal(this.value); });
</script>

<?php include '../assets/partials/footer.php'; ?>
