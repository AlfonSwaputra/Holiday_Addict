<?php
session_start();
require 'db.php';
require 'function.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

// Ambil user ID dari session
$user_id = $_SESSION['user']['id'];

// Tangkap data preferensi dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil kategori yang dipilih
    $selected_categories = $_POST['categories'] ?? [];

    try {
        // Mulai transaksi
        $conn->beginTransaction();

        // Hapus preferensi lama
        $stmt_delete = $conn->prepare("DELETE FROM preferences WHERE id_user = :user_id");
        $stmt_delete->bindParam(':user_id', $user_id);
        $stmt_delete->execute();

        // Masukkan preferensi baru
        $stmt_insert = $conn->prepare("
            UPDATE preferences 
            SET 
                wisata_alam = :wisata_alam,
                wisata_budaya = :wisata_budaya,
                wisata_sejarah = :wisata_sejarah,
                wisata_kuliner = :wisata_kuliner,
                wisata_belanja = :wisata_belanja
            WHERE id_user = :user_id
        ");

        // Set nilai berdasarkan kategori yang dipilih
        $stmt_insert->bindValue(':wisata_alam', in_array('wisata_alam', $selected_categories) ? 1 : 0);
        $stmt_insert->bindValue(':wisata_budaya', in_array('wisata_budaya', $selected_categories) ? 1 : 0);
        $stmt_insert->bindValue(':wisata_sejarah', in_array('wisata_sejarah', $selected_categories) ? 1 : 0);
        $stmt_insert->bindValue(':wisata_kuliner', in_array('wisata_kuliner', $selected_categories) ? 1 : 0);
        $stmt_insert->bindValue(':wisata_belanja', in_array('wisata_belanja', $selected_categories) ? 1 : 0);
        $stmt_insert->bindParam(':user_id', $user_id);
        $stmt_insert->execute();

        // Commit transaksi
        $conn->commit();

        // Redirect ke halaman home atau dashboard
        header("Location: ../pages/home.php");
        exit;

    } catch (PDOException $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollBack();
        
        // Tampilkan pesan error
        error_log("Error processing preferences: " . $e->getMessage());
        
        // Redirect kembali ke halaman preferensi dengan pesan error
        header("Location: ../pages/first-preference.php?error=1");
        exit;
    }
} else {
    // Jika bukan metode POST, redirect kembali
    header("Location: ../pages/first-preference.php");
    exit;
}
?>