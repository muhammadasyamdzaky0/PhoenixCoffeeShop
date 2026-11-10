<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'phoenix_coffee';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) { die('Koneksi database gagal: ' . $conn->connect_error . '. Pastikan MySQL aktif dan database sudah diimpor.'); }
$conn->set_charset('utf8mb4');
if (session_status() === PHP_SESSION_NONE) session_start();
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function rupiah($value) { return 'Rp ' . number_format((float)$value, 0, ',', '.'); }
function is_logged_in() { return isset($_SESSION['user']); }
function require_login() { if (!is_logged_in()) { header('Location: login.php'); exit; } }
function require_role($roles) { require_login(); $roles = (array)$roles; if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) { http_response_code(403); exit('Akses ditolak. Akun Anda tidak memiliki izin membuka halaman ini.'); } }
function product_image($name, $category = 'Minuman') {
    $n = strtolower(trim((string)$name));
    $map = [
        'es kopi susu' => 'es-kopi-susu.jpg', 'kopi susu' => 'es-kopi-susu.jpg',
        'americano' => 'americano.jpg', 'cappuccino' => 'cappuccino.jpg', 'cappucino' => 'cappuccino.jpg',
        'matcha latte' => 'matcha-latte.jpg', 'choco latte' => 'choco-latte.jpg', 'cokelat' => 'choco-latte.jpg',
        'croissant' => 'croissant.jpg', 'roti bakar' => 'roti-bakar.jpg', 'pisang nugget' => 'pisang-nugget.jpg'
    ];
    $file = $map[$n] ?? (strtolower((string)$category) === 'makanan' ? 'croissant.jpg' : 'es-kopi-susu.jpg');
    return 'assets/images/' . $file;
}
?>
