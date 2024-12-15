<?php
session_start();
require '../config.php';

// Cek apakah siswa sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.html");
    exit;
}

// Ambil data siswa dari sesi
$siswa_id = $_SESSION['siswa_id'];
$kelas = $_SESSION['kelas'];

// Ambil materi berdasarkan kelas atau tema
$query = $pdo->prepare("SELECT * FROM materi_pembelajaran WHERE kategori LIKE :kelas ORDER BY created_at DESC");
$query->execute(['kelas' => "%$kelas%"]);
$materi = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Pembelajaran - <?= htmlspecialchars($kelas) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #2563eb;
            --color-primary-light: #3b82f6;
            --color-background: #f4f7ff;
            --color-text-dark: #1f2937;
            --color-text-light: #6b7280;
            --color-white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--color-background);
            color: var(--color-text-dark);
            line-height: 1.6;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background-color: var(--color-primary);
            color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 600;
        }

        .user-info {
            font-weight: 300;
        }

        .materi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .materi-card {
            background-color: var(--color-white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(37, 99, 235, 0.1);
            box-shadow: var(--box-shadow);
        }

        .materi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.15);
        }

        .materi-card h2 {
            color: var(--color-primary);
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .materi-card .detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }

        .materi-card .kategori {
            color: var(--color-text-light);
            font-size: 0.875rem;
        }

        .btn {
            display: inline-block;
            background-color: var(--color-primary-light);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .btn:hover {
            background-color: var(--color-primary);
        }

        .empty-state {
            background-color: var(--color-white);
            border-radius: var(--border-radius);
            padding: 3rem;
            text-align: center;
            box-shadow: var(--box-shadow);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>Materi Pembelajaran</h1>
            <div class="user-info">
                Kelas: <?= htmlspecialchars($kelas) ?> | 
                <?= htmlspecialchars($_SESSION['siswa_id']) ?>
            </div>
        </div>

        <?php if (empty($materi)): ?>
            <div class="empty-state">
                <h2>Tidak Ada Materi Tersedia</h2>
                <p>Belum ada materi pembelajaran untuk kelas Anda saat ini.</p>
            </div>
        <?php else: ?>
            <div class="materi-grid">
                <?php foreach ($materi as $m): ?>
                    <div class="materi-card">
                        <h2><?= htmlspecialchars($m['judul']) ?></h2>
                        <div class="detail">
                            <div class="kategori"><?= htmlspecialchars($m['kategori']) ?></div>
                            <?php if ($m['tipe'] == 'Link'): ?>
                                <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="btn">Buka</a>
                            <?php else: ?>
                                <a href="../uploads/<?= htmlspecialchars($m['file_path']) ?>" download class="btn">Unduh</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>