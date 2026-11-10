
<?php
require 'config/koneksi.php';
require_role(['admin', 'kasir']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: riwayat_transaksi.php');
    exit;
}

$st = $conn->prepare('SELECT * FROM transactions WHERE id = ?');
$st->bind_param('i', $id);
$st->execute();
$transaction = $st->get_result()->fetch_assoc();

if (!$transaction) {
    http_response_code(404);
    exit('Transaksi tidak ditemukan.');
}

$st = $conn->prepare(
    'SELECT product_name, qty, price, subtotal
     FROM transaction_items WHERE transaction_id = ?'
);
$st->bind_param('i', $id);
$st->execute();
$items = $st->get_result();

$pageTitle = 'Struk Transaksi';
require 'includes/header.php';
?>

<style>
.receipt {
    max-width: 480px;
    margin: 20px auto;
    padding: 28px;
    background: var(--panel, #fff);
    border: 1px solid var(--border, #ddd);
    border-radius: 14px;
}
.receipt h2, .receipt .center { text-align: center; }
.receipt-line {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin: 10px 0;
}
.receipt-table { width: 100%; border-collapse: collapse; }
.receipt-table th, .receipt-table td {
    text-align: left;
    padding: 8px 2px;
    border-bottom: 1px dashed #aaa;
    font-size: 13px;
}
.receipt-table th:last-child, .receipt-table td:last-child {
    text-align: right;
}
@media print {
    header, nav, footer, .no-print,
    .page-heading, .sidebar {
        display: none !important;
    }
    body, main, .content, .receipt {
        background: #fff !important;
        color: #000 !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        max-width: none !important;
    }
}
</style>

<div class="page-heading no-print">
    <div>
        <h1>Struk Transaksi</h1>
        <p>Nota penjualan Phoenix Coffee Shop.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn" onclick="window.print()">Cetak Struk</button>
        <a class="btn" href="transaksi.php">Transaksi Baru</a>
        <a class="btn" href="riwayat_transaksi.php">Riwayat</a>
    </div>
</div>

<section class="receipt">
    <h2>Phoenix Coffee Shop</h2>
    <p class="center">Struk Pembelian</p>
    <hr>

    <div class="receipt-line">
        <span>Invoice</span>
        <strong><?= e($transaction['invoice']) ?></strong>
    </div>
    <div class="receipt-line">
        <span>Tanggal</span>
        <span><?= e(date(
            'd-m-Y H:i',
            strtotime($transaction['created_at'])
        )) ?></span>
    </div>
    <div class="receipt-line">
        <span>Kasir</span>
        <span><?= e($_SESSION['user']['name'] ?? 'Kasir') ?></span>
    </div>

    <hr>

    <table class="receipt-table">
        <thead>
            <tr>
                <th>Menu</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
                <td>
                    <?= e($item['product_name']) ?><br>
                    <small><?= rupiah($item['price']) ?> / item</small>
                </td>
                <td><?= (int) $item['qty'] ?></td>
                <td><?= rupiah($item['subtotal']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <hr>

    <div class="receipt-line">
        <strong>Total</strong>
        <strong><?= rupiah($transaction['total']) ?></strong>
    </div>
    <div class="receipt-line">
        <span>Metode Pembayaran</span>
        <span><?= e($transaction['payment_method']) ?></span>
    </div>
    <div class="receipt-line">
        <span>Uang Diterima</span>
        <span><?= rupiah($transaction['payment']) ?></span>
    </div>
    <div class="receipt-line">
        <span>Kembalian</span>
        <strong><?= rupiah($transaction['change_amount']) ?></strong>
    </div>

    <hr>
    <p class="center">Terima kasih telah berkunjung!</p>
    <p class="center">Selamat menikmati kopi Anda ☕</p>
</section>

<?php require 'includes/footer.php'; ?>
