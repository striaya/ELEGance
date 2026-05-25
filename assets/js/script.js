// ============================================
// script.js — Elegance Shop
// ============================================

document.addEventListener('DOMContentLoaded', function () {

  // ---- Navbar scroll effect ----
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 20) {
        navbar.style.boxShadow = '0 2px 24px rgba(26,25,23,0.10)';
      } else {
        navbar.style.boxShadow = 'none';
      }
    });
  }

  // ---- Mobile sidebar toggle ----
  const menuToggle = document.getElementById('menuToggle');
  const sidebar    = document.querySelector('.sidebar');

  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  // ---- Auto-hide flash messages ----
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity    = '0';
      setTimeout(function () { alert.remove(); }, 500);
    }, 4000);
  });

  // ---- Image upload preview ----
  const uploadInput   = document.getElementById('gambar');
  const imagePreview  = document.getElementById('image-preview');

  if (uploadInput && imagePreview) {
    uploadInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          imagePreview.src     = e.target.result;
          imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // ---- Delete confirmation ----
  const deleteBtns = document.querySelectorAll('[data-confirm]');
  deleteBtns.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      const msg = this.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // ---- Animate elements on scroll ----
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.style.opacity   = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  document.querySelectorAll('.product-card, .stat-card, .feature-item').forEach(function (el) {
    el.style.opacity   = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
  });

  // ---- Search: filter products on keyup ----
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q     = this.value.toLowerCase().trim();
      const cards = document.querySelectorAll('.product-card[data-name]');
      cards.forEach(function (card) {
        const name = card.getAttribute('data-name').toLowerCase();
        card.style.display = name.includes(q) ? '' : 'none';
      });
    });
  }

  // ---- Active nav link ----
  const currentPath = window.location.pathname;
  document.querySelectorAll('.navbar__links a, .sidebar__nav a').forEach(function (link) {
    if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').replace('../', ''))) {
      link.classList.add('active');
    }
  });

});
