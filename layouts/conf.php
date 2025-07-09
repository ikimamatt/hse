<?php
// Konfigurasi Database
$host       = 'localhost';      // Host database, biasanya 'localhost'
$db_name    = 'gdap'; // Nama database Anda
$username   = 'root';           // Username database
$password   = '';               // Password database

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);

    // Mengatur mode error PDO ke exception
    // Ini penting agar setiap kesalahan SQL akan melempar PDOException
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mengatur mode pengambilan data default ke associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // (Opsional) Baris di bawah ini biasanya tidak diperlukan di halaman lain
    // echo "Koneksi ke database '{$db_name}' berhasil.";

} catch (PDOException $e) {
    // Menangkap dan menampilkan error jika koneksi gagal
    die("Koneksi atau query bermasalah: " . $e->getMessage());
}
?>