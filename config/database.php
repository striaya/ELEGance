<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elegance_shop');
define('BASE_URL', '/EcomersPakHikmat/');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:serif;text-align:center;padding:60px;color:#c0392b;">
        <h2>Koneksi Database Gagal</h2>
        <p>' . $conn->connect_error . '</p>
    </div>');
}

$conn->set_charset('utf8mb4');
