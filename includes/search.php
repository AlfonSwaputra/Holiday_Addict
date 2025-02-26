<?php
require 'db.php';

function searchWisata($keyword) {
    global $conn;

    // Query yang lebih spesifik, fokus pada nama_wisata dengan prioritas exact match
    $query = "
        SELECT * FROM wisata 
        WHERE nama_wisata LIKE :keyword_exact 
        ORDER BY 
            CASE 
                WHEN nama_wisata = :keyword_strict THEN 1 
                WHEN nama_wisata LIKE :keyword_start THEN 2 
                ELSE 3 
            END
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':keyword_exact' => "%$keyword%",
        ':keyword_strict' => $keyword,
        ':keyword_start' => "$keyword%"
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $results;
}

function handleSearch($keyword) {
    $results = searchWisata($keyword);

    if (empty($results)) {
        $_SESSION['search_alert'] = "Kata kunci yang Anda masukkan salah!";
        return [];
    }

    return $results;
}
?>
