<?php
require 'db.php';

// Registrasi Untuk Login
function register($data) {
    global $conn;

    $username = strtolower($data['usernameReg']);
    $email = $data['emailReg'];
    $password = $data["passwordReg"];
    $birthdate = $data['birthdate'];
    $gender = $data['gender'];

    // Cek email sudah terdaftar ?
    $stmt = $conn->prepare("SELECT email_user FROM users WHERE email_user = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        header("Location: register.php?error=email_exists");
        exit();
    }

    // Enkripsi password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (name_user, email_user, password_user, birthdate_user, gender_user) 
                            VALUES (:username, :email, :password, :birthdate, :gender)");
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hashedPassword);
    $stmt->bindParam(":birthdate", $birthdate);
    $stmt->bindParam(":gender", $gender);

    if ($stmt->execute()) {
        // Setelah registrasi berhasil, buat preferensi default
        $userId = $conn->lastInsertId();
        createDefaultPreferences($userId, $conn);
        return "success";
    }
    
    return "success";
}


// Fungsi untuk membuat preferensi default
function createDefaultPreferences($userId, $conn) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO preferences 
            (id_user, alam, budaya_sejarah, pantai, kota_belanja, kuliner, petualangan, relaksasi) 
            VALUES 
            (:user_id, 1, 1, 1, 1, 1, 1, 1)
        ");
        $stmt->execute([':user_id' => $userId]);
    } catch (PDOException $e) {
        error_log("Preferensi Default Error: " . $e->getMessage());
    }
}

// Fungsi Rekomendasi Hybrid yang Disempurnakan
function getHybridRecommendationsNew($user_id, $conn) {
    try {
        // Ambil preferensi user
        $stmt = $conn->prepare("SELECT * FROM preferences WHERE id_user = :user_id");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

        // Buat query berdasarkan preferensi
        $query = "SELECT * FROM wisata WHERE 1=1";
        $params = [];

        if ($preferences) {
            $categories = [];
            if ($preferences['alam']) $categories[] = 'alam';
            if ($preferences['budaya_sejarah']) $categories[] = 'budaya_sejarah';
            if ($preferences['pantai']) $categories[] = 'pantai';
            if ($preferences['kota_belanja']) $categories[] = 'kota_belanja';
            if ($preferences['kuliner']) $categories[] = 'kuliner';
            if ($preferences['petualangan']) $categories[] = 'petualangan';
            if ($preferences['relaksasi']) $categories[] = 'relaksasi';

            if (!empty($categories)) {
                $query .= " AND kategori IN (" . str_repeat('?,', count($categories) - 1) . "?)";
                $params = $categories;
            }
        }

        $query .= " ORDER BY RAND() LIMIT 9";
        
        $stmt = $conn->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in recommendations: " . $e->getMessage());
        return [];
    }
}


// Fungsi pencarian gambar wisata
function findImagesForWisata($nama_wisata, $defaultImage = '../asset/img/placeholder.jpg') {
    $images = [
        $defaultImage, 
        $defaultImage, 
        $defaultImage
    ];

    $possibleFileNames = [
        strtolower(str_replace(' ', '_', $nama_wisata)),
        strtolower(str_replace(' ', '-', $nama_wisata))
    ];

    $imageExtensions = ['jpg', 'png', 'jpeg'];
    $imageDirectories = [
        '../asset/img/wisata/',
        '../asset/img/',
    ];

    foreach ($imageDirectories as $dir) {
        foreach ($possibleFileNames as $baseName) {
            foreach ($imageExtensions as $ext) {
                for ($i = 1; $i <= 3; $i++) {
                    $fullPath = $dir . $baseName . ($i > 1 ? "_$i" : '') . '.' . $ext;
                    if (file_exists($fullPath)) {
                        $images[$i-1] = $fullPath;
                    }
                }
            }
        }
    }

    return $images;
}

function scrapeInstagramPosts($hashtag) {
    try {
        // Gunakan path absolut yang lebih robust
        $scriptPath = realpath(__DIR__ . '/../asset/js/instagramScraper.js');
        
        // Validasi keberadaan script
        if (!file_exists($scriptPath)) {
            throw new Exception("Instagram scraper script not found");
        }

        // Escape input untuk mencegah shell injection
        $safeHashtag = escapeshellarg($hashtag);
        $command = "node " . escapeshellarg($scriptPath) . " " . $safeHashtag;

        error_log("Executing Instagram scraper command: $command");

        // Eksekusi dengan timeout dan kontrol error
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes, null, null);

        if (!is_resource($process)) {
            throw new Exception("Failed to start Instagram scraper process");
        }

        // Baca output
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        // Tutup pipes
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        // Dapatkan status proses
        $returnValue = proc_close($process);

        // Log error jika ada
        if ($returnValue !== 0) {
            error_log("Instagram scraper error: $error");
            return [];
        }

        // Decode JSON output
        $posts = json_decode($output, true);

        // Validasi struktur posts
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg());
            return [];
        }

        // Filter posts valid
        $validPosts = array_filter($posts, function($post) {
            return 
                isset($post['imageUrl']) && 
                isset($post['caption']) && 
                !empty($post['imageUrl']) && 
                !empty($post['caption']);
        });

        error_log("Valid Instagram Posts for $hashtag: " . count($validPosts));

        return $validPosts;

    } catch (Exception $e) {
        error_log("Instagram Scraping Exception: " . $e->getMessage());
        return [];
    }
}

// Di dalam includes/function.php
function hybridFilter($socialPosts, $localPosts, $userPreferences) {
    $recommendations = [];
   
    foreach ($socialPosts as $socialPost) {
        foreach ($localPosts as $localPost) {
            // Gunakan fungsi calculateSimilarity yang baru
            $similarity = calculateSimilarity($socialPost, $localPost, $userPreferences);
           
            $recommendation = [
                'id_wisata' => $localPost['id_wisata'] ?? null,
                'nama_wisata' => $localPost['nama_wisata'],
                'description' => $localPost['description'],
                'image_url' => $socialPost['imageUrl'] ?? $localPost['image_url'] ?? '',
                'popularity' => calculatePopularity($socialPost, $localPost),
                'categories' => !empty($localPost['categories'])
                    ? explode(',', $localPost['categories'])
                    : []
            ];
           
            $recommendations[] = $recommendation;
        }
    }
   
    return $recommendations;
}

// Tambahkan di akhir file function.php

/**
 * Update popularitas objek wisata
 */
function updateTouristPopularity($conn, $wisataId, $incrementValue = 1) {
    try {
        $stmt = $conn->prepare("
            UPDATE wisata 
            SET popularity = popularity + :increment 
            WHERE id_wisata = :id
        ");
        $stmt->bindParam(":increment", $incrementValue, PDO::PARAM_INT);
        $stmt->bindParam(":id", $wisataId, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Popularity update error: " . $e->getMessage());
    }
}

/**
 * Catat interaksi pengguna
 */
function logUserInteraction($conn, $userId, $contentId, $action) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO user_behavior 
            (user_id, content_id, action) 
            VALUES (:user_id, :content_id, :action)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':content_id' => $contentId,
            ':action' => $action
        ]);
    } catch (PDOException $e) {
        error_log("User interaction log error: " . $e->getMessage());
    }
}

/**
 * Fungsi untuk menghitung similarity yang lebih kompleks
 */
function calculateSimilarity($socialPost, $localPost, $userPreferences) {
    $score = 0;
    
    // Cocokkan berdasarkan hashtag
    if (strpos($socialPost['hashtag'], $localPost['hashtag']) !== false) {
        $score += 5;
    }
    
    // Tambahkan logika pencocokan preferensi pengguna
    $categories = [
        'alam' => $userPreferences['alam'] ?? 0,
        'budaya_sejarah' => $userPreferences['budaya_sejarah'] ?? 0,
        'pantai' => $userPreferences['pantai'] ?? 0,
        // Tambahkan kategori lain sesuai kebutuhan
    ];
    
    foreach ($categories as $category => $preference) {
        if ($preference && strpos(strtolower($localPost['categories']), $category) !== false) {
            $score += 10;
        }
    }
    
    return $score;
}


// Di dalam includes/function.php
function getLocalTouristData($conn, $hashtag) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                id_wisata,
                nama_wisata, 
                deskripsi AS description, 
                kategori AS categories, 
                lokasi, 
                rating, 
                image_url, 
                hashtag,
                popularity
            FROM wisata 
            WHERE hashtag = :hashtag OR kategori LIKE :category
        ");

        $stmt->bindParam(":hashtag", $hashtag);
        $stmt->bindParam(":category", $hashtag);
        $stmt->execute();
       
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
        error_log("Local Tourist Data for $hashtag: " . print_r($results, true));
       
        return $results;
    } catch (PDOException $e) {
        error_log("Query error: " . $e->getMessage());
        return [];
    }
}


// Di dalam includes/function.php
function addOrUpdateTouristDestination($conn, $data) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO wisata 
            (nama_wisata, kategori, deskripsi, lokasi, rating, sosial_media, hashtag, image_url) 
            VALUES 
            (:nama, :kategori, :deskripsi, :lokasi, :rating, :sosial_media, :hashtag, :image_url)
            ON DUPLICATE KEY UPDATE
            deskripsi = :deskripsi,
            lokasi = :lokasi,
            rating = :rating,
            sosial_media = :sosial_media,
            hashtag = :hashtag,
            image_url = :image_url
        ");

        $stmt->execute([
            ':nama' => $data['nama_wisata'],
            ':kategori' => $data['kategori'],
            ':deskripsi' => $data['deskripsi'],
            ':lokasi' => $data['lokasi'],
            ':rating' => $data['rating'],
            ':sosial_media' => $data['sosial_media'],
            ':hashtag' => $data['hashtag'],
            ':image_url' => $data['image_url']
        ]);

        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Tourist destination insert/update error: " . $e->getMessage());
        return false;
    }
}

function getCachedRecommendations($user_id, $conn) {
    $cacheFile = __DIR__ . "/../cache/recommendations_{$user_id}.json";
    
    if (file_exists($cacheFile)) {
        $cacheTime = filemtime($cacheFile);
        // Changed to 10 minutes (600 seconds)
        if (time() - $cacheTime < 600) {
            return json_decode(file_get_contents($cacheFile), true);
        }
    }
    
    $recommendations = getHybridRecommendationsNew($user_id, $conn);
    
    // Create cache directory if it doesn't exist
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    
    file_put_contents($cacheFile, json_encode($recommendations));
    
    return $recommendations;
}

function addToFavorites($conn, $userId, $wisataId) {
    try {
        // Cek apakah wisata sudah ada di daftar favorite
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM user_favorites 
            WHERE user_id = :user_id AND wisata_id = :wisata_id
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jika belum ada di favorites, tambahkan
        if ($result['count'] == 0) {
            $insertStmt = $conn->prepare("
                INSERT INTO user_favorites (user_id, wisata_id, added_at) 
                VALUES (:user_id, :wisata_id, NOW())
            ");
            $insertStmt->execute([
                ':user_id' => $userId,
                ':wisata_id' => $wisataId
            ]);

            return true; // Berhasil ditambahkan
        }

        return false; // Sudah ada di favorites
    } catch (PDOException $e) {
        error_log("Add to favorites error: " . $e->getMessage());
        return false;
    }
}

function getFavorites($conn, $userId) {
    try {
        $stmt = $conn->prepare("
            SELECT w.* 
            FROM user_favorites uf
            JOIN wisata w ON uf.wisata_id = w.id_wisata
            WHERE uf.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get favorites error: " . $e->getMessage());
        return [];
    }
}

function trackUserInteraction($conn, $userId, $wisataId, $interactionType) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO user_interactions 
            (user_id, wisata_id, interaction_type) 
            VALUES (:user_id, :wisata_id, :interaction_type)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId,
            ':interaction_type' => $interactionType
        ]);
    } catch (PDOException $e) {
        error_log("Interaction tracking error: " . $e->getMessage());
    }
}

function addUserRating($conn, $userId, $wisataId, $rating, $review = null) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO user_ratings 
            (user_id, wisata_id, rating, review) 
            VALUES (:user_id, :wisata_id, :rating, :review)
            ON DUPLICATE KEY UPDATE 
            rating = :rating, 
            review = :review
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':wisata_id' => $wisataId,
            ':rating' => $rating,
            ':review' => $review
        ]);

        return true;
    } catch (PDOException $e) {
        error_log("Rating submission error: " . $e->getMessage());
        return false;
    }
}

function getWisataAnalytics($conn, $wisataId) {
    try {
        // Hitung total views
        $viewStmt = $conn->prepare("
            SELECT COUNT(*) as total_views 
            FROM user_interactions 
            WHERE wisata_id = :wisata_id AND interaction_type = 'view'
        ");
        $viewStmt->execute([':wisata_id' => $wisataId]);
        $views = $viewStmt->fetch(PDO::FETCH_ASSOC)['total_views'];

        // Hitung total favorites
        $favStmt = $conn->prepare("
            SELECT COUNT(*) as total_favorites 
            FROM user_interactions 
            WHERE wisata_id = :wisata_id AND interaction_type = 'favorite'
        ");
        $favStmt->execute([':wisata_id' => $wisataId]);
        $favorites = $favStmt->fetch(PDO::FETCH_ASSOC)['total_favorites'];

        // Hitung rating rata-rata
        $ratingStmt = $conn->prepare("
            SELECT 
                AVG(rating) as average_rating, 
                COUNT(*) as total_ratings 
            FROM user_ratings 
            WHERE wisata_id = :wisata_id
        ");
        $ratingStmt->execute([':wisata_id' => $wisataId]);
        $ratingData = $ratingStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_views' => $views,
            'total_favorites' => $favorites,
            'average_rating' => round($ratingData['average_rating'], 2),
            'total_ratings' => $ratingData['total_ratings']
        ];
    } catch (PDOException $e) {
        error_log("Wisata analytics error: " . $e->getMessage());
        return null;
    }
}

function validateUserPreferences($user_id, $conn) {
    try {
        // Periksa data preferensi
        $stmt = $conn->prepare("SELECT * FROM preferences WHERE id_user = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$preferences) {
            error_log("No preferences found for user $user_id. Inserting default preferences.");
            
            // Masukkan preferensi default jika tidak ada
            $insertStmt = $conn->prepare("
                INSERT INTO preferences 
                (id_user, alam, budaya_sejarah, pantai, kota_belanja, kuliner, petualangan, relaksasi) 
                VALUES 
                (:user_id, 0, 0, 0, 0, 0, 0, 0)
            ");
            $insertStmt->execute([':user_id' => $user_id]);
        }

        return $preferences;
    } catch (PDOException $e) {
        error_log("Preferences Validation Error: " . $e->getMessage());
        return null;
    }
}

function ensureUserPreferences($user_id, $conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM preferences WHERE id_user = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $insertStmt = $conn->prepare("
                INSERT INTO preferences 
                (id_user, alam, budaya_sejarah, pantai, kota_belanja, kuliner, petualangan, relaksasi) 
                VALUES 
                (:user_id, 1, 1, 1, 1, 1, 1, 1)
            ");
            $insertStmt->execute([':user_id' => $user_id]);
        }
    } catch (PDOException $e) {
        error_log("Preferences Validation Error: " . $e->getMessage());
    }
}

function bulkUpdateWisataImages($conn, $wisataData) {
    foreach ($wisataData as $wisata) {
        $baseName = strtolower(str_replace(' ', '_', $wisata['nama_wisata']));
        
        $images = [
            "../asset/img/wisata/{$baseName}.jpg",
            "../asset/img/wisata/{$baseName}_2.jpg",
            "../asset/img/wisata/{$baseName}_3.jpg"
        ];

        $updateStmt = $conn->prepare("
            UPDATE wisata 
            SET 
                image_url_1 = :image1, 
                image_url_2 = :image2, 
                image_url_3 = :image3 
            WHERE id_wisata = :wisata_id
        ");

        $updateStmt->execute([
            ':image1' => file_exists($images[0]) ? $images[0] : null,
            ':image2' => file_exists($images[1]) ? $images[1] : null,
            ':image3' => file_exists($images[2]) ? $images[2] : null,
            ':wisata_id' => $wisata['id_wisata']
        ]);
    }
}

function getUserFavorites($conn, $userId) {
    try {
        $stmt = $conn->prepare("
            SELECT wisata_id 
            FROM user_favorites 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Get user favorites error: " . $e->getMessage());
        return [];
    }
}

function isWisataFavorited($conn, $userId, $wisataId) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM user_favorites 
        WHERE user_id = :user_id AND wisata_id = :wisata_id
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':wisata_id' => $wisataId
    ]);
    return $stmt->fetchColumn() > 0;
}

function updateUserPreferences($conn, $userId, $categories) {
    try {
        // Hapus preferensi lama
        $deleteStmt = $conn->prepare("
            UPDATE preferences 
            SET alam = 0, 
                budaya_sejarah = 0, 
                pantai = 0, 
                kota_belanja = 0, 
                kuliner = 0, 
                petualangan = 0, 
                relaksasi = 0
            WHERE id_user = :user_id
        ");
        $deleteStmt->execute([':user_id' => $userId]);

        // Update dengan preferensi baru
        $stmt = $conn->prepare("
            UPDATE preferences
            SET alam = :alam,
                budaya_sejarah = :budaya_sejarah,
                pantai = :pantai,
                kota_belanja = :kota_belanja,
                kuliner = :kuliner,
                petualangan = :petualangan,
                relaksasi = :relaksasi
            WHERE id_user = :user_id
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':alam' => $categories['alam'],
            ':budaya_sejarah' => $categories['budaya_sejarah'],
            ':pantai' => $categories['pantai'],
            ':kota_belanja' => $categories['kota_belanja'],
            ':kuliner' => $categories['kuliner'],
            ':petualangan' => $categories['petualangan'],
            ':relaksasi' => $categories['relaksasi']
        ]);

        return true;
    } catch (PDOException $e) {
        error_log("Update preferences error: " . $e->getMessage());
        return false;
    }
}

?>
