
<?php
require 'config/koneksi.php';
require_role(['kasir']);

// Ambil ID pesanan
$orderId = (int)($_GET['id'] ?? 0);

// Pilihan format cetak
$format = $_GET['format'] ?? 'thermal';

if (!in_array($format, ['thermal', 'a4'], true)) {
    $format = 'thermal';
}

if ($orderId < 1) {
    die('ID pesanan tidak valid.');
}

// Ambil data pesanan dan customer
$st = $conn->prepare("
    SELECT o.*,
           COALESCE(u.name, 'Customer tidak ditemukan') AS customer_name,
           u.phone
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    WHERE o.id = ?
");

$st->bind_param('i', $orderId);
$st->execute();

$order = $st->get_result()->fetch_assoc();

if (!$order) {
    die('Pesanan tidak ditemukan.');
}

// Ambil detail produk
$stItems = $conn->prepare("
    SELECT product_name, qty, subtotal
    FROM order_items
    WHERE order_id = ?
");

$stItems->bind_param('i', $orderId);
$stItems->execute();

$items = $stItems->get_result();

function e_struk($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function rupiah_struk($value) {
    return 'Rp ' . number_format(
        (float)$value,
        0,
        ',',
        '.'
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Struk <?= e_struk($order['order_code']) ?> - Phoenix Coffee</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 25px;
    background: #eeeeee;
    font-family: Arial, sans-serif;
    color: #222;
}

.actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.actions a,
.actions button {
    padding: 11px 18px;
    border: none;
    border-radius: 6px;
    background: #6f4e37;
    color: white;
    text-decoration: none;
    cursor: pointer;
    font-size: 13px;
}

.actions .active {
    background: #c9a227;
    color: #2c1b12;
}

.receipt {
    margin: auto;
    background: white;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,.12);
}

.receipt.thermal {
    width: 80mm;
    max-width: 100%;
    font-size: 12px;
    padding: 12px;
}

.receipt.a4 {
    width: 210mm;
    min-height: 148mm;
    padding: 30px;
    max-width: 100%;
    font-size: 14px;
}

.header {
    text-align: center;
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
    color: #6f4e37;
    font-size: 25px;
    letter-spacing: 2px;
}

.header p {
    margin: 5px 0;
    color: #666;
}

.thermal .header h1 {
    font-size: 20px;
}

.line {
    border-top: 1px dashed #888;
    margin: 12px 0;
}

.info {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin: 7px 0;
}

.info span:first-child {
    color: #666;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}

th, td {
    padding: 9px 5px;
    text-align: left;
    vertical-align: top;
}

th {
    border-bottom: 1px solid #aaa;
}

td:last-child,
th:last-child {
    text-align: right;
}

.thermal th,
.thermal td {
    padding: 7px 2px;
    font-size: 11px;
}

.total {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 18px;
    margin-top: 15px;
}

.thermal .total {
    font-size: 14px;
}

.notes {
    margin-top: 12px;
    overflow-wrap: anywhere;
}

.footer {
    text-align: center;
    margin-top: 25px;
    line-height: 1.6;
}

.footer strong {
    color: #6f4e37;
}

@media print {
    body {
        background: white;
        padding: 0;
    }

    .actions {
        display: none !important;
    }

    .receipt {
        box-shadow: none;
        margin: 0 auto;
        max-width: none;
    }

    .receipt.thermal {
        width: 80mm;
        padding: 3mm;
    }

    .receipt.a4 {
        width: 100%;
        min-height: 0;
        padding: 10mm;
    }

    .line {
        border-top-style: dashed;
    }

    @page {
        margin: 0;
        size: auto;
    }
}
</style>
</head>

<body>

<div class="actions">

    <a href="?id=<?= $orderId ?>&format=thermal"
       class="<?= $format === 'thermal' ? 'active' : '' ?>">
        Thermal 80mm
    </a>

    <a href="?id=<?= $orderId ?>&format=a4"
       class="<?= $format === 'a4' ? 'active' : '' ?>">
        Kertas A4
    </a>

    <button type="button" onclick="window.print()">
        Cetak / Simpan PDF
    </button>

</div>

<div class="receipt <?= $format ?>">

    <div class="header">
        <h1>PHOENIX COFFEE</h1>
        <p>COFFEE SHOP</p>
        <p>Terima kasih telah memilih kami</p>
    </div>

    <div class="line"></div>

    <div class="info">
        <span>No. Pesanan</span>
        <strong><?= e_struk($order['order_code']) ?></strong>
    </div>

    <div class="info">
        <span>Tanggal</span>
        <span><?= e_struk($order['created_at']) ?></span>
    </div>

    <div class="info">
        <span>Customer</span>
        <span><?= e_struk($order['customer_name']) ?></span>
    </div>

    <div class="info">
        <span>No. HP</span>
        <span><?= e_struk($order['phone'] ?: '-') ?></span>
    </div>

    <div class="line"></div>

    <strong>DETAIL PESANAN</strong>

    <table>
        <thead>
            <tr>
                <th>Menu</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($items->num_rows > 0): ?>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= e_struk($item['product_name']) ?></td>
                    <td><?= (int)$item['qty'] ?></td>
                    <td><?= rupiah_struk($item['subtotal']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">Tidak ada detail pesanan.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="line"></div>

    <div class="total">
        <span>TOTAL</span>
        <span><?= rupiah_struk($order['total']) ?></span>
    </div>

    <div class="line"></div>

    <div class="info">
        <span>Pembayaran</span>
        <strong><?= e_struk($order['payment_method']) ?></strong>
    </div>

    <div class="info">
        <span>Status</span>
        <strong><?= e_struk($order['status']) ?></strong>
    </div>

    <?php if (!empty($order['notes'])): ?>
        <div class="notes">
            <strong>Catatan:</strong><br>
            <?= nl2br(e_struk($order['notes'])) ?>
        </div>
    <?php endif; ?>

    <div class="line"></div>

    <div class="footer">
        <strong>TERIMA KASIH!</strong>
        <p>Selamat menikmati kopi Anda.</p>
        <p>Have a nice day ☕</p>
        <small>PHOENIX COFFEE</small>
    </div>

</div>

</body>
</html>
