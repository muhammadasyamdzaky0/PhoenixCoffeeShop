
<?php
require 'config/koneksi.php';

$akun = [
    ['admin@phoenixcoffee.com', 'Admin123!'],
    ['kasir@phoenixcoffee.com', 'Kasir123!']
];

foreach ($akun as $data) {
    $email = $data[0];
    $hash = password_hash($data[1], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE users SET password = ? WHERE email = ?"
    );
    $stmt->bind_param("ss", $hash, $email);
    $stmt->execute();

    echo htmlspecialchars($email) . ': ';
    echo $stmt->affected_rows > 0
        ? 'Password berhasil diperbarui.<br>'
        : 'Akun tidak ditemukan atau password sudah sama.<br>';
}
?>
