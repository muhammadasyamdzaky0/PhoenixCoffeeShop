
<?php
require 'config/koneksi.php';
require_role(['customer', 'kasir']);

$pageTitle = 'Riwayat Transaksi';

$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $search = '%' . $keyword . '%';
    $st = $conn->prepare(
        'SELECT * FROM transactions
         WHERE invoice LIKE ?
         ORDER BY created_at DESC'
    );
    $st->bind_param('s', $search);
} else {
    $st = $conn->prepare(
        'SELECT * FROM transactions ORDER BY created_at DESC'
    );
}

$st->execute();
$transactions = $st->get_result();

$summary = $conn->query(
    'SELECT COUNT(*) AS jumlah, COALESCE(SUM(total), 0) AS penjualan
     FROM transactions'
)->fetch_assoc();

require 'includes/header.php';
?>

<style>
.history-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.history-stat {
    padding: 20px;
    border: 1px solid var(--border, #ddd);
    border-radius: 14px;
    background: var(--panel, #fff);
}
.history-stat p {
    color: var(--muted, #777);
    margin-bottom: 8px;
}
.history-stat strong { font-size: 22px; }
.history-search {
    display: flex;
    gap: 10px;
    margin-bottom: 18px;
}
.history-search input {
    flex: 1;
    min-width: 0;
    padding: 10px 12px;
    border: 1px solid var(--border, #ddd);
    border-radius: 9px;
}
.history-table {
    width: 100%;
    border-collapse: collapse;
}
.history-table th, .history-table td {
    text-align: left;
    padding: 12px 10px;
    border-bottom: 1px solid var(--border, #eee);
}
@media(max-width: 600px) {
    .history-stats { grid-template-columns: 1fr; }
    .history-search { flex-wrap: wrap; }
    .history-table th, .history-table td {
        padding: 9px 6px;
        font-size: 12px;
    }
}
</style>

<div class="page-heading">
    <div>
        <h1>Riwayat Transaksi</h1>
        <p>Lihat dan cetak transaksi penjualan sebelumnya.</p>
    </div>
    <a class="btn" href="transaksi.php">+ Transaksi Baru</a>
</div>

<div class="history-stats">
    <section class="history-stat">
        <p>Total Transaksi</p>
        <strong><?= number_format((int) $summary['jumlah'], 0, ',', '.') ?></strong>
    </section>
    <section class="history-stat">
        <p>Total Penjualan</p>
        <strong><?= rupiah($summary['penjualan']) ?></strong>
    </section>
</div>

<section class="panel" style="padding:20px">
    <form method="get" class="history-search">
        <input type="search" name="q"
               placeholder="Cari nomor invoice..."
               value="<?= e($keyword) ?>">
        <button class="btn" type="submit">Cari</button>
        <?php if ($keyword !== ''): ?>
            <a class="btn" href="riwayat_transaksi.php">Reset</a>
        <?php endif; ?>
    </form>

    <div style="overflow-x:auto">
        <table class="history-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($transactions->num_rows === 0): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:28px">
                        Belum ada transaksi ditemukan.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php while ($t = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= e($t['invoice']) ?></strong></td>
                        <td><?= e(date(
                            'd-m-Y H:i',
                            strtotime($t['created_at'])
                        )) ?></td>
                        <td><?= rupiah($t['total']) ?></td>
                        <td><?= e($t['payment_method']) ?></td>
                        <td>
                            <a class="btn"
                               href="struk.php?id=<?= (int) $t['id'] ?>">
                                Detail / Struk
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
