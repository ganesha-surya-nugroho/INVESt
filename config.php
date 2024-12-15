<?php
$host = 'localhost'; // Ganti jika bukan 'localhost'
$dbname = 'chatschool'; // Ganti dengan nama database Anda
$username = 'root'; // Ganti jika berbeda
$password = ''; // Ganti jika ada password untuk user MySQL Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
