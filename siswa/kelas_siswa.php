<?php
// Memulai sesi dan menghubungkan database
session_start();
include '../config.php';


// Cek apakah user sudah login dan memiliki role siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.html");
    exit;
}

// Ambil ID siswa dari sesi
$siswa_id = $_SESSION['user_id'];

// Tangani permintaan bergabung ke kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kelas_id'])) {
    $kelas_id = $_POST['kelas_id'];

    // Periksa apakah siswa sudah terdaftar di kelas
    $checkQuery = $pdo->prepare("SELECT * FROM class_participants WHERE kelas_id = :kelas_id AND siswa_id = :siswa_id");
    $checkQuery->execute(['kelas_id' => $kelas_id, 'siswa_id' => $siswa_id]);

    if ($checkQuery->rowCount() === 0) {
        // Tambahkan siswa ke kelas
        $joinQuery = $pdo->prepare("INSERT INTO class_participants (kelas_id, siswa_id) VALUES (:kelas_id, :siswa_id)");
        $joinQuery->execute(['kelas_id' => $kelas_id, 'siswa_id' => $siswa_id]);

        $_SESSION['message'] = "Berhasil bergabung ke kelas!";
    } else {
        $_SESSION['message'] = "Anda sudah terdaftar di kelas ini.";
    }
}

// Ambil daftar kelas
$query = $pdo->prepare("SELECT * FROM classes");
$query->execute();
$classes = $query->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar kelas yang sudah diikuti siswa
$query = $pdo->prepare("
    SELECT classes.id, classes.nama_kelas, classes.deskripsi
    FROM class_participants
    INNER JOIN classes ON class_participants.kelas_id = classes.id
    WHERE class_participants.siswa_id = :siswa_id
");
$query->execute(['siswa_id' => $siswa_id]);
$enrolledClasses = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Siswa</title>
    <!-- CSS -->
     <link rel="stylesheet" href="../static/assets/css/kelas_siswa.css">
     <!-- FONT -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
    <!-- SIDEBAR -->
    <?php include '../header.php'; ?>

    <!-- MAIN -->
    <main>
    <h1>Daftar Kelas</h1>

    <!-- Pesan -->
    <?php if (isset($_SESSION['message'])): ?>
        <p><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <!-- Kelas yang Diikuti -->
    <h2>Kelas yang Anda Ikuti</h2>
    <div class="kelas-container">
    <?php foreach ($enrolledClasses as $class): ?>
    <li>
        <div class="kelas-card">
            <img src="../static/assets/img/Banner.png" alt="">
            <?= htmlspecialchars($class['nama_kelas']) ?> <br> <?= htmlspecialchars($class['deskripsi']) ?>
            <a href="detail_kelas_siswa.php?id=<?= $class['id'] ?>">Lihat Detail</a>
        </div>
        
    </li>
    <?php endforeach; ?>
    </div>
    </main>


    
</body>
</html>
