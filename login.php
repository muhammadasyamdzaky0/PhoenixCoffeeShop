
<?php
require 'config/koneksi.php';

if (is_logged_in()) {
    $role = $_SESSION['user']['role'] ?? '';

    if ($role === 'kasir') {
        header('Location: index.php');
    } elseif ($role === 'customer') {
        header('Location: customer.php');
    } else {
        session_unset();
        session_destroy();
        header('Location: login.php');
    }

    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role
         FROM users WHERE email = ? LIMIT 1"
    );

    $stmt->bind_param('s', $email);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    if (
        $user &&
        (
            password_verify($password, $user['password']) ||
            $password === $user['password']
        ) &&
        in_array($user['role'], ['kasir', 'customer'], true)
    ) {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        if ($user['role'] === 'kasir') {
            header('Location: index.php');
        } else {
            header('Location: customer.php');
        }

        exit;
    }

    $error = 'Email atau password tidak sesuai.';
}
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Login | Phoenix Coffee</title>
<link rel="stylesheet" href="assets/css/style.css">

<style>
.auth-wrap {
    min-height:100vh;
    display:grid;
    place-items:center;
    padding:24px;
    background:linear-gradient(135deg,#2d1b14,#765039);
}

.auth-card {
    width:min(430px,100%);
    background:#fffdfb;
    border-radius:18px;
    padding:32px;
    box-shadow:0 20px 60px #0003;
}

.auth-brand {
    text-align:center;
    font:700 25px 'Playfair Display';
    color:#493021;
    margin-bottom:6px;
}

.auth-sub {
    text-align:center;
    color:#8e7e72;
    margin:0 0 24px;
}

.auth-card .field {
    margin-bottom:16px;
}

.auth-links {
    text-align:center;
    margin-top:18px;
    color:#8e7e72;
    font-size:12px;
}

.auth-links a {
    color:#765039;
    font-weight:700;
}

.auth-error {
    background:#fbe7e4;
    color:#a32f25;
    border-radius:8px;
    padding:11px;
    margin-bottom:14px;
}
</style>
</head>

<body>

<main class="auth-wrap">
<section class="auth-card">

<div class="auth-brand">PHOENIX COFFEE</div>
<p class="auth-sub">Masuk ke akun Anda</p>

<?php if ($error): ?>
<div class="auth-error">
    <?= e($error) ?>
</div>
<?php endif; ?>

<form method="post">

    <div class="field">
        <label>Email</label>
        <input
            type="email"
            name="email"
            required
            autocomplete="email">
    </div>

    <div class="field">
        <label>Password</label>
        <input
            type="password"
            name="password"
            required
            autocomplete="current-password">
    </div>

    <button class="btn" style="width:100%">
        Masuk
    </button>

</form>

<div class="auth-links">
    Belum punya akun?
    <a href="register.php">Daftar sebagai customer</a>
</div>

</section>
</main>

</body>
</html>
