```php
<?php
require 'config/koneksi.php';

$name = 'Kasir Phoenix';
$email = 'kasir@phoenixcoffee.com';
$password = password_hash('Kasir123!', PASSWORD_DEFAULT);
$role = 'kasir';

$cek = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);
$cek->bind_param("s", $email);
$cek->execute();

if ($cek->get_result()->num_rows > 0) {
    exit('Akun kasir sudah tersedia.');
}

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssss",
    $name,
    $email,
    $password,
    $role
);

if ($stmt->execute()) {
    echo "Akun kasir berhasil dibuat.";
} else {
    echo "Gagal membuat akun kasir.";
}
?>
```