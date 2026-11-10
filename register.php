
<?php
require 'config/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $phone === '') {
        $error = 'Nama dan nomor HP wajib diisi.';
    } elseif (!preg_match('/^[0-9+\-\s]{9,20}$/', $phone)) {
        $error = 'Format nomor HP tidak valid.';
    } else {
        $role = 'customer';

        $st = $conn->prepare(
            "INSERT INTO users (name, phone, role)
             VALUES (?, ?, ?)"
        );

        $st->bind_param('sss', $name, $phone, $role);

        if ($st->execute()) {
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => $st->insert_id,
                'name' => $name,
                'phone' => $phone,
                'role' => 'customer'
            ];

            header('Location: customer.php');
            exit;
        } else {
            $error = 'Gagal mendaftar. Silakan coba lagi.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Customer | Phoenix Coffee</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(135deg,#2d1b14,#765039);
        }
        .auth-card {
            width: min(450px,100%);
            background: #fffdfb;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 20px 60px #0003;
        }
        .auth-brand {
            text-align: center;
            font: 700 25px 'Playfair Display';
            color: #493021;
        }
        .auth-sub {
            text-align: center;
            color: #8e7e72;
            margin: 7px 0 24px;
        }
        .field { margin-bottom: 15px; }
        .auth-error {
            background: #fbe7e4;
            color: #a32f25;
            border-radius: 8px;
            padding: 11px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
<main class="auth-wrap">
    <section class="auth-card">
        <div class="auth-brand">PHOENIX COFFEE</div>
        <p class="auth-sub">Isi data untuk mulai memesan</p>

        <?php if ($error): ?>
            <div class="auth-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label>Nama lengkap</label>
                <input name="name" required maxlength="120"
                       value="<?= e($_POST['name'] ?? '') ?>">
            </div>

            <div class="field">
                <label>Nomor HP</label>
                <input type="tel" name="phone" required maxlength="20"
                       placeholder="Contoh: 081234567890"
                       value="<?= e($_POST['phone'] ?? '') ?>">
            </div>

            <button class="btn" style="width:100%">
                Lanjutkan
            </button>
        </form>
    </section>
</main>
</body>
</html>
