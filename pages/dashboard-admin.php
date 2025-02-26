
<?php
session_start();
require '../includes/db.php';
require '../includes/function.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Proses penambahan wisata
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $wisataData = [
        'nama_wisata' => $_POST['nama_wisata'],
        'kategori' => $_POST['kategori'],
        'deskripsi' => $_POST['deskripsi'],
        'lokasi' => $_POST['lokasi'],
        'rating' => $_POST['rating'],
        'sosial_media' => $_POST['sosial_media'],
        'hashtag' => $_POST['hashtag'],
        'image_url' => $_POST['image_url']
    ];

    $wisataId = addOrUpdateTouristDestination($conn, $wisataData);

    if ($wisataId) {
        $successMessage = "Wisata berhasil ditambahkan/diupdate";
    } else {
        $errorMessage = "Gagal menambahkan/mengupdate wisata";
    }
}
?>

<!-- Form untuk menambahkan/mengupdate wisata -->
<form method="POST">
    <input type="text" name="nama_wisata" placeholder="Nama Wisata" required>
    <select name="kategori" required>
        <option value="alam">Alam</option>
        <option value="budaya_sejarah">Budaya & Sejarah</option>
        <option value="pantai">Pantai</option>
        <option value="kota_belanja">Kota & Belanja</option>
        <option value="kuliner">Kuliner</option>
        <option value="petualangan">Petualangan</option>
        <option value="relaksasi">Relaksasi</option>
    </select>
    <textarea name="deskripsi" placeholder="Deskripsi" required></textarea>
    <input type="text" name="lokasi" placeholder="Lokasi" required>
    <input type="number" name="rating" step="0.1" min="0" max="5" placeholder="Rating">
    <input type="text" name="sosial_media" placeholder="Akun Sosial Media">
    <input type="text" name="hashtag" placeholder="Hashtag">
    <input type="url" name="image_url" placeholder="URL Gambar">
    <button type="submit">Simpan Wisata</button>
</form>
