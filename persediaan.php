<?php require 'config/koneksi.php';
require_role(['kasir']);
$pageTitle='Manajemen Persediaan';
if($_SERVER['REQUEST_METHOD']==='POST'){ $pid=(int)($_POST['product_id']??0);
$type=$_POST['movement_type']??'Masuk';
$qty=(int)($_POST['qty']??0);
$note=trim($_POST['note']??'');
if($pid>0&&$qty>0&&in_array($type,['Masuk','Keluar'],true)){ $st=$conn->prepare('SELECT stock FROM products WHERE id=?');
$st->bind_param('i',$pid);
$st->execute();
$p=$st->get_result()->fetch_assoc();
if($p&&($type==='Masuk'||$p['stock']>=$qty)){ $conn->begin_transaction();
try{$mv=$conn->prepare('INSERT INTO stock_movements(product_id,movement_type,qty,note) VALUES(?,?,?,?)');
$mv->bind_param('isis',$pid,$type,$qty,$note);
$mv->execute();
$delta=$type==='Masuk'?$qty:-$qty;
$up=$conn->prepare('UPDATE products SET stock=stock+? WHERE id=?');
$up->bind_param('ii',$delta,$pid);
$up->execute();
$conn->commit();
$_SESSION['flash']='Pergerakan stok berhasil dicatat.';
}
catch(Throwable $e)
{
    $conn->rollback();
$_SESSION['flash']='Gagal menyimpan perubahan stok.';
}
}else $_SESSION['flash']='Stok keluar melebihi stok yang tersedia.';
header('Location: persediaan.php');
exit;}
}
$products=$conn->query('SELECT * FROM products ORDER BY name');
$movements=$conn->query('SELECT m.*,p.name FROM stock_movements m JOIN products p ON p.id=m.product_id ORDER BY m.created_at DESC LIMIT 30');
require 'includes/header.php'; ?>
<div class="page-heading"><div>
    <h1>Manajemen Persediaan</h1>
    <p>Pantau stok produk dan catat pergerakan persediaan.</p>
</div></div>
<div class="tabs">
    <a class="tab active" href="#data-stok">Data Stok</a>
<a class="tab" href="#form-stok">Stok Masuk / Keluar</a>
<a class="tab" href="#riwayat">Riwayat Stok</a></div>
<section class="panel" id="form-stok">
    <h3>Catat Stok Masuk atau Keluar</h3>
    <form method="post">
        <div class="form-grid"><div class="field"><label>Produk</label><select name="product_id" required><?php while($p=$products->fetch_assoc()): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (stok: <?= $p['stock'] ?>)</option><?php endwhile; ?></select></div><div class="field"><label>Jenis Pergerakan</label><select name="movement_type"><option>Masuk</option><option>Keluar</option></select></div><div class="field"><label>Jumlah</label><input type="number" min="1" name="qty" required></div><div class="field"><label>Catatan</label><input name="note" placeholder="Contoh: Restock dari supplier"></div></div><div class="form-actions"><button class="btn">Simpan Pergerakan</button></div></form></section>
<div class="toolbar" id="data-stok"><h3>Data Stok Produk</h3><input class="search" data-search="#stock-table" placeholder="⌕ Cari produk..."></div><div class="table-card table-wrap"><table id="stock-table"><thead><tr><th>No</th><th>Nama Produk</th><th>Kategori</th><th>Stok Saat Ini</th><th>Satuan</th><th>Status</th></tr></thead><tbody><?php $all=$conn->query('SELECT * FROM products ORDER BY stock ASC');$i=1;while($p=$all->fetch_assoc()): ?><tr><td><?= $i++ ?></td><td><strong><?= e($p['name']) ?></strong></td><td><?= e($p['category']) ?></td><td><?= $p['stock'] ?></td><td><?= e($p['unit']) ?></td><td><?php if($p['stock']<=0): ?><span class="badge out">Habis</span><?php elseif($p['stock']<=5): ?><span class="badge low">Menipis</span><?php else: ?><span class="badge ok">Aman</span><?php endif; ?></td></tr><?php endwhile; ?></tbody></table></div>
<section class="panel" id="riwayat" style="margin-top:18px">
    <h3>Riwayat Pergerakan Stok Terbaru</h3>
    <div class="table-wrap">
        <table><thead><tr><th>Tanggal</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Catatan</th></tr></thead><tbody><?php if($movements->num_rows):while($m=$movements->fetch_assoc()): ?><tr><td><?= e($m['created_at']) ?></td><td><?= e($m['name']) ?></td><td><span class="badge <?= $m['movement_type']==='Masuk'?'ok':'low' ?>"><?= e($m['movement_type']) ?></span></td><td><?= (int)$m['qty'] ?></td><td><?= e($m['note']??'-') ?></td></tr><?php endwhile;else: ?><tr><td colspan="5" class="empty">Belum ada pergerakan stok.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
