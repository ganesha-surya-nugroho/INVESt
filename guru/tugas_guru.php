<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tugas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        header {
            background-color: #007BFF;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        main {
            padding: 20px;
        }

        h2 {
            color: #0056b3;
        }

        form {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        form input, form textarea, form select, form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        form button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }

        form button:hover {
            background-color: #0056b3;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            background: #ffffff;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        ul li strong {
            color: #333;
        }

        ul li a {
            color: #007BFF;
            text-decoration: none;
        }

        ul li a:hover {
            text-decoration: underline;
        }

        .tugas-penilaian form {
            display: flex;
            flex-direction: column;
        }

        .tugas-penilaian form input,
        .tugas-penilaian form textarea,
        .tugas-penilaian form button {
            margin-bottom: 10px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #007BFF;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #DC3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #a71d2a;
        }

        footer {
            text-align: center;
            padding: 10px;
            background: #f4f4f9;
            margin-top: 20px;
        }

        footer p {
            margin: 0;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <header>
        <h1>Kelola Tugas</h1>
    </header>
    <main>
        <!-- Form Buat Tugas Baru -->
        <h2>Buat Tugas Baru</h2>
        <form method="post">
            <label for="kelas_id">Kelas:</label>
            <select name="kelas_id" id="kelas_id" required>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="judul">Judul Tugas:</label>
            <input type="text" name="judul" id="judul" required>

            <label for="deskripsi">Deskripsi:</label>
            <textarea name="deskripsi" id="deskripsi" rows="4" required></textarea>

            <label for="tenggat_waktu">Tenggat Waktu:</label>
            <input type="datetime-local" name="tenggat_waktu" id="tenggat_waktu" required>

            <button type="submit" name="create_tugas">Buat Tugas</button>
        </form>

        <!-- Daftar Tugas -->
        <h2>Daftar Tugas</h2>
        <?php if (!empty($tugas)): ?>
            <ul>
                <?php foreach ($tugas as $t): ?>
                    <li>
                        <strong>Judul:</strong> <?= htmlspecialchars($t['judul']) ?><br>
                        <strong>Kelas:</strong> <?= htmlspecialchars($t['nama_kelas']) ?><br>
                        <strong>Tenggat Waktu:</strong> <?= htmlspecialchars($t['tenggat_waktu']) ?><br>
                        <a href="tugas_guru.php?tugas_id=<?= $t['tugas_id'] ?>" class="btn btn-primary">Lihat Pengumpulan</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Belum ada tugas yang dibuat.</p>
        <?php endif; ?>

        <!-- Tugas Terkumpul -->
        <?php if (isset($submissions)): ?>
            <h2>Tugas Terkumpul</h2>
            <?php if (!empty($submissions)): ?>
                <ul>
                    <?php foreach ($submissions as $s): ?>
                        <li>
                            <strong>Siswa:</strong> <?= htmlspecialchars($s['nama_siswa']) ?><br>
                            <strong>File:</strong> <a href="<?= htmlspecialchars($s['file']) ?>" target="_blank">Lihat</a><br>
                            <strong>Status Penilaian:</strong> <?= htmlspecialchars($s['status_penilaian']) ?><br>

                            <?php if ($s['status_penilaian'] === 'Belum Dinilai'): ?>
                                <form method="post" class="tugas-penilaian">
                                    <input type="hidden" name="submission_id" value="<?= $s['submission_id'] ?>">
                                    <input type="hidden" name="tugas_id" value="<?= $tugas_id ?>">
                                    <label for="nilai">Nilai:</label>
                                    <input type="number" name="nilai" id="nilai" required>

                                    <label for="feedback">Umpan Balik:</label>
                                    <textarea name="feedback" id="feedback" rows="2" required></textarea>

                                    <button type="submit" name="nilai_tugas" class="btn btn-primary">Beri Nilai</button>
                                </form>
                            <?php else: ?>
                                <strong>Nilai:</strong> <?= htmlspecialchars($s['nilai']) ?><br>
                                <strong>Umpan Balik:</strong> <?= htmlspecialchars($s['feedback']) ?><br>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Belum ada tugas yang dikumpulkan.</p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> Kelola Tugas</p>
    </footer>
</body>
</html>
