<?php
// Partial: footer
// Usage: include 'path/to/partials/footer.php';
if (!isset($basePath)) $basePath = '/EcomersPakHikmat/';
?>

<footer class="footer" id="kontak">
  <div class="container">
    <div class="footer__grid">
      <div>
        <div class="footer__logo">ELÉG<span>ance</span></div>
        <p class="footer__desc">
          Koleksi fashion premium yang dipilih dengan cermat, menghadirkan keanggunan sejati dalam setiap detail.
        </p>
      </div>

      <div>
        <div class="footer__col-title">Navigasi</div>
        <ul class="footer__links">
          <li><a href="<?= $basePath ?>index.php">Beranda</a></li>
          <li><a href="<?= $basePath ?>produk/index.php">Koleksi</a></li>
          <li><a href="#">Tentang Kami</a></li>
          <li><a href="#">Blog</a></li>
        </ul>
      </div>

      <div>
        <div class="footer__col-title">Layanan</div>
        <ul class="footer__links">
          <li><a href="#">Panduan Ukuran</a></li>
          <li><a href="#">Pengembalian</a></li>
          <li><a href="#">Pengiriman</a></li>
          <li><a href="#">FAQ</a></li>
        </ul>
      </div>

      <div>
        <div class="footer__col-title">Hubungi</div>
        <ul class="footer__links">
          <li><a href="mailto:hello@elegance.id">hello@elegance.id</a></li>
          <li><a href="tel:+6221000000">+62 21 000-0000</a></li>
          <li><a href="#">Instagram</a></li>
          <li><a href="#">WhatsApp</a></li>
        </ul>
      </div>
    </div>

    <div class="footer__bottom">
      <span>&copy; <?= date('Y') ?> Elegance Shop. Hak cipta dilindungi.</span>
      <span>Dibuat dengan PHP Native + MySQL</span>
    </div>
  </div>
</footer>

<script src="<?= $basePath ?>assets/js/script.js"></script>
</body>
</html>
