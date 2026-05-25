<?php

session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /EcomersPakHikmat/login/index.php');
        exit;
    }
}

function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: /EcomersPakHikmat/index.php');
        exit;
    }
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function flashMessage($type, $msg)
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
