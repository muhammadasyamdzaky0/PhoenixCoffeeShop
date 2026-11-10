
<?php
require 'config/koneksi.php';

$email = 'kasir@phoenixcoffee.com';
$password = 'Kasir123!';

$stmt = $conn->prepare(
    "SELECT password, role FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "Akun kasir tidak ditemukan di database ini.";
} elseif (password_verify($password, $user['password'])) {
    echo "Password cocok. Role: " .
         htmlspecialchars($user['role']);
} else {
    echo "Password tidak cocok dengan hash di database.";
}
?>
