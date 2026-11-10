
<?php
require 'config/koneksi.php';

$email = 'kasir@phoenixcoffee.com';
$password = password_hash('Kasir123!', PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "UPDATE users SET password = ?, role = 'kasir'
     WHERE email = ?"
);
$stmt->bind_param("ss", $password, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Password kasir berhasil diperbarui.";
} else {
    echo "Akun tidak ditemukan atau password sudah sama.";
}
?>
