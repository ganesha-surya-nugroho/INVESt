<?php
include '../config.php';
session_start();

// Cek sesi pengguna (pastikan pengguna sudah login)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login.html");
    exit;
}

// Ambil ID kelas dari URL
$kelas_id = $_GET['id'] ?? null;
if (!$kelas_id) {
    die("Kelas tidak ditemukan.");
}

// Tangani penambahan siswa ke kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $siswa_id = $_POST['siswa_id'];

    if ($siswa_id) {
        // Periksa apakah siswa sudah tergabung
        $query = $pdo->prepare("SELECT COUNT(*) FROM class_participants WHERE kelas_id = :kelas_id AND siswa_id = :siswa_id");
        $query->execute([
            'kelas_id' => $kelas_id,
            'siswa_id' => $siswa_id,
        ]);
        $isAlreadyInClass = $query->fetchColumn() > 0;

        if ($isAlreadyInClass) {
            echo "<p>Siswa sudah tergabung dalam kelas ini.</p>";
        } else {
            // Masukkan siswa ke kelas
            $query = $pdo->prepare("INSERT INTO class_participants (kelas_id, siswa_id) VALUES (:kelas_id, :siswa_id)");
            $query->execute([
                'kelas_id' => $kelas_id,
                'siswa_id' => $siswa_id,
            ]);
            header("Location: detail_kelas.php?id=" . $kelas_id);
            exit;
        }
    }
}

// Query untuk mengambil informasi kelas
$query = $pdo->prepare("SELECT * FROM classes WHERE id = :kelas_id");
$query->execute(['kelas_id' => $kelas_id]);
$class = $query->fetch(PDO::FETCH_ASSOC);
if (!$class) {
    die("Kelas tidak ditemukan.");
}

// Query untuk daftar siswa
$query = $pdo->prepare("
    SELECT users.nama AS siswa_nama, users.id AS siswa_id
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kelas</title>
    <link rel="stylesheet" href="../static/assets/css/detail_guru.css">
    <script>
        function openAddStudentPopup() {
            document.getElementById('addStudentPopup').style.display = 'block';
        }
        function closeAddStudentPopup() {
            document.getElementById('addStudentPopup').style.display = 'none';
        }
    </script>
</head>
<body>
    <h1>Detail Kelas: <?= htmlspecialchars($class['nama_kelas']) ?></h1>
    <p><?= htmlspecialchars($class['deskripsi']) ?></p>

    <h2>Daftar Siswa</h2>
    <ul>
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $student): ?>
                <li><?= htmlspecialchars($student['siswa_nama']) ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Tidak ada siswa di kelas ini.</p>
        <?php endif; ?>
    </ul>
    <button onclick="openAddStudentPopup()">Tambah Siswa</button>

    <div id="addStudentPopup">
        <div class="popup-content">
            <h3>Tambah Siswa ke Kelas</h3>
            <form method="POST">
                <label for="siswa_id">Pilih Siswa:</label>
                <select name="siswa_id" id="siswa_id" required>
                    <option value="">-- Pilih Siswa --</option>
                    <?php
                    $query = $pdo->prepare("
                        SELECT id, nama FROM users
                        WHERE role = 'siswa' AND id NOT IN (
                            SELECT siswa_id FROM class_participants WHERE kelas_id = :kelas_id
                        )
                    ");
                    $query->execute(['kelas_id' => $kelas_id]);
                    $availableStudents = $query->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($availableStudents as $student) {
                        echo '<option value="' . htmlspecialchars($student['id']) . '">' . htmlspecialchars($student['nama']) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit" name="add_student">Tambah</button>
            </form>
            <button onclick="closeAddStudentPopup()">Batal</button>
        </div>
    </div>

    
</body>
</html>
