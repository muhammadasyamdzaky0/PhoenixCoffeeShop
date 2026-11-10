# Phoenix Coffee Shop — Sistem Informasi Manajemen

Starter project PHP + MySQL untuk dijalankan secara lokal dengan XAMPP dan diedit melalui Visual Studio Code.

## Persyaratan
- XAMPP dengan Apache, MySQL/MariaDB, dan PHP
- Visual Studio Code
- Browser modern

## Cara menjalankan
1. Ekstrak folder `PhoenixCoffeeShop` ke folder `C:\xampp\htdocs\`.
2. Buka XAMPP Control Panel, lalu jalankan **Apache** dan **MySQL**.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih tab **Import**, pilih file `database.sql` dari folder proyek, lalu klik **Import/Go**. (File SQL membuat database `phoenix_coffee` otomatis.)
5. Buka folder `C:\xampp\htdocs\PhoenixCoffeeShop` di Visual Studio Code.
6. Buka browser dan kunjungi `http://localhost/PhoenixCoffeeShop/`.

## Halaman
- `index.php` — Dashboard
- `menu.php` — CRUD menu (tambah, edit, hapus, pencarian)
- `persediaan.php` — data stok, stok masuk/keluar, riwayat, peringatan stok menipis
- `transaksi.php` — pemilihan produk, kuantitas, pembayaran, kembalian, riwayat, dan pengurangan stok otomatis
- `laporan.php` — filter tanggal, rekap transaksi, produk terlaris, laporan stok, cetak/simpan PDF melalui browser

## Konfigurasi database
Edit `config/koneksi.php` jika username/password MySQL di komputer kamu berbeda. Default XAMPP biasanya username `root` dan password kosong.

## Catatan
- Produk contoh otomatis ditambahkan ketika tabel `products` masih kosong.
- Nilai dashboard dan laporan berasal dari data transaksi database; data akan mulai terisi setelah transaksi dibuat.
- Tombol **Cetak / Simpan PDF** membuka dialog cetak browser. Pilih **Save as PDF** untuk menyimpan laporan.
- Ini merupakan starter untuk tugas lokal, belum memiliki login multi-user, pembatasan akses role, dan validasi keamanan produksi.


## Fitur login dan role (versi tambahan)
- `login.php` — login semua pengguna
- `register.php` — pendaftaran customer
- `setup_admin.php` — membuat akun admin pertama (hanya aktif jika belum ada admin)
- `customer.php` — katalog produk untuk customer
- `keranjang.php` — keranjang dan checkout pesanan online
- `pesanan.php` — riwayat/status pesanan customer
- `pesanan_kasir.php` — kasir/admin memperbarui status pesanan online

### Cara mengaktifkan fitur tambahan
1. Cadangkan database `phoenix_coffee` terlebih dahulu.
2. Buka phpMyAdmin, pilih database `phoenix_coffee`, lalu gunakan tab **Import** untuk mengimpor kembali `config/database.sql`. Script menggunakan `CREATE TABLE IF NOT EXISTS`, sehingga tabel lama tidak dihapus; tabel `users`, `orders`, dan `order_items` akan ditambahkan.
3. Buka `http://localhost/PhoenixCoffeeShop/setup_admin.php` untuk membuat akun admin pertama. Buat password minimal 8 karakter.
4. Login melalui `http://localhost/PhoenixCoffeeShop/login.php`. Admin bisa mengakses halaman manajemen; kasir hanya halaman transaksi dan pesanan online; customer bisa mendaftar sendiri, melihat menu, checkout, dan memantau status pesanan.
5. Setelah admin pertama berhasil dibuat, `setup_admin.php` otomatis tidak dapat digunakan lagi. Untuk penggunaan publik/produksi, hapus file `setup_admin.php` setelah admin dibuat.

Catatan: pesanan online tidak langsung mengurangi stok saat checkout. Stok diperiksa dan dikurangi ketika kasir/admin mengubah status pesanan menjadi **Diproses**. Metode pembayaran pada fitur ini dicatat sebagai pilihan (Tunai/QRIS), bukan pemrosesan pembayaran online otomatis.
