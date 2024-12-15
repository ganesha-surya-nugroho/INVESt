<?php
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];
    $tipe = $_POST['tipe'];
    $guru_id = 1; // Sesuaikan dengan session login guru
    $file_path = null;
    $link = null;

    // Proses unggah file jika tipe adalah dokumen atau video
    if ($tipe === 'dokumen' || $tipe === 'video') {
        if (isset($_FILES['file']['name']) && $_FILES['file']['error'] === 0) {
            $upload_dir = '../uploads/';
            $file_name = basename($_FILES['file']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                $file_path = $file_name;
            } else {
                echo "Gagal mengunggah file.";
                exit;
            }
        }
    } elseif ($tipe === 'link') {
        $link = $_POST['link'];
    }

    // Simpan ke database
    $query = $pdo->prepare("INSERT INTO materi_pembelajaran (judul, deskripsi, kategori, tipe, file_path, link, guru_id) 
                            VALUES (:judul, :deskripsi, :kategori, :tipe, :file_path, :link, :guru_id)");
    $query->execute([
        'judul' => $judul,
        'deskripsi' => $deskripsi,
        'kategori' => $kategori,
        'tipe' => $tipe,
        'file_path' => $file_path,
        'link' => $link,
        'guru_id' => $guru_id
    ]);

    echo "Materi berhasil diunggah.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah Materi</title>
    <link rel="stylesheet" href="../static/assets/css/unggah_materi.css">
</head>
<body>
    <h1>Unggah Materi</h1>
    <form method="POST" enctype="multipart/form-data">
        <label>Judul Materi:</label><br>
        <input type="text" name="judul" required><br><br>

        <label>Deskripsi:</label><br>
        <textarea name="deskripsi" required></textarea><br><br>

        <label>Kategori:</label><br>
        <input type="text" name="kategori" required><br><br>

        <label>Tipe:</label><br>
        <select name="tipe" required>
            <option value="dokumen">Dokumen</option>
            <option value="video">Video</option>
            <option value="link">Link</option>
        </select><br><br>

        <div id="file_input">
            <label>Unggah File:</label><br>
            <input type="file" name="file"><br><br>
        </div>

        <div id="link_input" style="display: none;">
            <label>Link Pembelajaran:</label><br>
            <input type="url" name="link"><br><br>
        </div>

        <button type="submit">Unggah Materi</button>
    </form>

    <script>
        const tipeSelect = document.querySelector('select[name="tipe"]');
        const fileInput = document.getElementById('file_input');
        const linkInput = document.getElementById('link_input');

        tipeSelect.addEventListener('change', function() {
            if (this.value === 'link') {
                fileInput.style.display = 'none';
                linkInput.style.display = 'block';
            } else {
                fileInput.style.display = 'block';
                linkInput.style.display = 'none';
            }
        });
    </script>
</body>
</html>
