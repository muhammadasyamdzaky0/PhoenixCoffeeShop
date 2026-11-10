
<?php
require 'config/koneksi.php';
require_role(['admin', 'kasir','customer']);

$pageTitle = 'Transaksi Kasir';

if (!isset($_SESSION['cart_kasir'])) {
    $_SESSION['cart_kasir'] = [];
}

if (empty($_SESSION['csrf_kasir'])) {
    $_SESSION['csrf_kasir'] = bin2hex(random_bytes(32));
}

function kasirRedirect() {
    header('Location: transaksi.php');
    exit;
}

function validCsrf() {
    return isset($_POST['csrf'], $_SESSION['csrf_kasir'])
        && hash_equals($_SESSION['csrf_kasir'], $_POST['csrf']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'tambah') {
            $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

            if (!$id || !$qty || $qty < 1) {
                $error = 'Produk atau jumlah tidak valid.';
            } else {
                $st = $conn->prepare(
                    'SELECT id, name, price, stock FROM products WHERE id = ?'
                );
                $st->bind_param('i', $id);
                $st->execute();
                $product = $st->get_result()->fetch_assoc();

                if (!$product) {
                    $error = 'Produk tidak ditemukan.';
                } elseif ($product['stock'] < 1) {
                    $error = 'Stok produk habis.';
                } else {
                    $current = (int) ($_SESSION['cart_kasir'][$id] ?? 0);
                    $newQty = $current + $qty;

                    if ($newQty > (int) $product['stock']) {
                        $error = 'Jumlah melebihi stok yang tersedia.';
                    } else {
                        $_SESSION['cart_kasir'][$id] = $newQty;
                        kasirRedirect();
                    }
                }
            }
        }

        if ($action === 'ubah') {
            $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

            if ($id && isset($_SESSION['cart_kasir'][$id])) {
                if (!$qty || $qty < 1) {
                    unset($_SESSION['cart_kasir'][$id]);
                    kasirRedirect();
                }

                $st = $conn->prepare('SELECT stock FROM products WHERE id = ?');
                $st->bind_param('i', $id);
                $st->execute();
                $product = $st->get_result()->fetch_assoc();

                if (!$product || $qty > (int) $product['stock']) {
                    $error = 'Jumlah melebihi stok yang tersedia.';
                } else {
                    $_SESSION['cart_kasir'][$id] = $qty;
                    kasirRedirect();
                }
            }
        }

        if ($action === 'hapus') {
            $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            if ($id) {
                unset($_SESSION['cart_kasir'][$id]);
            }
            kasirRedirect();
        }

        if ($action === 'kosongkan') {
            $_SESSION['cart_kasir'] = [];
            kasirRedirect();
        }

        if ($action === 'bayar') {
            $method = $_POST['payment_method'] ?? '';
            $cashInput = $_POST['payment'] ?? '0';

            if (!in_array($method, ['Tunai', 'QRIS', 'Debit'], true)) {
                $error = 'Metode pembayaran tidak valid.';
            } elseif (empty($_SESSION['cart_kasir'])) {
                $error = 'Keranjang masih kosong.';
            } elseif (!is_numeric($cashInput) || (float) $cashInput < 0) {
                $error = 'Jumlah pembayaran tidak valid.';
            } else {
                $invoice = 'INV' . date('YmdHis') .
                    strtoupper(bin2hex(random_bytes(3)));

                try {
                    $conn->begin_transaction();

                    $total = 0;
                    $items = [];

                    // Kunci stok selama proses transaksi.
                    $stockSt = $conn->prepare(
                        'SELECT id, name, price, stock
                         FROM products WHERE id = ? FOR UPDATE'
                    );

                    foreach ($_SESSION['cart_kasir'] as $id => $qty) {
                        $id = (int) $id;
                        $qty = (int) $qty;

                        if ($qty < 1) {
                            throw new Exception('Jumlah produk tidak valid.');
                        }

                        $stockSt->bind_param('i', $id);
                        $stockSt->execute();
                        $p = $stockSt->get_result()->fetch_assoc();

                        if (!$p || (int) $p['stock'] < $qty) {
                            throw new Exception(
                                'Stok ' . ($p['name'] ?? 'produk') .
                                ' tidak mencukupi.'
                            );
                        }

                        $price = (float) $p['price'];
                        $subtotal = $price * $qty;
                        $total += $subtotal;

                        $items[] = [
                            'id' => $id,
                            'name' => $p['name'],
                            'qty' => $qty,
                            'price' => $price,
                            'subtotal' => $subtotal
                        ];
                    }

                    if ($method === 'Tunai') {
                        $payment = (float) $cashInput;

                        if ($payment < $total) {
                            throw new Exception(
                                'Uang pembayaran kurang dari total.'
                            );
                        }

                        $change = $payment - $total;
                    } else {
                        // QRIS/Debit dicatat saja; bukan verifikasi
                        // pembayaran elektronik secara otomatis.
                        $payment = $total;
                        $change = 0;
                    }

                    $st = $conn->prepare(
                        'INSERT INTO transactions
                         (invoice, total, payment, change_amount, payment_method)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $st->bind_param(
                        'sddds',
                        $invoice,
                        $total,
                        $payment,
                        $change,
                        $method
                    );
                    $st->execute();

                    $transactionId = $conn->insert_id;

                    $itemSt = $conn->prepare(
                        'INSERT INTO transaction_items
                         (transaction_id, product_id, product_name, qty, price, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    );

                    $updateStock = $conn->prepare(
                        'UPDATE products SET stock = stock - ?
                         WHERE id = ? AND stock >= ?'
                    );

                    foreach ($items as $item) {
                        $pid = $item['id'];
                        $name = $item['name'];
                        $qty = $item['qty'];
                        $price = $item['price'];
                        $subtotal = $item['subtotal'];

                        $itemSt->bind_param(
                            'iisidd',
                            $transactionId,
                            $pid,
                            $name,
                            $qty,
                            $price,
                            $subtotal
                        );
                        $itemSt->execute();

                        $updateStock->bind_param('iii', $qty, $pid, $qty);
                        $updateStock->execute();

                        if ($updateStock->affected_rows !== 1) {
                            throw new Exception(
                                'Stok berubah. Silakan ulangi transaksi.'
                            );
                        }
                    }

                    $conn->commit();
                    $_SESSION['cart_kasir'] = [];

                    header('Location: struk.php?id=' . $transactionId);
                    exit;

                } catch (Throwable $e) {
                    $conn->rollback();
                    $error = $e instanceof mysqli_sql_exception
                        ? 'Transaksi gagal disimpan. Periksa database dan coba lagi.'
                        : $e->getMessage();
                }
            }
        }
    }
}

// Ambil produk untuk daftar menu.
$products = $conn->query(
    'SELECT id, name, price, stock
     FROM products
     WHERE stock > 0
     ORDER BY name ASC'
);

$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart_kasir'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart_kasir']));
    $ids = array_filter($ids, fn($id) => $id > 0);

    if ($ids) {
        $idList = implode(',', $ids);
        $result = $conn->query(
            "SELECT id, name, price, stock
             FROM products WHERE id IN ($idList)"
        );

        while ($p = $result->fetch_assoc()) {
            $qty = (int) $_SESSION['cart_kasir'][$p['id']];
            $subtotal = (float) $p['price'] * $qty;
            $p['qty'] = $qty;
            $p['subtotal'] = $subtotal;
            $cartItems[] = $p;
            $total += $subtotal;
        }
    }
}

require 'includes/header.php';
?>

<style>
.kasir-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(300px, 0.9fr);
    gap: 20px;
}
.kasir-card {
    background: var(--panel, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 18px;
}
.kasir-products {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}
.kasir-product {
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    padding: 14px;
}
.kasir-product h3 { margin: 0 0 8px; font-size: 15px; }
.kasir-muted { color: var(--muted, #777); font-size: 13px; }
.kasir-form input, .kasir-form select {
    box-sizing: border-box;
    max-width: 100%;
    padding: 9px;
    border: 1px solid var(--border, #ddd);
    border-radius: 8px;
}
.kasir-form button {
    cursor: pointer;
}
.kasir-total {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 20px;
    font-weight: 800;
    margin: 18px 0;
}
.kasir-alert {
    padding: 12px 15px;
    border-radius: 10px;
    background: #fee2e2;
    color: #991b1b;
    margin-bottom: 16px;
}
.kasir-table { width: 100%; border-collapse: collapse; }
.kasir-table th, .kasir-table td {
    text-align: left;
    padding: 10px 5px;
    border-bottom: 1px solid var(--border, #eee);
    font-size: 13px;
}
@media(max-width: 800px) {
    .kasir-layout { grid-template-columns: 1fr; }
}
</style>

<div class="page-heading">
    <div>
        <h1>Transaksi Kasir</h1>
        <p>Kelola pesanan, pembayaran, dan penjualan coffee shop.</p>
    </div>
    <a class="btn" href="riwayat_transaksi.php">Riwayat Transaksi</a>
</div>

<?php if ($error): ?>
    <div class="kasir-alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="kasir-layout">
    <section class="kasir-card">
        <h2>Daftar Menu</h2>
        <p class="kasir-muted">Pilih produk untuk memasukkannya ke keranjang.</p>

        <div class="kasir-products">
            <?php while ($p = $products->fetch_assoc()): ?>
                <div class="kasir-product">
                    <h3><?= e($p['name']) ?></h3>
                    <p><strong><?= rupiah($p['price']) ?></strong></p>
                    <p class="kasir-muted">Stok: <?= (int) $p['stock'] ?></p>

                    <form method="post" class="kasir-form">
                        <input type="hidden" name="csrf"
                               value="<?= e($_SESSION['csrf_kasir']) ?>">
                        <input type="hidden" name="action" value="tambah">
                        <input type="hidden" name="product_id"
                               value="<?= (int) $p['id'] ?>">
                        <label class="kasir-muted">Jumlah</label><br>
                        <input type="number" name="qty" value="1" min="1"
                               max="<?= (int) $p['stock'] ?>"
                               style="width:70px" required>
                        <button class="btn" type="submit">+ Tambah</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="kasir-card">
        <h2>Keranjang</h2>

        <?php if (!$cartItems): ?>
            <p class="kasir-muted">Keranjang masih kosong.</p>
        <?php else: ?>
            <div style="overflow-x:auto">
                <table class="kasir-table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['name']) ?></strong><br>
                                <span class="kasir-muted">
                                    <?= rupiah($item['price']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="kasir-form">
                                    <input type="hidden" name="csrf"
                                           value="<?= e($_SESSION['csrf_kasir']) ?>">
                                    <input type="hidden" name="action" value="ubah">
                                    <input type="hidden" name="product_id"
                                           value="<?= (int) $item['id'] ?>">
                                    <input type="number" name="qty"
                                           value="<?= (int) $item['qty'] ?>"
                                           min="0" max="<?= (int) $item['stock'] ?>"
                                           style="width:55px" required>
                                    <button type="submit">OK</button>
                                </form>
                            </td>
                            <td><?= rupiah($item['subtotal']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="csrf"
                                           value="<?= e($_SESSION['csrf_kasir']) ?>">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="product_id"
                                           value="<?= (int) $item['id'] ?>">
                                    <button type="submit">×</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="kasir-total">
                <span>Total</span>
                <span><?= rupiah($total) ?></span>
            </div>

            <form method="post" class="kasir-form" id="paymentForm">
                <input type="hidden" name="csrf"
                       value="<?= e($_SESSION['csrf_kasir']) ?>">
                <input type="hidden" name="action" value="bayar">

                <label for="payment_method">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method"
                        style="width:100%;margin:8px 0 14px">
                    <option value="Tunai">Tunai</option>
                    <option value="QRIS">QRIS</option>
                    <option value="Debit">Debit</option>
                </select>

                <div id="cashArea">
                    <label for="payment">Uang Diterima</label>
                    <input type="number" id="payment" name="payment"
                           min="0" step="1" value="<?= (int) ceil($total) ?>"
                           style="width:100%;margin:8px 0">
                    <p class="kasir-muted">
                        Perkiraan kembalian:
                        <strong id="changePreview">Rp0</strong>
                    </p>
                </div>

                <button type="submit" class="btn"
                        style="width:100%;margin-top:12px">
                    Simpan Transaksi
                </button>
            </form>

            <form method="post" style="margin-top:10px">
                <input type="hidden" name="csrf"
                       value="<?= e($_SESSION['csrf_kasir']) ?>">
                <input type="hidden" name="action" value="kosongkan">
                <button type="submit" class="btn"
                        onclick="return confirm('Kosongkan keranjang?')">
                    Kosongkan Keranjang
                </button>
            </form>
        <?php endif; ?>
    </section>
</div>

<script>
const methodSelect = document.getElementById('payment_method');
const paymentInput = document.getElementById('payment');
const cashArea = document.getElementById('cashArea');
const changePreview = document.getElementById('changePreview');
const grandTotal = <?= json_encode($total) ?>;

function updatePaymentView() {
    if (!methodSelect) return;

    const cash = methodSelect.value === 'Tunai';
    cashArea.style.display = cash ? 'block' : 'none';
    paymentInput.required = cash;
    paymentInput.disabled = !cash;
    updateChange();
}

function updateChange() {
    if (!paymentInput || !changePreview) return;
    const paid = Number(paymentInput.value || 0);
    const change = Math.max(0, paid - grandTotal);
    changePreview.textContent = new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR',
        maximumFractionDigits: 0
    }).format(change);
}

if (methodSelect) {
    methodSelect.addEventListener('change', updatePaymentView);
    paymentInput.addEventListener('input', updateChange);
    updatePaymentView();
}
</script>

<?php require 'includes/footer.php'; ?>
