<?php
session_start();
include 'config.php'; // Pastikan koneksi disertakan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Amankan dengan hashing
    $role = $_POST['role'];

    try {
        // Pastikan menggunakan variabel koneksi yang benar, `$pdo` jika menggunakan PDO
        $query = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, :role)");
        $query->execute([
            'nama' => $nama,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ]);

        echo "Registrasi berhasil!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
