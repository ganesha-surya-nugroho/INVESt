<?php
session_start();
include '../config.php';

// Cek apakah user sudah login dan memiliki role siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.html");
    exit;
}

// Ambil ID siswa dari sesi
$siswa_id = $_SESSION['user_id'];

// Ambil daftar tugas aktif
$query = $pdo->prepare("
    SELECT a.id AS tugas_id, a.judul, a.tenggat_waktu, 
        COALESCE(s.id, NULL) AS submission_id, 
        IF(s.id IS NULL, 'Belum Dikumpulkan', 'Sudah Dikumpulkan') AS status_pengumpulan
    FROM assignments a
    LEFT JOIN submissions s ON a.id = s.tugas_id AND s.siswa_id = :siswa_id
    WHERE a.kelas_id IN (
        SELECT kelas_id FROM class_participants WHERE siswa_id = :siswa_id
    )
    ORDER BY a.tenggat_waktu ASC
");
$query->execute(['siswa_id' => $siswa_id]);
$tugas_aktif = $query->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar tugas terkumpul
$query = $pdo->prepare("
    SELECT a.judul, s.file, s.status_penilaian, s.nilai, s.feedback
    FROM submissions s
    INNER JOIN assignments a ON s.tugas_id = a.id
    WHERE s.siswa_id = :siswa_id
    ORDER BY s.created_at DESC
");
$query->execute(['siswa_id' => $siswa_id]);
$tugas_terkumpul = $query->fetchAll(PDO::FETCH_ASSOC);

// Tangani pengunggahan tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $tugas_id = $_POST['tugas_id'];
    $upload_dir = '../uploads/';
    $file_name = basename($_FILES['file']['name']);
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $query = $pdo->prepare("
            INSERT INTO submissions (siswa_id, tugas_id, file) 
            VALUES (:siswa_id, :tugas_id, :file)
        ");
        $query->execute([
            'siswa_id' => $siswa_id,
            'tugas_id' => $tugas_id,
            'file' => $target_file,
        ]);

        header("Location: tugas_siswa.php");
        exit;
    } else {
        $error_message = "Gagal mengunggah file.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Tugas Siswa</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            color: #333;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #2563eb;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logout-btn {
            background-color: #dc2626;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.875rem;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #b91c1c;
        }

        /* Main Content Styles */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
        }

        .stat-blue {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .stat-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .stat-yellow {
            background-color: #fef9c3;
            color: #ca8a04;
        }

        .stat-info h3 {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            font-size: 1.5rem;
            font-weight: bold;
        }

        /* Section Styles */
        .section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .section-content {
            padding: 1.5rem;
        }

        /* Tasks Grid */
        .tasks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .task-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.25rem;
            transition: box-shadow 0.3s;
        }

        .task-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .task-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
        }

        .task-status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }

        .status-pending {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .status-submitted {
            background-color: #dcfce7;
            color: #166534;
        }

        /* File Upload Styles */
        .file-upload {
            border: 2px dashed #e5e7eb;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            margin-bottom: 1rem;
            transition: background-color 0.3s;
        }

        .file-upload:hover {
            background-color: #f9fafb;
        }

        .upload-icon {
            font-size: 2rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }

        .upload-text {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .submit-btn {
            width: 100%;
            background-color: #2563eb;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #1d4ed8;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .table th {
            background-color: #f9fafb;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .table tr:hover {
            background-color: #f9fafb;
        }

        .file-link {
            color: #2563eb;
            text-decoration: none;
        }

        .file-link:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .tasks-grid {
                grid-template-columns: 1fr;
            }

            .navbar {
                padding: 1rem;
            }

            .section-content {
                padding: 1rem;
            }
        }

        /* Empty State Styles */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }

        .empty-text {
            color: #6b7280;
        }
    </style>
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <i class="fas fa-graduation-cap"></i>
                Portal Tugas Siswa
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-blue">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <h3>Tugas Aktif</h3>
                    <p><?= count($tugas_aktif) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Tugas Terkumpul</h3>
                    <p><?= count($tugas_terkumpul) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-yellow">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Menunggu Penilaian</h3>
                    <p><?= count(array_filter($tugas_terkumpul, fn($t) => $t['status_penilaian'] !== 'Sudah Dinilai')) ?></p>
                </div>
            </div>
        </div>

        <!-- Tugas Aktif Section -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-clipboard-list" style="color: #2563eb;"></i>
                    Tugas Aktif
                </h2>
            </div>
            <div class="section-content">
                <?php if (!empty($tugas_aktif)): ?>
                    <div class="tasks-grid">
                        <?php foreach ($tugas_aktif as $tugas): ?>
                            <div class="task-card">
                                <div class="task-header">
                                    <h3 class="task-title"><?= htmlspecialchars($tugas['judul']) ?></h3>
                                    <span class="task-status <?= $tugas['status_pengumpulan'] === 'Sudah Dikumpulkan' ? 'status-submitted' : 'status-pending' ?>">
                                        <?= htmlspecialchars($tugas['status_pengumpulan']) ?>
                                    </span>
                                </div>
                                <p style="color: #6b7280; margin-bottom: 1rem;">
                                    <i class="far fa-clock"></i>
                                    Tenggat: <?= date('d M Y, H:i', strtotime($tugas['tenggat_waktu'])) ?>
                                </p>
                                <?php if ($tugas['status_pengumpulan'] === 'Belum Dikumpulkan'): ?>
                                    <form method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="tugas_id" value="<?= $tugas['tugas_id'] ?>">
                                        <label class="file-upload">
                                            <div class="upload-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <p class="upload-text">Klik atau seret file ke sini</p>
                                            <input type="file" name="file" style="display: none;" required>
                                        </label>
                                        <button type="submit" class="submit-btn">
                                            <i class="fas fa-upload"></i> Unggah Tugas
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <p class="empty-text">Tidak ada tugas aktif saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tugas Terkumpul Section -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-history" style="color: #16a34a;"></i>
                    Riwayat Tugas Terkumpul
                </h2>
            </div>
            <div class="section-content">
                <?php if (!empty($tugas_terkumpul)): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tugas</th>
                                    <th>File</th>
                                    <th>Status</th>
                                    <th>Nilai</th>
                                    <th>Umpan Balik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tugas_terkumpul as $tugas): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tugas['judul']) ?></td>
                                        <td>
                                            <a href="<?= htmlspecialchars($tugas['file']) ?>" class="file-link" target="_blank">
                                                <i class="fas fa-file"></i> Lihat File
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($tugas['status_penilaian']) ?></td>
                                        <td><?= $tugas['status_penilaian'] === 'Sudah Dinilai' ? htmlspecialchars($tugas['nilai']) : '-' ?></td>
                                        <td><?= $tugas['status_penilaian'] === 'Sudah Dinilai' ? htmlspecialchars($tugas['feedback']) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <p class="empty-text">Belum ada tugas yang dikumpulkan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk interaksi file upload -->
    <script>
        document.querySelectorAll('.file-upload').forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const text = upload.querySelector('.upload-text');
            
            upload.addEventListener('click', () => input.click());
            
            input.addEventListener('change', () => {
                if (input.files.length > 0) {
                    text.textContent = `File terpilih: ${input.files[0].name}`;
                } else {
                    text.textContent = 'Klik atau seret file ke sini';
                }
            });

            // Drag and drop functionality
            upload.addEventListener('dragover', (e) => {
                e.preventDefault();
                upload.style.backgroundColor = '#f9fafb';
            });

            upload.addEventListener('dragleave', (e) => {
                e.preventDefault();
                upload.style.backgroundColor = '';
            });

            upload.addEventListener('drop', (e) => {
                e.preventDefault();
                upload.style.backgroundColor = '';
                
                if (e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    text.textContent = `File terpilih: ${e.dataTransfer.files[0].name}`;
                }
            });
        });

        // Animasi smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Tampilkan pesan error jika ada
        <?php if (isset($error_message)): ?>
        alert('<?= htmlspecialchars($error_message) ?>');
        <?php endif; ?>
    </script>
</body>
</html>