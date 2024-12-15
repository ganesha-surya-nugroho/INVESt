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

// Ambil ID kelas dari URL
$kelas_id = $_GET['id'] ?? null;
if (!$kelas_id) {
    die("Kelas tidak ditemukan.");
}

// Periksa apakah siswa sudah bergabung di kelas
$checkQuery = $pdo->prepare("
    SELECT * FROM class_participants
    WHERE kelas_id = :kelas_id AND siswa_id = :siswa_id
");
$checkQuery->execute(['kelas_id' => $kelas_id, 'siswa_id' => $siswa_id]);

if ($checkQuery->rowCount() === 0) {
    die("Anda belum bergabung ke kelas ini.");
}

// Query untuk mengambil informasi kelas
$query = $pdo->prepare("SELECT * FROM classes WHERE id = :kelas_id");
$query->execute(['kelas_id' => $kelas_id]);
$class = $query->fetch(PDO::FETCH_ASSOC);
if (!$class) {
    die("Kelas tidak ditemukan.");
}

// Query untuk daftar siswa di kelas
$query = $pdo->prepare("
    SELECT users.nama AS siswa_nama
    FROM class_participants
    INNER JOIN users ON class_participants.siswa_id = users.id
    WHERE class_participants.kelas_id = :kelas_id
");
$query->execute(['kelas_id' => $kelas_id]);
$students = $query->fetchAll(PDO::FETCH_ASSOC);

// Query untuk pesan forum
$query = $pdo->prepare("
    SELECT users.nama AS pengirim, forum.pesan, forum.created_at
    FROM forum
    INNER JOIN users ON forum.pengirim_id = users.id
    WHERE forum.kelas_id = :kelas_id
    ORDER BY forum.created_at DESC
");
$query->execute(['kelas_id' => $kelas_id]);
$messages = $query->fetchAll(PDO::FETCH_ASSOC);

// Tangani pengiriman pesan forum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // Masukkan pesan ke tabel forum
    $query = $pdo->prepare("INSERT INTO forum (kelas_id, pengirim_id, pesan) VALUES (:kelas_id, :pengirim_id, :pesan)");
    $query->execute([
        'kelas_id' => $kelas_id,
        'pengirim_id' => $siswa_id,
        'pesan' => $message,
    ]);

    // Redirect kembali ke halaman ini
    header("Location: detail_kelas_siswa.php?id=" . $kelas_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kelas Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../static/assets/css/detail_kelas.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="header-title">
                <i class="fas fa-graduation-cap"></i>
                <?= isset($class['nama_kelas']) ? htmlspecialchars($class['nama_kelas']) : 'Kelas Tidak Ditemukan'; ?>
            </h1>
            <a href="kelas_siswa.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Daftar Kelas
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Informasi Kelas
                        </h2>
                    </div>
                    <div class="card-content">
                        <p><?= isset($class['deskripsi']) ? htmlspecialchars($class['deskripsi']) : 'Deskripsi tidak tersedia.'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Forum -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-users"></i>
                            Peserta Kelas
                        </h2>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($students)) : ?>
                            <ul>
                                <?php foreach ($students as $student) : ?>
                                    <li>
                                        <?= htmlspecialchars($student['siswa_nama']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <p>Tidak ada siswa yang terdaftar di kelas ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
