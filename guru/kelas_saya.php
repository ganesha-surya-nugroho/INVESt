<?php
include '../config.php';
session_start();


// Periksa apakah user sudah login dan role-nya adalah guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login.html");
    exit;
}

// Tangani form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kelas = $_POST['nama_kelas'];
    $deskripsi = $_POST['deskripsi'];
    $guru_id = $_SESSION['user_id'];

    // Validasi input
    if (empty($nama_kelas)) {
        $error = "Nama kelas tidak boleh kosong.";
    } else {
        // Simpan ke database
        $query = $pdo->prepare("INSERT INTO classes (nama_kelas, deskripsi, guru_id) VALUES (:nama_kelas, :deskripsi, :guru_id)");
        $query->execute([
            'nama_kelas' => $nama_kelas,
            'deskripsi' => $deskripsi,
            'guru_id' => $guru_id
        ]);

        // Redirect untuk mencegah form re-submission
        header("Location: kelas_saya.php");
        exit;
    }
}

// Ambil daftar kelas yang dikelola oleh guru
$query = $pdo->prepare("SELECT * FROM classes WHERE guru_id = :guru_id");
$query->execute(['guru_id' => $_SESSION['user_id']]);
$classes = $query->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelas Saya</title>
    <link rel="stylesheet" href="../static/assets/css/kelas_siswa.css">
    <style>
        /* Gaya untuk modal popup */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #openModal {
            width: 160px;
            height: 50px;
            padding: 10px 20px;
            color: #fff;
            border-radius: 8px;
            border: none;
            background-color: #007BFF;
            cursor: pointer;
        }

        .col {
            margin-left: 80px;
            margin-top: 30px;
            display : flex;
            flex-direction: column;
            gap: 15px;
        }
    </style>
    
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="col">
        
    <h1>Kelas Saya</h1>

<!-- Tombol untuk membuka modal -->
<button id="openModal">Tambah Kelas</button>

<!-- Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Tambah Kelas Baru</h2>
        <form method="POST" action="">
            <label for="nama_kelas">Nama Kelas:</label><br>
            <input type="text" id="nama_kelas" name="nama_kelas" required><br><br>

            <label for="deskripsi">Deskripsi:</label><br>
            <textarea id="deskripsi" name="deskripsi"></textarea><br><br>

            <button type="submit">Simpan</button>
        </form>
    </div>
</div>

<!-- Daftar kelas -->
<?php if (!empty($classes)): ?>
    <div class="kelas-container">
        <?php foreach ($classes as $class): ?>
            <li>
                <div class="kelas-card">
                <img src="../static/assets/img/Banner.png" alt="">
                <h2><?= htmlspecialchars($class['nama_kelas']) ?></h2>
                <p><?= htmlspecialchars($class['deskripsi']) ?></p>
                <a href="detail_kelas.php?id=<?= $class['id'] ?>">Lihat Detail</a>
                </div>
            </li>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Anda belum memiliki kelas.</p>
<?php endif; ?>

    </div>
    <script>
        // JavaScript untuk mengontrol modal
        const modal = document.getElementById('myModal');
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementsByClassName('close')[0];

        openModalBtn.onclick = function() {
            modal.style.display = 'block';
        }

        closeModalBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

