
<?php
require 'config/koneksi.php';
require_role(['kasir','customer']);

$pageTitle = 'Dashboard';

// Statistik penjualan hari ini
$revenue = (float)($conn->query("
    SELECT COALESCE(SUM(total), 0) AS n
    FROM transactions
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['n'] ?? 0);

$txCount = (int)($conn->query("
    SELECT COUNT(*) AS n
    FROM transactions
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['n'] ?? 0);

// Statistik produk
$productCount = (int)($conn->query("
    SELECT COUNT(*) AS n FROM products
")->fetch_assoc()['n'] ?? 0);

$lowCount = (int)($conn->query("
    SELECT COUNT(*) AS n FROM products WHERE stock <= 5
")->fetch_assoc()['n'] ?? 0);

// Statistik pesanan online
$pendingOrders = (int)($conn->query("
    SELECT COUNT(*) AS n FROM orders
    WHERE status = 'Menunggu Konfirmasi'
")->fetch_assoc()['n'] ?? 0);

$processingOrders = (int)($conn->query("
    SELECT COUNT(*) AS n FROM orders
    WHERE status = 'Diproses'
")->fetch_assoc()['n'] ?? 0);

$completedOrders = (int)($conn->query("
    SELECT COUNT(*) AS n FROM orders
    WHERE status = 'Selesai'
")->fetch_assoc()['n'] ?? 0);

$onlineTotal = (float)($conn->query("
    SELECT COALESCE(SUM(total), 0) AS n
    FROM orders
    WHERE status IN ('Diproses', 'Selesai')
")->fetch_assoc()['n'] ?? 0);

// Lima produk terlaris dari transaksi kasir
$best = $conn->query("
    SELECT product_name, SUM(qty) AS qty
    FROM transaction_items
    GROUP BY product_id, product_name
    ORDER BY qty DESC
    LIMIT 5
");

// Data grafik penjualan tujuh hari terakhir
$daily = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily[$date] = 0;
}

$res = $conn->query("
    SELECT DATE(created_at) AS d, SUM(total) AS total
    FROM transactions
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
");

while ($r = $res->fetch_assoc()) {
    if (isset($daily[$r['d']])) {
        $daily[$r['d']] = (float)$r['total'];
    }
}

$max = max(array_merge([1], array_values($daily)));

require 'includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Dashboard</h1>
        <p>Selamat datang, Admin! Kelola bisnis coffee shop dengan mudah dan efisien.</p>
    </div>
    <span class="badge ok">● Sistem aktif</span>
</div>

<!-- Statistik penjualan dan produk -->
<div class="stats">

    <div class="stat">
        <div class="stat-icon">☕</div>
        <div>
            <div class="stat-label">Total Penjualan Hari Ini</div>
            <div class="stat-value"><?= rupiah($revenue) ?></div>
            <div class="stat-note">Penjualan hari ini</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon">▣</div>
        <div>
            <div class="stat-label">Jumlah Transaksi</div>
            <div class="stat-value"><?= $txCount ?></div>
            <div class="stat-note">Transaksi hari ini</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon">▦</div>
        <div>
            <div class="stat-label">Total Produk</div>
            <div class="stat-value"><?= $productCount ?></div>
            <div class="stat-note">Produk terdaftar</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon">⚠</div>
        <div>
            <div class="stat-label">Stok Menipis</div>
            <div class="stat-value"><?= $lowCount ?></div>
            <div class="stat-note"
                 style="color:<?= $lowCount ? '#c94f43' : '#16834b' ?>">
                <?= $lowCount ? 'Perlu diperhatikan' : 'Stok aman' ?>
            </div>
        </div>
    </div>

</div>

<!-- Statistik pesanan online -->
<div class="stats">

    <div class="stat">
        <div class="stat-icon">🛒</div>
        <div>
            <div class="stat-label">Pesanan Menunggu</div>
            <div class="stat-value"><?= $pendingOrders ?></div>
            <div class="stat-note">Menunggu konfirmasi</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon">☕</div>
        <div>
            <div class="stat-label">Pesanan Diproses</div>
            <div class="stat-value"><?= $processingOrders ?></div>
            <div class="stat-note">Sedang disiapkan</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon">✓</div>
        <div>
            <div class="stat-label">Pesanan Selesai</div>
            <div class="stat-value"><?= $completedOrders ?></div>
            <div class="stat-note">Pesanan selesai</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon">Rp</div>
        <div>
            <div class="stat-label">Total Pesanan Online</div>
            <div class="stat-value"><?= rupiah($onlineTotal) ?></div>
            <div class="stat-note">Pesanan diproses dan selesai</div>
        </div>
    </div>

</div>

<!-- Grafik dan produk terlaris -->
<div class="dashboard-grid">

    <section class="panel">
        <h3>Grafik Penjualan 7 Hari Terakhir</h3>

        <div class="chart-bars">
            <?php foreach ($daily as $date => $amount): ?>
                <div class="bar-item">
                    <div
                        class="bar"
                        title="<?= e(rupiah($amount)) ?>"
                        style="height:<?= max(4, ($amount / $max) * 78) ?>%"
                    ></div>
                    <span><?= date('d/m', strtotime($date)) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <p style="font-size:11px;color:var(--muted)">
            Grafik berdasarkan transaksi yang tersimpan.
        </p>
    </section>

    <section class="panel">
        <h3>Produk Terlaris</h3>

        <?php if ($best && $best->num_rows > 0): ?>
            <?php while ($p = $best->fetch_assoc()): ?>
                <div class="top-product">
                    <img
                        class="product-thumb-img"
                        src="<?= e(product_image($p['product_name'])) ?>"
                        alt="<?= e($p['product_name']) ?>"
                    >
                    <div class="product-name">
                        <?= e($p['product_name']) ?>
                    </div>
                    <div class="product-count">
                        <?= (int)$p['qty'] ?> terjual
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">Belum ada transaksi penjualan.</div>
        <?php endif; ?>
    </section>

</div>

<!-- Akses cepat pesanan online -->
<section class="panel" style="margin-bottom:20px">
    <h3>Manajemen Pesanan Online</h3>
    <p>
        Periksa pesanan pelanggan, konfirmasi pesanan,
        dan pantau status pengerjaan.
    </p>
    <a href="admin_pesanan.php" class="btn btn-primary">
        Kelola Pesanan Online →
    </a>
</section>

<!-- Banner dashboard -->
<div class="banner">
    <div>
        <h2>Good Coffee, Better Mood.</h2>
        <p>Rasa terbaik untuk setiap cerita.</p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
