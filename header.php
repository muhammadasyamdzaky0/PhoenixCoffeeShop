<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user']['role'] ?? 'guest';
$displayName = $_SESSION['user']['name'] ?? 'Pengunjung';
$roleLabel = ['kasir'=>'Kasir','customer'=>'Customer','guest'=>'Pengunjung'][$role] ?? 'Pengguna';

if ($role === 'customer') {
    $nav = [
        'customer.php' => ['☕', 'Katalog Menu'],
        'pesanan.php' => ['▣', 'Pesanan Saya']
    ];
} elseif ($role === 'kasir') {
    $nav = [
        'index.php' => ['⌂', 'Dashboard'],
        'menu.php' => ['▦', 'Manajemen Menu'],
        'persediaan.php' => ['▤', 'Manajemen Persediaan'],
        'transaksi.php' => ['▣', 'Transaksi Penjualan'],
        'riwayat_transaksi.php' => ['▤', 'Riwayat Transaksi'],
        'laporan.php' => ['▥', 'Laporan'],
        'admin_pesanan.php' => ['▣', 'Pesanan Online']
    ];
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($pageTitle) ?> | Phoenix Coffee</title><link rel="stylesheet" href="assets/css/style.css"></head><body>
<aside class="sidebar"><a class="brand" href="<?= $role==='customer'?'customer.php':($role==='kasir'?'transaksi.php':'index.php') ?>"><span class="brand-mark">♨</span><span>PHOENIX<small>COFFEE SHOP</small></span></a><nav><?php foreach($nav as $file=>$item): ?><a class="nav-link <?= $currentPage===$file?'active':'' ?>" href="<?= $file ?>"><span><?= $item[0] ?></span><?= $item[1] ?></a><?php endforeach; ?></nav><div class="sidebar-bottom"><span class="admin-dot"><?= e(strtoupper(substr($displayName,0,1))) ?></span><div><strong><?= e($displayName) ?></strong><small><?= e($roleLabel) ?></small><small><a href="logout.php">Keluar</a></small></div></div></aside>
<main class="main"><header class="topbar"><div class="mobile-brand">PHOENIX COFFEE</div><div class="top-user"><span class="avatar"><?= e(strtoupper(substr($displayName,0,1))) ?></span><span><strong><?= e($displayName) ?></strong><small><?= e($roleLabel) ?> · <a href="logout.php">Keluar</a></small></span></div></header><div class="content">
<?php if(isset($_SESSION['flash'])): ?><div class="alert success"><?= e($_SESSION['flash']); unset($_SESSION['flash']); ?></div><?php endif; ?>
