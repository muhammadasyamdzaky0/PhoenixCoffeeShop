<?php
require 'config/koneksi.php';
require_role(['customer','kasir']);

$pageTitle = 'Riwayat Pesanan';
$uid = (int) $_SESSION['user']['id'];

$st = $conn->prepare(
    'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC'
);
$st->bind_param('i', $uid);
$st->execute();
$orders = $st->get_result();

$notifSt = $conn->prepare(
    "SELECT order_code, status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5"
);

$notifSt->bind_param('i', $uid);
$notifSt->execute();
$notifications = $notifSt->get_result();

require 'includes/header.php';
?>

<style>
.riwayat {
    display: grid;
    gap: 20px;
}

.order-card {
    background: var(--panel, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 18px;
}

.order-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.order-code {
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 6px;
}

.order-date {
    color: var(--muted, #777);
    font-size: 13px;
}

.status {
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.status-menunggu {
    background: #fff3cd;
    color: #856404;
}

.status-diproses {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-selesai {
    background: #dcfce7;
    color: #166534;
}

.status-dibatalkan {
    background: #fee2e2;
    color: #b91c1c;
}

.order-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0 18px;
}

.order-table th,
.order-table td {
    padding: 12px 8px;
    text-align: left;
    border-bottom: 1px solid var(--border, #e5e7eb);
}

.order-table th {
    font-size: 12px;
    color: var(--muted, #777);
}

.order-table td {
    font-size: 14px;
}

.order-total {
    border-top: 1px solid var(--border, #e5e7eb);
    padding-top: 16px;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.total-price {
    font-size: 20px;
    font-weight: 800;
}

.order-info {
    color: var(--muted, #777);
    font-size: 13px;
    margin-top: 12px;
}

.empty-order {
    text-align: center;
    padding: 55px 20px;
    border-radius: 16px;
}

.empty-order .empty-icon {
    font-size: 44px;
    margin-bottom: 12px;
}

@media (max-width: 600px) {
    .order-card {
        padding: 15px;
    }

    .order-table th,
    .order-table td {
        padding: 10px 5px;
        font-size: 12px;
    }

    .total-price {
        font-size: 18px;
    }
}
</style>

<div class="page-heading">
    <div>
        <h1>Riwayat Pesanan</h1>
        <p>Pantau pesanan dan lihat kembali menu yang pernah kamu pesan.</p>
    </div>
    <a class="btn" href="customer.php">+ Pesan Lagi</a>
</div>

<section class="panel" style="margin-bottom:20px">
    <h3 style="margin-bottom:16px">
        🔔 Notifikasi Pesanan
    </h3>

    <?php if ($notifications->num_rows > 0): ?>

        <?php while ($n = $notifications->fetch_assoc()): ?>

            <?php
            $pesan = [
                'Menunggu Konfirmasi' =>
                    'Pesanan kamu sedang menunggu konfirmasi admin.',
                'Diproses' =>
                    'Pesanan kamu sedang disiapkan.',
                'Selesai' =>
                    'Pesanan kamu sudah selesai. Terima kasih!',
                'Dibatalkan' =>
                    'Pesanan kamu telah dibatalkan.'
            ];
            ?>

            <div style="
                padding:12px 0;
                border-bottom:1px solid var(--border,#eee)
            ">
                <strong><?= e($n['order_code']) ?></strong>

                <p style="margin:6px 0">
                    <?= e($pesan[$n['status']] ?? 'Status pesanan diperbarui.') ?>
                </p>

                <small style="color:var(--muted)">
                    <?= e($n['status']) ?> ·
                    <?= e(date(
                        'd M Y, H:i',
                        strtotime($n['created_at'])
                    )) ?>
                </small>
            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p style="color:var(--muted)">
            Belum ada notifikasi pesanan.
        </p>

    <?php endif; ?>
</section>

<div class="riwayat">

<?php if ($orders->num_rows === 0): ?>

    <section class="panel empty-order">
        <div class="empty-icon">☕</div>
        <h2>Belum Ada Pesanan</h2>
        <p style="color:var(--muted)">
            Yuk, pilih menu favoritmu dan buat pesanan pertamamu!
        </p>
        <br>
        <a class="btn" href="customer.php">Lihat Menu</a>
    </section>

<?php else: ?>

    <?php while ($o = $orders->fetch_assoc()): ?>

        <?php
        $statusClass = [
            'Menunggu Konfirmasi' => 'status-menunggu',
            'Diproses' => 'status-diproses',
            'Selesai' => 'status-selesai',
            'Dibatalkan' => 'status-dibatalkan'
        ];

        $badgeClass = $statusClass[$o['status']]
            ?? 'status-menunggu';

        $st2 = $conn->prepare(
            'SELECT product_name, qty, price, subtotal
             FROM order_items
             WHERE order_id = ?'
        );

        $st2->bind_param('i', $o['id']);
        $st2->execute();
        $items = $st2->get_result();
        ?>

        <section class="panel order-card">

            <div class="order-top">
                <div>
                    <div class="order-code">
                        <?= e($o['order_code']) ?>
                    </div>

                    <div class="order-date">
                        <?= e(date(
                            'd M Y, H:i',
                            strtotime($o['created_at'])
                        )) ?>
                    </div>
                </div>

                <span class="status <?= $badgeClass ?>">
                    <?= e($o['status']) ?>
                </span>
            </div>

            <div class="table-wrap">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php while ($it = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($it['product_name']) ?></td>
                            <td><?= rupiah($it['price']) ?></td>
                            <td><?= (int) $it['qty'] ?></td>
                            <td><?= rupiah($it['subtotal']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="order-total">
                <div>
                    <div style="color:var(--muted);font-size:13px">
                        Total Pembayaran
                    </div>
                    <div class="total-price">
                        <?= rupiah($o['total']) ?>
                    </div>
                </div>

                <div>
                    <div style="color:var(--muted);font-size:13px">
                        Metode Pembayaran
                    </div>
                    <strong><?= e($o['payment_method']) ?></strong>
                </div>
            </div>

            <?php if (!empty($o['notes'])): ?>
                <div class="order-info">
                    Catatan: <?= e($o['notes']) ?>
                </div>
            <?php endif; ?>

        </section>

        <?php $st2->close(); ?>

    <?php endwhile; ?>

<?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>