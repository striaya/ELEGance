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

$error      = '';
$order_done = false;
$order_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty             = max(1, (int)($_POST['qty'] ?? 1));
    $metode_bayar    = sanitize($_POST['metode_bayar'] ?? '');
    $allowed_metode  = ['transfer_bca','transfer_mandiri','qris','cod'];

    if ($qty > $produk['stok']) {
        $error = 'Jumlah melebihi stok yang tersedia (' . $produk['stok'] . ').';
    } elseif (!in_array($metode_bayar, $allowed_metode)) {
        $error = 'Pilih metode pembayaran terlebih dahulu.';
    } else {
        $total   = $qty * $produk['harga'];
        $user_id = (int)$_SESSION['user_id'];

        // Cek apakah kolom metode_bayar ada, kalau tidak pakai kolom status saja
        $ins = $conn->prepare("INSERT INTO orders (user_id, product_id, qty, total, status) VALUES (?, ?, ?, ?, 'pending')");
        $ins->bind_param('iiid', $user_id, $produk_id, $qty, $total);

        $updStok = $conn->prepare("UPDATE products SET stok = stok - ? WHERE id = ? AND stok >= ?");
        $updStok->bind_param('iii', $qty, $produk_id, $qty);

        if ($ins->execute() && $updStok->execute()) {
            $order_id   = $conn->insert_id;
            $order_done = true;

            // Generate kode unik pesanan
            $kode_pesanan = 'ELG-' . date('ymd') . '-' . str_pad($order_id, 4, '0', STR_PAD_LEFT);

            $order_data = [
                'kode'         => $kode_pesanan,
                'order_id'     => $order_id,
                'produk'       => $produk['nama_produk'],
                'qty'          => $qty,
                'harga'        => $produk['harga'],
                'total'        => $total,
                'metode'       => $metode_bayar,
                'nama_user'    => $_SESSION['nama'],
                'tanggal'      => date('d M Y, H:i'),
            ];
        } else {
            $error = 'Gagal membuat pesanan. Silakan coba lagi.';
        }
    }
}

$metode_labels = [
    'transfer_bca'     => 'Transfer Bank BCA',
    'transfer_mandiri' => 'Transfer Bank Mandiri',
    'qris'             => 'QRIS',
    'cod'              => 'Bayar di Tempat (COD)',
];

$metode_info = [
    'transfer_bca'     => 'BCA — 1234567890 a.n. Elegance Shop',
    'transfer_mandiri' => 'Mandiri — 0987654321 a.n. Elegance Shop',
    'qris'             => 'Scan QRIS di kasir atau tunjukkan ke kurir',
    'cod'              => 'Bayar langsung saat barang tiba',
];

$pageTitle  = 'Konfirmasi Pesanan';
$activePage = 'produk';
$basePath   = '/EcomersPakHikmat/';
include '../assets/partials/header.php';
?>

<div style="padding-top:calc(var(--nav-h) + 60px);padding-bottom:80px;background:var(--off-white);min-height:100vh">
  <div class="container" style="max-width:700px">

    <?php if (!$order_done): ?>
    <!-- ===== FORM KONFIRMASI ===== -->

    <nav class="breadcrumb">
      <a href="/EcomersPakHikmat/">Beranda</a>
      <span class="breadcrumb__sep"></span>
      <a href="/EcomersPakHikmat/produk/index.php">Koleksi</a>
      <span class="breadcrumb__sep"></span>
      <a href="/EcomersPakHikmat/produk/detail.php?id=<?= $produk['id'] ?>"><?= htmlspecialchars($produk['nama_produk']) ?></a>
      <span class="breadcrumb__sep"></span>
      <span style="color:var(--text-dark)">Pesan</span>
    </nav>

    <div style="font-family:var(--font-serif);font-size:2rem;font-weight:300;margin-bottom:6px">Konfirmasi Pesanan</div>
    <p style="color:var(--text-light);font-size:0.85rem;margin-bottom:32px;letter-spacing:0.04em">Periksa detail dan pilih metode pembayaran</p>

    <?php if ($error): ?><div class="alert alert--danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Ringkasan Produk -->
    <div style="background:var(--white);border:1px solid var(--border);padding:24px;margin-bottom:20px;display:flex;gap:20px;align-items:center">
      <div style="width:80px;height:80px;background:var(--cream);border:1px solid var(--border);flex-shrink:0;overflow:hidden">
        <?php if ($produk['gambar'] && file_exists('../assets/images/' . $produk['gambar'])): ?>
          <img src="/EcomersPakHikmat/assets/images/<?= htmlspecialchars($produk['gambar']) ?>" style="width:100%;height:100%;object-fit:cover">
        <?php else: ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-light)">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
        <?php endif; ?>
      </div>
      <div style="flex:1">
        <?php if ($produk['kategori']): ?>
          <div style="font-size:0.68rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-light);margin-bottom:4px"><?= htmlspecialchars($produk['kategori']) ?></div>
        <?php endif; ?>
        <div style="font-family:var(--font-serif);font-size:1.25rem"><?= htmlspecialchars($produk['nama_produk']) ?></div>
        <div style="color:var(--accent);font-size:0.95rem;margin-top:4px"><?= formatRupiah($produk['harga']) ?> / pcs</div>
      </div>
      <div style="font-size:0.78rem;color:var(--text-light)">Stok: <?= $produk['stok'] ?></div>
    </div>

    <div class="form-card" style="border:1px solid var(--border)">
      <form method="POST" action="/EcomersPakHikmat/orders/tambah.php?produk_id=<?= $produk_id ?>" id="orderForm">

        <!-- Jumlah -->
        <div class="form-group">
          <label for="qty">Jumlah Pesanan</label>
          <input type="number" id="qty" name="qty"
                 value="<?= (int)($_POST['qty'] ?? 1) ?>"
                 min="1" max="<?= $produk['stok'] ?>" required
                 oninput="updateTotal(this.value)">
        </div>

        <!-- Ringkasan harga -->
        <div style="background:var(--off-white);border:1px solid var(--border);padding:18px 20px;margin-bottom:24px">
          <div style="display:flex;justify-content:space-between;font-size:0.83rem;color:var(--text-light);margin-bottom:8px">
            <span>Harga satuan</span>
            <span><?= formatRupiah($produk['harga']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.83rem;color:var(--text-light);margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)">
            <span>Jumlah</span>
            <span id="qtyDisplay">1</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-family:var(--font-serif);font-size:1.3rem;color:var(--accent)">
            <span>Total</span>
            <span id="totalDisplay"><?= formatRupiah($produk['harga']) ?></span>
          </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="form-group">
          <label style="margin-bottom:14px;display:block">Metode Pembayaran</label>

          <div style="display:grid;gap:10px">

            <!-- Transfer BCA -->
            <label class="metode-option" for="m_bca">
              <input type="radio" id="m_bca" name="metode_bayar" value="transfer_bca" <?= (($_POST['metode_bayar'] ?? '') === 'transfer_bca') ? 'checked' : '' ?>>
              <div class="metode-card">
                <div style="display:flex;align-items:center;gap:14px">
                  <div style="width:40px;height:40px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                  </div>
                  <div>
                    <div style="font-size:0.87rem;font-weight:500;color:var(--text-dark)">Transfer Bank BCA</div>
                    <div style="font-size:0.75rem;color:var(--text-light);margin-top:2px">No. Rek: 1234567890 a.n. Elegance Shop</div>
                  </div>
                </div>
              </div>
            </label>

            <!-- Transfer Mandiri -->
            <label class="metode-option" for="m_mandiri">
              <input type="radio" id="m_mandiri" name="metode_bayar" value="transfer_mandiri" <?= (($_POST['metode_bayar'] ?? '') === 'transfer_mandiri') ? 'checked' : '' ?>>
              <div class="metode-card">
                <div style="display:flex;align-items:center;gap:14px">
                  <div style="width:40px;height:40px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                  </div>
                  <div>
                    <div style="font-size:0.87rem;font-weight:500;color:var(--text-dark)">Transfer Bank Mandiri</div>
                    <div style="font-size:0.75rem;color:var(--text-light);margin-top:2px">No. Rek: 0987654321 a.n. Elegance Shop</div>
                  </div>
                </div>
              </div>
            </label>

            <!-- QRIS -->
            <label class="metode-option" for="m_qris">
              <input type="radio" id="m_qris" name="metode_bayar" value="qris" <?= (($_POST['metode_bayar'] ?? '') === 'qris') ? 'checked' : '' ?>>
              <div class="metode-card">
                <div style="display:flex;align-items:center;gap:14px">
                  <div style="width:40px;height:40px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/><rect x="19" y="14" width="2" height="2"/><rect x="14" y="19" width="2" height="2"/><rect x="18" y="18" width="3" height="3"/></svg>
                  </div>
                  <div>
                    <div style="font-size:0.87rem;font-weight:500;color:var(--text-dark)">QRIS</div>
                    <div style="font-size:0.75rem;color:var(--text-light);margin-top:2px">Scan QR Code untuk pembayaran instan</div>
                  </div>
                </div>
              </div>
            </label>

            <!-- COD -->
            <label class="metode-option" for="m_cod">
              <input type="radio" id="m_cod" name="metode_bayar" value="cod" <?= (($_POST['metode_bayar'] ?? '') === 'cod') ? 'checked' : '' ?>>
              <div class="metode-card">
                <div style="display:flex;align-items:center;gap:14px">
                  <div style="width:40px;height:40px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                  </div>
                  <div>
                    <div style="font-size:0.87rem;font-weight:500;color:var(--text-dark)">Bayar di Tempat (COD)</div>
                    <div style="font-size:0.75rem;color:var(--text-light);margin-top:2px">Bayar tunai saat barang diterima</div>
                  </div>
                </div>
              </div>
            </label>

          </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:8px">
          <button type="submit" class="btn btn--primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Konfirmasi Pesanan
          </button>
          <a href="/EcomersPakHikmat/produk/detail.php?id=<?= $produk_id ?>" class="btn btn--ghost">Batal</a>
        </div>

      </form>
    </div>

    <?php else: ?>
    <!-- ===== STRUK PESANAN ===== -->

    <div style="text-align:center;margin-bottom:36px">
      <div style="width:64px;height:64px;background:rgba(74,124,107,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4a7c6b" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h1 style="font-family:var(--font-serif);font-size:2rem;font-weight:300;margin-bottom:8px">Pesanan Berhasil</h1>
      <p style="color:var(--text-light);font-size:0.87rem;letter-spacing:0.04em">Terima kasih telah berbelanja di Elegance Shop</p>
    </div>

    <!-- Struk -->
    <div style="background:var(--white);border:1px solid var(--border)" id="struk">

      <!-- Header struk -->
      <div style="padding:28px 32px;border-bottom:1px solid var(--border);text-align:center;background:var(--off-white)">
        <div style="font-family:var(--font-serif);font-size:1.5rem;font-weight:500;letter-spacing:0.06em;color:var(--text-dark);margin-bottom:4px">ELÉGance</div>
        <div style="font-size:0.68rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--text-light)">Bukti Pesanan</div>
      </div>

      <!-- Kode pesanan -->
      <div style="padding:24px 32px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <div>
          <div style="font-size:0.68rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-light);margin-bottom:6px">Kode Pesanan</div>
          <div style="font-family:monospace;font-size:1.2rem;font-weight:600;color:var(--text-dark);letter-spacing:0.08em"><?= $order_data['kode'] ?></div>
        </div>
        <div style="text-align:right">
          <div style="font-size:0.68rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-light);margin-bottom:6px">Tanggal</div>
          <div style="font-size:0.87rem;color:var(--text-dark)"><?= $order_data['tanggal'] ?></div>
        </div>
      </div>

      <!-- Detail produk -->
      <div style="padding:24px 32px;border-bottom:1px solid var(--border)">
        <div style="font-size:0.68rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-light);margin-bottom:16px">Detail Pesanan</div>

        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px">
          <div style="flex:1">
            <div style="font-family:var(--font-serif);font-size:1.1rem;margin-bottom:4px"><?= htmlspecialchars($order_data['produk']) ?></div>
            <div style="font-size:0.8rem;color:var(--text-light)"><?= $order_data['qty'] ?> pcs x <?= formatRupiah($order_data['harga']) ?></div>
          </div>
          <div style="font-family:var(--font-serif);font-size:1.1rem;color:var(--accent)"><?= formatRupiah($order_data['total']) ?></div>
        </div>

        <div style="height:1px;background:var(--border);margin:16px 0"></div>

        <div style="display:flex;justify-content:space-between;font-size:0.83rem;color:var(--text-light);margin-bottom:6px">
          <span>Subtotal</span><span><?= formatRupiah($order_data['total']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.83rem;color:var(--text-light);margin-bottom:12px">
          <span>Ongkos kirim</span><span style="color:var(--success)">Gratis</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-family:var(--font-serif);font-size:1.4rem;color:var(--text-dark)">
          <span>Total Pembayaran</span>
          <span style="color:var(--accent)"><?= formatRupiah($order_data['total']) ?></span>
        </div>
      </div>

      <!-- Metode pembayaran -->
      <div style="padding:24px 32px;border-bottom:1px solid var(--border)">
        <div style="font-size:0.68rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-light);margin-bottom:14px">Metode Pembayaran</div>
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:36px;height:36px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <?php if (in_array($order_data['metode'], ['transfer_bca','transfer_mandiri'])): ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            <?php elseif ($order_data['metode'] === 'qris'): ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <?php else: ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <?php endif; ?>
          </div>
          <div>
            <div style="font-size:0.87rem;font-weight:500;color:var(--text-dark)"><?= $metode_labels[$order_data['metode']] ?></div>
            <div style="font-size:0.75rem;color:var(--text-light);margin-top:2px"><?= $metode_info[$order_data['metode']] ?></div>
          </div>
        </div>
      </div>

      <!-- Info pelanggan -->
      <div style="padding:24px 32px;border-bottom:1px solid var(--border)">
        <div style="font-size:0.68rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-light);margin-bottom:14px">Informasi Pelanggan</div>
        <div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:8px">
          <span style="color:var(--text-light)">Nama</span>
          <span style="color:var(--text-dark);font-weight:500"><?= htmlspecialchars($order_data['nama_user']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.85rem">
          <span style="color:var(--text-light)">Status</span>
          <span style="background:rgba(139,115,85,0.1);color:var(--accent);padding:2px 10px;font-size:0.72rem;letter-spacing:0.1em;text-transform:uppercase">Menunggu Pembayaran</span>
        </div>
      </div>

      <!-- Catatan -->
      <div style="padding:20px 32px;background:var(--off-white)">
        <div style="font-size:0.78rem;color:var(--text-light);line-height:1.7;text-align:center">
          Simpan kode pesanan Anda sebagai bukti transaksi.<br>
          Tim kami akan menghubungi Anda dalam 1x24 jam untuk konfirmasi pengiriman.
        </div>
      </div>

    </div>
    <!-- End Struk -->

    <!-- Tombol aksi -->
    <div style="display:flex;gap:12px;margin-top:24px;justify-content:center;flex-wrap:wrap">
      <button onclick="window.print()" class="btn btn--outline">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak Struk
      </button>
      <a href="/EcomersPakHikmat/" class="btn btn--primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Kembali ke Beranda
      </a>
      <a href="/EcomersPakHikmat/produk/index.php" class="btn btn--ghost">Belanja Lagi</a>
    </div>

    <?php endif; ?>

  </div>
</div>

<!-- CSS Metode Pembayaran -->
<style>
.metode-option {
  cursor: pointer;
  display: block;
}
.metode-option input[type="radio"] {
  display: none;
}
.metode-card {
  padding: 14px 18px;
  border: 1px solid var(--border);
  background: var(--white);
  transition: border-color 0.25s, background 0.25s;
}
.metode-option input[type="radio"]:checked + .metode-card {
  border-color: var(--accent);
  background: rgba(139,115,85,0.04);
}
.metode-option:hover .metode-card {
  border-color: var(--accent);
}

@media print {
  .navbar, .breadcrumb, #orderForm, .btn, footer { display: none !important; }
  body { background: white; }
  #struk { border: 1px solid #ccc; }
  .admin-layout { display: block; }
}
</style>

<script>
var harga = <?= $produk['harga'] ?>;
function updateTotal(qty) {
  qty = parseInt(qty) || 1;
  var total = harga * qty;
  document.getElementById('qtyDisplay').textContent = qty;
  document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
var qtyInput = document.getElementById('qty');
if (qtyInput) qtyInput.addEventListener('input', function(){ updateTotal(this.value); });
</script>

<?php include '../assets/partials/footer.php'; ?>