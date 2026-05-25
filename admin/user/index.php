<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

requireAdmin();
$adminPage = 'user';

// Handle delete
if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    if ($hapusId === (int)$_SESSION['user_id']) {
        flashMessage('danger', 'Tidak dapat menghapus akun yang sedang aktif.');
    } else {
        $del = $conn->prepare("DELETE FROM users WHERE id = ?");
        $del->bind_param('i', $hapusId);
        $del->execute();
        flashMessage('success', 'Pengguna berhasil dihapus.');
    }
    redirect('/admin/user/index.php');
}

$search  = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$perPage = 10;
$page    = isset($_GET['halaman']) ? max(1, (int)$_GET['halaman']) : 1;
$offset  = ($page - 1) * $perPage;

$where  = '1=1';
$params = [];
$types  = '';

if ($search !== '') {
    $where  .= ' AND (nama LIKE ? OR email LIKE ?)';
    $like    = '%' . $search . '%';
    $params  = [$like, $like];
    $types   = 'ss';
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $where");
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows  = $countStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalRows / $perPage);

$fp   = array_merge($params, [$perPage, $offset]);
$ft   = $types . 'ii';
$stmt = $conn->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param($ft, ...$fp);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pengguna — Admin</title>
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
        <span class="admin-topbar__title">Kelola Pengguna</span>
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
          <h1 class="page-header__title">Pengguna</h1>
          <p class="page-header__sub"><?= $totalRows ?> pengguna terdaftar</p>
        </div>
        <a href="tambah.php" class="btn btn--primary btn--sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Pengguna
        </a>
      </div>

      <div style="margin-bottom:20px">
        <form class="search-bar" method="GET" action="index.php">
          <input type="text" name="q" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
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
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th>Bergabung</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)): ?>
              <tr><td colspan="6" style="text-align:center;padding:48px;color:var(--text-light)">Tidak ada pengguna ditemukan.</td></tr>
            <?php else: ?>
              <?php foreach ($users as $i => $u): ?>
                <tr>
                  <td style="color:var(--text-light)"><?= $offset + $i + 1 ?></td>
                  <td>
                    <div style="display:flex;align-items:center;gap:10px">
                      <div style="width:32px;height:32px;border-radius:50%;background:var(--cream);display:flex;align-items:center;justify-content:center;font-family:var(--font-serif);font-size:0.9rem;color:var(--accent);border:1px solid var(--border)">
                        <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                      </div>
                      <span style="font-weight:500;color:var(--text-dark)"><?= htmlspecialchars($u['nama']) ?></span>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><span class="badge badge--<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                  <td style="font-size:0.8rem;color:var(--text-light)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                  <td>
                    <div class="data-table__actions">
                      <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn--ghost btn--sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                      </a>
                      <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                        <a href="index.php?hapus=<?= $u['id'] ?>" class="btn btn--danger btn--sm"
                           data-confirm="Hapus pengguna '<?= htmlspecialchars($u['nama']) ?>'?">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                          Hapus
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['halaman' => $page - 1])) ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <<?= $i === $page ? 'span class="active"' : 'a href="?' . http_build_query(array_merge($_GET, ['halaman' => $i])) . '"' ?>><?= $i ?></<?= $i === $page ? 'span' : 'a' ?>>
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
