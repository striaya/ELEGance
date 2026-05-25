<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

requireAdmin();
$adminPage = 'orders';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    $allowed = ['pending','paid','shipped','done'];
    if (in_array($status, $allowed)) {
        $upd = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $upd->bind_param('si', $status, $oid);
        $upd->execute();
        flashMessage('success','Status pesanan diperbarui.');
    }
    redirect('/EcomersPakHikmat/admin/orders/index.php');
}

$perPage = 10;
$page    = isset($_GET['halaman']) ? max(1,(int)$_GET['halaman']) : 1;
$offset  = ($page-1)*$perPage;

$totalRows  = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalPages = ceil($totalRows/$perPage);

$orders = $conn->query("
    SELECT o.*, u.nama AS user_nama, u.email AS user_email,
           p.nama_produk, p.harga AS harga_satuan
    FROM orders o
    JOIN users u ON u.id = o.user_id
    JOIN products p ON p.id = o.product_id
    ORDER BY o.created_at DESC
    LIMIT $perPage OFFSET $offset
")->fetch_all(MYSQLI_ASSOC);

$flash = getFlash();
$statusColors = [
    'pending' => 'background:rgba(139,115,85,0.1);color:var(--accent)',
    'paid'    => 'background:rgba(74,124,107,0.12);color:var(--success)',
    'shipped' => 'background:rgba(100,130,200,0.12);color:#5570b0',
    'done'    => 'background:rgba(50,50,50,0.08);color:var(--text-mid)',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pesanan — Admin</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<div class="admin-layout">
  <?php include '../partials/sidebar.php'; ?>
  <main class="admin-main">
    <header class="admin-topbar">
      <span class="admin-topbar__title">Kelola Pesanan</span>
      <div class="admin-topbar__user">
        <div class="admin-topbar__avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
      </div>
    </header>
    <div class="admin-content">
      <?php if ($flash): ?><div class="alert alert--<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-header__title">Pesanan</h1>
          <p class="page-header__sub"><?= $totalRows ?> total pesanan</p>
        </div>
      </div>

      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Pelanggan</th>
              <th>Produk</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Ubah Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--text-light)">Belum ada pesanan.</td></tr>
            <?php else: ?>
              <?php foreach ($orders as $i => $o): ?>
                <tr>
                  <td style="color:var(--text-light)"><?= $offset+$i+1 ?></td>
                  <td>
                    <div style="font-weight:500;color:var(--text-dark)"><?= htmlspecialchars($o['user_nama']) ?></div>
                    <div style="font-size:0.78rem;color:var(--text-light)"><?= htmlspecialchars($o['user_email']) ?></div>
                  </td>
                  <td><?= htmlspecialchars($o['nama_produk']) ?></td>
                  <td><?= $o['qty'] ?></td>
                  <td style="color:var(--accent);font-weight:500"><?= formatRupiah($o['total']) ?></td>
                  <td>
                    <span class="badge" style="<?= $statusColors[$o['status']] ?? '' ?>"><?= ucfirst($o['status']) ?></span>
                  </td>
                  <td style="font-size:0.78rem;color:var(--text-light)"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                  <td>
                    <form method="POST" action="index.php" style="display:flex;gap:6px;align-items:center">
                      <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                      <select name="status" style="padding:5px 8px;border:1px solid var(--border);font-size:0.78rem;background:var(--off-white);font-family:var(--font-sans);outline:none">
                        <?php foreach (['pending','paid','shipped','done'] as $s): ?>
                          <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" name="update_status" class="btn btn--accent btn--sm" style="padding:6px 12px">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?><a href="?halaman=<?= $page-1 ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg></a><?php endif; ?>
          <?php for ($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
            <?php if ($i===$page): ?><span class="active"><?= $i ?></span><?php else: ?><a href="?halaman=<?= $i ?>"><?= $i ?></a><?php endif; ?>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?><a href="?halaman=<?= $page+1 ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a><?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>
<script src="../../assets/js/script.js"></script>
</body>
</html>
