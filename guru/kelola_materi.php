<?php
require '../config.php';

// Pastikan koneksi database menggunakan error mode untuk debugging
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Ambil materi berdasarkan guru
    $guru_id = 1; // Sesuaikan dengan session login guru
    $query = $pdo->prepare("SELECT * FROM materi_pembelajaran WHERE guru_id = :guru_id");
    $query->execute(['guru_id' => $guru_id]);
    $materi = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Jika query gagal, tangkap error
    echo "Error: " . $e->getMessage();
    $materi = [];
}

// Hapus Materi
if (isset($_GET['delete_id'])) {
    try {
        $delete_id = $_GET['delete_id'];
        $delete_query = $pdo->prepare("DELETE FROM materi_pembelajaran WHERE id = :id");
        $delete_query->execute(['id' => $delete_id]);
        echo "<script>alert('Materi berhasil dihapus.'); window.location.href = 'kelola_materi.php';</script>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Update Materi (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];

    try {
        $update_query = $pdo->prepare("UPDATE materi_pembelajaran SET judul = :judul, deskripsi = :deskripsi, kategori = :kategori WHERE id = :id");
        $update_query->execute([
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'kategori' => $kategori,
            'id' => $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Materi berhasil diperbarui.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui materi.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Materi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        a {
            text-decoration: none;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #007BFF;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #f1f1f1;
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
            border-radius: 8px;
            z-index: 1000;
        }

        .modal.active {
            display: block;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }

        .modal h2 {
            margin: 0 0 20px;
            font-size: 20px;
            color: #333;
        }

        .modal label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .modal input, .modal textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .modal button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal .btn-save {
            background-color: #28a745;
            color: white;
        }

        .modal .btn-save:hover {
            background-color: #218838;
        }

        .modal .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .modal .btn-cancel:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>Kelola Materi</h1>
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="unggah_materi.php" class="btn btn-primary">Tambah Materi</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Tipe</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($materi) && is_array($materi)): ?>
                <?php foreach ($materi as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['judul']) ?></td>
                        <td><?= htmlspecialchars($m['kategori']) ?></td>
                        <td><?= htmlspecialchars($m['tipe']) ?></td>
                        <td><?= htmlspecialchars($m['file_path']) ?></td>
                        <td>
                            <button class="btn btn-primary edit-btn" 
                                    data-id="<?= $m['id'] ?>" 
                                    data-judul="<?= htmlspecialchars($m['judul']) ?>" 
                                    data-deskripsi="<?= htmlspecialchars($m['deskripsi']) ?>" 
                                    data-kategori="<?= htmlspecialchars($m['kategori']) ?>">Edit</button>
                            <a href="?delete_id=<?= $m['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada data materi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal Popup -->
    <div class="overlay"></div>
    <div class="modal" id="editModal">
        <h2>Edit Materi</h2>
        <form id="editForm">
            <input type="hidden" name="edit_id" id="edit_id">
            <label>Judul Materi:</label>
            <input type="text" name="judul" id="edit_judul" required>

            <label>Deskripsi:</label>
            <textarea name="deskripsi" id="edit_deskripsi" required></textarea>

            <label>Kategori:</label>
            <input type="text" name="kategori" id="edit_kategori" required>

            <button type="submit" class="btn-save">Simpan Perubahan</button>
            <button type="button" class="btn-cancel" id="closeModal">Batal</button>
        </form>
    </div>
    <script>
        // Modal JavaScript sama seperti sebelumnya
    </script>
</body>
</html>
