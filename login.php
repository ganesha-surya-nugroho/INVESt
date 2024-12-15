<?php 
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Ambil data user berdasarkan email
    $query = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $query->execute(['email' => $email]);
    $user = $query->fetch();

    // Verifikasi password
    if ($user && password_verify($password, $user['password'])) {
        // Simpan data login ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'siswa') {
            // Jika siswa, tambahkan data kelas ke session
            $_SESSION['siswa_id'] = $user['id'];
            $_SESSION['kelas'] = $user['kelas']; // Pastikan kolom kelas ada di database
            
            // Redirect ke dashboard siswa
            header("Location: dashboard_siswa.php");
        } elseif ($user['role'] === 'guru') {
            // Redirect ke dashboard guru
            header("Location: dashboard_guru.php");
        }
        exit;
    } else {
        $error = "Email atau password salah.";
    }
}
?>
