<?php require 'config/koneksi.php'; 
require_role(['kasir']);
$pageTitle='Laporan';
$start=$_GET['start']??date('Y-m-01');
$end=$_GET['end']??date('Y-m-d');
$st=$conn->prepare('SELECT COUNT(*) count_tx,COALESCE(SUM(total),0) revenue,COALESCE(AVG(total),0) average FROM transactions WHERE DATE(created_at) BETWEEN ? AND ?');
$st->bind_param('ss',$start,$end);
$st->execute();$summary=$st->get_result()->fetch_assoc();
$st=$conn->prepare('SELECT * FROM transactions WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC');
$st->bind_param('ss',$start,$end);
$st->execute();
$transactions=$st->get_result();
$best=$conn->query('SELECT product_name,SUM(qty) qty,SUM(subtotal) revenue FROM transaction_items GROUP BY product_id,product_name ORDER BY qty DESC LIMIT 10');
$stock=$conn->query('SELECT * FROM products ORDER BY stock ASC');require 'includes/header.php'; ?>
<div class="page-heading"><div>
    <h1>Laporan</h1>
    <p>Lihat dan rekap laporan operasional Phoenix Coffee Shop.</p>
</div><button class="btn" onclick="window.print()">⇩ Cetak / Simpan PDF</button></div>
<form class="panel" method="get">
    <div class="form-grid"><div class="field">
        <label>Dari Tanggal</label>
        <input type="date" name="start" value="<?= e($start) ?>" required></div><div class="field">
            <label>Sampai Tanggal</label>
            <input type="date" name="end" value="<?= e($end) ?>" required></div></div><div class="form-actions"><button class="btn">Tampilkan Laporan</button></div></form>
<div class="stats" style="margin-top:18px">
    <div class="stat">
        <div class="stat-icon">☕</div>
        <div><div class="stat-label">Total Penjualan</div>
        <div class="stat-value" style="font-size:18px"><?= rupiah($summary['revenue']) ?></div></div></div><div class="stat"><div class="stat-icon">▣</div><div><div class="stat-label">Total Transaksi</div><div class="stat-value"><?= (int)$summary['count_tx'] ?></div></div></div><div class="stat"><div class="stat-icon">↗</div><div><div class="stat-label">Rata-rata Transaksi</div><div class="stat-value" style="font-size:18px"><?= rupiah($summary['average']) ?></div></div></div><div class="stat"><div class="stat-icon">▦</div><div><div class="stat-label">Periode</div><div class="stat-value" style="font-size:14px"><?= date('d/m/y',strtotime($start)) ?> – <?= date('d/m/y',strtotime($end)) ?></div></div></div></div>
<section class="panel"><h3>Rekap Transaksi</h3><div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Tanggal</th><th>Total</th><th>Metode Pembayaran</th></tr></thead><tbody><?php if($transactions->num_rows):while($t=$transactions->fetch_assoc()): ?><tr><td><?= e($t['invoice']) ?></td><td><?= e($t['created_at']) ?></td><td><?= rupiah($t['total']) ?></td><td><?= e($t['payment_method']) ?></td></tr><?php endwhile;else: ?><tr><td colspan="4" class="empty">Tidak ada transaksi pada periode ini.</td></tr><?php endif; ?></tbody></table></div></section>
<div class="dashboard-grid"><section class="panel"><h3>Produk Terlaris</h3><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Terjual</th><th>Total Penjualan</th></tr></thead><tbody><?php if($best->num_rows):while($p=$best->fetch_assoc()): ?><tr><td><?= e($p['product_name']) ?></td><td><?= (int)$p['qty'] ?></td><td><?= rupiah($p['revenue']) ?></td></tr><?php endwhile;else: ?><tr><td colspan="3" class="empty">Belum ada data penjualan.</td></tr><?php endif; ?></tbody></table></div></section><section class="panel"><h3>Laporan Stok</h3><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Stok</th><th>Status</th></tr></thead><tbody><?php while($p=$stock->fetch_assoc()): ?><tr><td><?= e($p['name']) ?></td><td><?= (int)$p['stock'] ?></td><td><?= $p['stock']<=0?'<span class="badge out">Habis</span>':($p['stock']<=5?'<span class="badge low">Menipis</span>':'<span class="badge ok">Aman</span>') ?></td></tr><?php endwhile; ?></tbody></table></div></section></div>
<?php require 'includes/footer.php'; ?>
