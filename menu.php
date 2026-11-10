<?php require 'config/koneksi.php'; 
require_role(['kasir']); 
$pageTitle='Manajemen Menu';
if($_SERVER['REQUEST_METHOD']==='POST') { $id=(int)($_POST['id']??0);
$name=trim($_POST['name']??'');
$category=$_POST['category']??'Minuman';
$price=(float)($_POST['price']??0);
$stock=(int)($_POST['stock']??0);
$unit=trim($_POST['unit']??'pcs');
if($name!==''&&$price>=0&&$stock>=0&&in_array($category,['Minuman','Makanan'],true)){if($id){$st=$conn->prepare('UPDATE products SET name=?,category=?,price=?,stock=?,unit=? WHERE id=?');
$st->bind_param('ssdisi',$name,$category,$price,$stock,$unit,$id);$st->execute();}else{$st=$conn->prepare('INSERT INTO products(name,category,price,stock,unit) VALUES(?,?,?,?,?)');$st->bind_param('ssdis',$name,$category,$price,$stock,$unit);$st->execute();}$_SESSION['flash']='Data menu berhasil disimpan.';header('Location: menu.php');exit;} }
if(isset($_GET['hapus'])){$id=(int)$_GET['hapus'];
$check=$conn->prepare('SELECT COUNT(*) n FROM transaction_items WHERE product_id=?');
$check->bind_param('i',$id);
$check->execute();$used=$check->get_result()->fetch_assoc()['n'];
if(!$used){$st=$conn->prepare('DELETE FROM products WHERE id=?');
$st->bind_param('i',$id);$st->execute();$_SESSION['flash']='Menu berhasil dihapus.';}else{$_SESSION['flash']='Menu tidak dapat dihapus karena sudah digunakan dalam transaksi.';}header('Location: menu.php');exit;}
$edit=null;if(isset($_GET['edit'])){$st=$conn->prepare('SELECT * FROM products WHERE id=?');$st->bind_param('i',$_GET['edit']);$st->execute();$edit=$st->get_result()->fetch_assoc();}
$products=$conn->query('SELECT * FROM products ORDER BY id DESC');require 'includes/header.php'; ?>
<div class="page-heading"><div><h1>Manajemen Menu</h1><p>Kelola menu makanan dan minuman di coffee shop Anda.</p></div><a class="btn" href="menu.php?form=1">＋ <?= $edit?'Edit Menu':'Tambah Menu' ?></a></div>
<?php if(isset($_GET['form'])||$edit): ?><section class="form-card" id="form-menu"><h3><?= $edit?'Edit Menu':'Tambah Menu Baru' ?></h3><form method="post"><input type="hidden" name="id" value="<?= e($edit['id']??'') ?>"><div class="form-grid"><div class="field full"><label>Nama Menu</label><input name="name" required value="<?= e($edit['name']??'') ?>" placeholder="Contoh: Es Kopi Susu"></div><div class="field"><label>Kategori</label><select name="category"><option <?= ($edit['category']??'')==='Minuman'?'selected':'' ?>>Minuman</option><option <?= ($edit['category']??'')==='Makanan'?'selected':'' ?>>Makanan</option></select></div><div class="field"><label>Harga (Rp)</label><input type="number" name="price" min="0" required value="<?= e($edit['price']??'') ?>"></div><div class="field"><label>Stok</label><input type="number" name="stock" min="0" required value="<?= e($edit['stock']??0) ?>"></div><div class="field"><label>Satuan</label><input name="unit" value="<?= e($edit['unit']??'pcs') ?>" required></div></div><div class="form-actions"><button class="btn">Simpan Menu</button><a class="btn light" href="menu.php">Batal</a></div></form></section><?php endif; ?>
<div class="toolbar"><input class="search" data-search="#menu-table" placeholder="⌕ Cari menu..."><a class="btn" href="menu.php?form=1">＋ Tambah Menu</a></div><div class="table-card table-wrap"><table id="menu-table"><thead><tr><th>No</th><th>Foto</th><th>Nama Menu</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Satuan</th><th>Aksi</th></tr></thead><tbody><?php if($products->num_rows):$no=1;while($p=$products->fetch_assoc()): ?><tr><td><?= $no++ ?></td><td><img class="menu-thumb" src="<?= e(product_image($p['name'],$p['category'])) ?>" alt="<?= e($p['name']) ?>"></td><td><div class="menu-name-cell"><strong><?= e($p['name']) ?></strong><small><?= e($p['category']) ?></small></div></td><td><?= e($p['category']) ?></td><td><?= rupiah($p['price']) ?></td><td><?= (int)$p['stock'] ?></td><td><?= e($p['unit']) ?></td><td><div class="actions"><a class="icon-btn" href="menu.php?edit=<?= $p['id'] ?>" title="Edit">✎</a><a class="icon-btn delete" data-confirm="Hapus menu ini?" href="menu.php?hapus=<?= $p['id'] ?>" title="Hapus">⌫</a></div></td></tr><?php endwhile;else: ?><tr><td colspan="8" class="empty">Belum ada data menu.</td></tr><?php endif; ?></tbody></table></div>
<?php require 'includes/footer.php'; ?>
