# Elegance Shop — Website PHP Native + MySQL

Proyek website e-commerce dinamis menggunakan **PHP Native + MySQL** sesuai modul tugas.

---

## Struktur Folder

```
project/
├── admin/
│   ├── orders/          # Kelola pesanan
│   ├── partials/        # Sidebar admin
│   ├── produk/          # CRUD Produk (tambah, edit, hapus)
│   ├── user/            # CRUD Pengguna (tambah, edit, hapus)
│   └── index.php        # Dashboard Admin
├── assets/
│   ├── css/
│   │   └── style.css    # Stylesheet utama (Elegant White Theme)
│   ├── js/
│   │   └── script.js    # JavaScript utama
│   ├── images/          # Upload gambar produk
│   └── partials/        # Header & Footer bersama
├── config/
│   ├── database.php     # Konfigurasi koneksi MySQL
│   └── session.php      # Fungsi session & helper
├── database/
│   └── elegance_shop.sql  # File database (import ke phpMyAdmin)
├── login/
│   ├── index.php        # Halaman Login
│   ├── register.php     # Halaman Register
│   └── logout.php       # Logout handler
├── orders/
│   └── tambah.php       # Buat pesanan baru
├── produk/
│   ├── index.php        # Daftar Produk + Search + Filter
│   └── detail.php       # Detail Produk
└── index.php            # Halaman Utama (Home)
```

---

## Cara Instalasi

### 1. Persiapan
- Install **XAMPP** (https://www.apachefriends.org)
- Pastikan Apache & MySQL sudah aktif

### 2. Copy Project
Taruh folder `project/` ke dalam:
```
C:\xampp\htdocs\project\
```

### 3. Import Database
1. Buka browser → http://localhost/phpmyadmin
2. Klik **New** → buat database baru: `elegance_shop`
3. Klik tab **Import**
4. Pilih file: `project/database/elegance_shop.sql`
5. Klik **Go / Import**

### 4. Konfigurasi Database
Buka file `config/database.php`, sesuaikan jika perlu:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // kosong jika pakai XAMPP default
define('DB_NAME', 'elegance_shop');
```

### 5. Buka Website
- **Frontend:** http://localhost/project/
- **Admin:**    http://localhost/project/admin/

---

## Akun Demo

| Role  | Email                  | Password   |
|-------|------------------------|------------|
| Admin | admin@elegance.com     | password   |
| User  | budi@example.com       | password   |
| User  | siti@example.com       | password   |

> Password di database di-hash menggunakan `password_hash()` PHP.  
> Password plaintext untuk demo: **password**

---

## Fitur Lengkap

### Frontend (Publik)
- Halaman Beranda (Hero, Fitur, Produk Unggulan, Tentang)
- Halaman Koleksi Produk (Search, Filter Kategori, Pagination)
- Halaman Detail Produk (Info lengkap, Produk Terkait)
- Halaman Login & Register
- Konfirmasi Pesanan

### Admin Panel
- Dashboard (Statistik: produk, user, pesanan, pendapatan)
- CRUD Produk (tambah, edit, hapus + upload gambar)
- CRUD Pengguna (tambah, edit, hapus + atur role)
- Kelola Pesanan (lihat & ubah status: pending → paid → shipped → done)

### Teknis
- Session login dengan role (admin / user)
- Password di-hash dengan `password_hash()`
- Prepared statements (anti SQL Injection)
- Upload gambar produk dengan validasi tipe & ukuran
- Flash messages (notifikasi berhasil/gagal)
- Responsive design (mobile-friendly)
- Pagination pada semua tabel
- Search & filter produk

---

## Tabel Database

### `users`
| Kolom      | Tipe         |
|------------|--------------|
| id         | INT PK AI    |
| nama       | VARCHAR(100) |
| email      | VARCHAR(150) UNIQUE |
| password   | VARCHAR(255) |
| role       | ENUM(admin, user) |
| created_at | TIMESTAMP    |

### `products`
| Kolom       | Tipe         |
|-------------|--------------|
| id          | INT PK AI    |
| nama_produk | VARCHAR(200) |
| harga       | DECIMAL(15,0)|
| deskripsi   | TEXT         |
| gambar      | VARCHAR(255) |
| stok        | INT          |
| kategori    | VARCHAR(100) |
| created_at  | TIMESTAMP    |

### `orders`
| Kolom      | Tipe         |
|------------|--------------|
| id         | INT PK AI    |
| user_id    | INT FK       |
| product_id | INT FK       |
| qty        | INT          |
| total      | DECIMAL(15,0)|
| status     | ENUM(pending, paid, shipped, done) |
| created_at | TIMESTAMP    |

---

## Desain

- **Tema:** Luxury Minimal / Editorial White
- **Font:** Cormorant Garamond (serif display) + Jost (sans-serif body)
- **Warna:** Putih bersih, krem hangat, aksen cokelat emas
- **Ikon:** SVG inline (tanpa library eksternal)
- **Tidak ada emoji** di seluruh website

---

## Teknologi

- PHP Native (tanpa framework)
- MySQL + phpMyAdmin
- HTML5, CSS3 (CSS Variables, Grid, Flexbox)
- JavaScript Vanilla
- XAMPP (local server)

---

Dibuat untuk memenuhi tugas project:
**"Mengubah Mockup Desain Web Menjadi Website Dinamis Menggunakan PHP Native"**
Mata Pelajaran: Pemrograman Web | Kelas XI/XII RPL/PPLG
