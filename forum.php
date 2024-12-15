<?php
session_start();
include 'config.php';

// Cek apakah user sudah login dan memiliki peran yang valid
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login.html");
    exit;
}

// Ambil ID kelas dari URL
$kelas_id = $_GET['kelas_id'] ?? null;
if (!$kelas_id) {
    die("Kelas tidak ditemukan.");
}

// Tangani pengiriman pesan forum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $pengirim_id = $_SESSION['user_id'];

    if (!empty($message)) {
        try {
            $query = $pdo->prepare("INSERT INTO forum (kelas_id, pengirim_id, pesan, created_at) VALUES (:kelas_id, :pengirim_id, :pesan, NOW())");
            $query->execute([
                'kelas_id' => $kelas_id,
                'pengirim_id' => $pengirim_id,
                'pesan' => $message,
            ]);
            header("Location: forum.php?kelas_id=" . $kelas_id);
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Query untuk mendapatkan data kelas
$query = $pdo->prepare("SELECT * FROM classes WHERE id = :kelas_id");
$query->execute(['kelas_id' => $kelas_id]);
$class = $query->fetch(PDO::FETCH_ASSOC);
if (!$class) {
    die("Kelas tidak ditemukan.");
}

// Query untuk mendapatkan pesan di forum
$query = $pdo->prepare("
    SELECT users.nama AS pengirim, forum.pesan, forum.created_at
    FROM forum
    INNER JOIN users ON forum.pengirim_id = users.id
    WHERE forum.kelas_id = :kelas_id
    ORDER BY forum.created_at DESC
");
$query->execute(['kelas_id' => $kelas_id]);
$messages = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Diskusi Kelas</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Sesuaikan dengan file CSS -->
</head>
<body>
    <div class="container">
        <h1>Forum Diskusi: <?= htmlspecialchars($class['nama_kelas']) ?></h1>

        <div class="forum-messages">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message">
                        <p><strong><?= htmlspecialchars($message['pengirim']) ?>:</strong></p>
                        <p><?= nl2br(htmlspecialchars($message['pesan'])) ?></p>
                        <small><?= htmlspecialchars($message['created_at']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Belum ada pesan di forum ini.</p>
            <?php endif; ?>
        </div>

        <form method="post" class="forum-form">
            <textarea name="message" rows="4" placeholder="Tulis pesan..."></textarea>
            <button type="submit">Kirim</button>
        </form>

        <a href="detail_kelas.php?id=<?= $kelas_id ?>" class="back-link">Kembali ke Detail Kelas</a>
    </div>
</body>
</html>
