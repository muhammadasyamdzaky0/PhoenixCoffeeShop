
<?php
require 'config/koneksi.php';
require_role(['kasir']);

$pageTitle = 'Pesanan Online';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $token = $_POST['csrf_token'] ?? '';
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    $allowed = [
        'Menunggu Konfirmasi',
        'Diproses',
        'Selesai',
        'Dibatalkan'
    ];

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak valid. Muat ulang halaman.';
    } elseif ($orderId < 1 || !in_array($newStatus, $allowed, true)) {
        $error = 'Data pesanan tidak valid.';
    } else {
        $conn->begin_transaction();

        try {
            $st = $conn->prepare(
                'SELECT status FROM orders WHERE id=? FOR UPDATE'
            );
            $st->bind_param('i', $orderId);
            $st->execute();
            $order = $st->get_result()->fetch_assoc();

            if (!$order) {
                throw new Exception('Pesanan tidak ditemukan.');
            }

            $oldStatus = $order['status'];

            if ($oldStatus === $newStatus) {
                throw new Exception('Status pesanan tidak berubah.');
            }

            // Perubahan status yang diperbolehkan:
            // Menunggu Konfirmasi -> Diproses atau Dibatalkan
            // Diproses -> Selesai
            $validTransition =
                ($oldStatus === 'Menunggu Konfirmasi' &&
                    in_array($newStatus, ['Diproses', 'Dibatalkan'], true))
                || ($oldStatus === 'Diproses' &&
                    $newStatus === 'Selesai');

            if (!$validTransition) {
                throw new Exception(
                    'Perubahan status ini tidak diperbolehkan.'
                );
            }

            // Stok dikurangi satu kali saat pesanan mulai diproses.
            if ($newStatus === 'Diproses') {
                $stItems = $conn->prepare(
                    'SELECT product_id, qty
                     FROM order_items WHERE order_id=?'
                );
                $stItems->bind_param('i', $orderId);
                $stItems->execute();
                $items = $stItems->get_result();

                $upd = $conn->prepare(
                    'UPDATE products
                     SET stock = stock - ?
                     WHERE id = ? AND stock >= ?'
                );

                $foundItems = false;

                while ($item = $items->fetch_assoc()) {
                    $foundItems = true;
                    $qty = (int)$item['qty'];
                    $productId = (int)$item['product_id'];

                    if ($qty < 1) {
                        throw new Exception('Jumlah produk tidak valid.');
                    }

                    $upd->bind_param(
                        'iii', $qty, $productId, $qty
                    );
                    $upd->execute();

                    if ($upd->affected_rows !== 1) {
                        throw new Exception(
                            'Stok produk tidak cukup atau produk tidak ditemukan.'
                        );
                    }
                }

                if (!$foundItems) {
                    throw new Exception('Detail pesanan tidak ditemukan.');
                }
            }

            $up = $conn->prepare(
                'UPDATE orders SET status=? WHERE id=?'
            );
            $up->bind_param('si', $newStatus, $orderId);
            $up->execute();

            $conn->commit();
            $_SESSION['flash'] = 'Status pesanan berhasil diperbarui.';
            header('Location: admin_pesanan.php');
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}


$sql = "
    SELECT o.*, 
           COALESCE(u.name, 'Customer tidak ditemukan') AS customer_name,
           u.phone
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
";
$orders = $conn->query($sql);

if (!$orders) {
    die('Gagal mengambil data pesanan: ' . $conn->error);
}

require 'includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Pesanan Online</h1>
        <p>Kelola pesanan customer Phoenix Coffee.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!$orders->num_rows): ?>
    <section class="panel empty">
        Belum ada pesanan online.
    </section>
<?php else: ?>
    <?php while ($o = $orders->fetch_assoc()): ?>
        <section class="panel" style="margin-bottom:18px">
            <div class="toolbar">
                <div>
                    <h3><?= e($o['order_code']) ?></h3>
                    <small style="color:var(--muted)">
                        <?= e($o['created_at']) ?>
                    </small>
                </div>
                <span class="badge <?= $o['status'] === 'Selesai' ? 'ok' : ($o['status'] === 'Dibatalkan' ? 'out' : 'low') ?>">
                    <?= e($o['status']) ?>
                </span>
            </div>

            <p>
                <strong>Customer:</strong>
                <?= e($o['customer_name']) ?><br>
                <strong>No. HP:</strong>
                <?= e($o['phone'] ?: '-') ?><br>
                <strong>Pembayaran:</strong>
                <?= e($o['payment_method']) ?>
            </p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $st = $conn->prepare(
                        'SELECT product_name, qty, subtotal
                         FROM order_items WHERE order_id=?'
                    );
                    $st->bind_param('i', $o['id']);
                    $st->execute();
                    $items = $st->get_result();

                    while ($item = $items->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= e($item['product_name']) ?></td>
                            <td><?= (int)$item['qty'] ?></td>
                            <td><?= rupiah($item['subtotal']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <p>
                <strong>Total: <?= rupiah($o['total']) ?></strong>
                
<div class="form-actions" style="margin-bottom:15px">

    <a href="cetak_struk.php?id=<?= (int)$o['id'] ?>&format=thermal"
       class="btn"
       target="_blank">
        Cetak Struk Thermal
    </a>

    <a href="cetak_struk.php?id=<?= (int)$o['id'] ?>&format=a4"
       class="btn"
       target="_blank">
        Cetak Struk A4
    </a>

</div>

            </p>

            <?php if ($o['notes']): ?>
                <p style="color:var(--muted)">
                    Catatan: <?= e($o['notes']) ?>
                </p>
            <?php endif; ?>

            <?php if ($o['status'] === 'Menunggu Konfirmasi'): ?>
                <form method="post" class="form-actions">
                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="order_id"
                           value="<?= (int)$o['id'] ?>">
                    <label for="status-<?= (int)$o['id'] ?>">
                        Ubah status
                    </label>
                    <select id="status-<?= (int)$o['id'] ?>"
                            name="status">
                        <option value="Diproses">Terima & Proses</option>
                        <option value="Dibatalkan">Batalkan Pesanan</option>
                    </select>
                    <button class="btn" name="update_status" value="1">
                        Simpan
                    </button>
                </form>
            <?php elseif ($o['status'] === 'Diproses'): ?>
                <form method="post" class="form-actions">
                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="order_id"
                           value="<?= (int)$o['id'] ?>">
                    <input type="hidden" name="status" value="Selesai">
                    <button class="btn" name="update_status" value="1">
                        Tandai Selesai
                    </button>
                </form>
            <?php endif; ?>
        </section>
    <?php endwhile; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
