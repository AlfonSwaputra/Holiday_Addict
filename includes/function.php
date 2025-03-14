<?php
require 'db.php';

if (!function_exists('register')) {
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
}

// Fungsi untuk membuat preferensi default
if (!function_exists('initializeUserPreferences')) {
    function initializeUserPreferences($userId) {
        global $conn;
        
        try {
            // Cek preferensi yang ada
            $stmt = $conn->prepare("SELECT COUNT(*) FROM preferences WHERE id_user = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $exists = $stmt->fetchColumn();
    
            if (!$exists) {
                // Buat preferensi default
                $stmt = $conn->prepare("
                    INSERT INTO preferences 
                    (id_user, alam, budaya_sejarah, pantai, kota_belanja, kuliner, petualangan, relaksasi) 
                    VALUES 
                    (:user_id, 1, 1, 1, 1, 1, 1, 1)
                ");
                $stmt->execute([':user_id' => $userId]);
            }
    
            return true;
        } catch (PDOException $e) {
            error_log("Initialize preferences error: " . $e->getMessage());
            return false;
        }
    }
}

// Fungsi Rekomendasi Hybrid yang Disempurnakan
if (!function_exists('getHybridRecommendationsNew')) {
    function getHybridRecommendationsNew($userId) {
        global $conn;
        
        try {
            // Ambil preferensi user
            $stmt = $conn->prepare("SELECT * FROM preferences WHERE id_user = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Buat query berdasarkan preferensi dan jumlah favorite
            $query = "
                SELECT w.*, COUNT(uf.id_favorite) as favorite_count 
                FROM wisata w 
                LEFT JOIN user_favorites uf ON w.id_wisata = uf.wisata_id 
                WHERE 1=1
            ";
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
    
            $query .= " GROUP BY w.id_wisata ORDER BY favorite_count DESC, RAND()";
            
            $stmt = $conn->prepare($query);
            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error dalam rekomendasi: " . $e->getMessage());
            return [];
        }
    }    
}

// Fungsi pencarian gambar wisata
if (!function_exists('findImagesForWisata')) {
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
}

if (!function_exists('scrapeInstagramPosts')) {
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
}

// Di dalam includes/function.php
if (!function_exists('hybridFilter')) {
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
}

// Update popularitas objek wisata
if (!function_exists('updateTouristPopularity')) {
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
}


// Catat interaksi pengguna
if (!function_exists('logUserInteraction')) {
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
}

// Fungsi untuk menghitung similarity yang lebih kompleks
if (!function_exists('calculateSimilarity')) {
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
}

// Di dalam includes/function.php
if (!function_exists('getLocalTouristData')) {
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
}

// Di dalam includes/function.php
if (!function_exists('addOrUpdateTouristDestination')) {
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
}

if (!function_exists('getCachedRecommendations')) {
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
}

if (!function_exists('addToFavorites')) {
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
}

if (!function_exists('getFavorites')) {
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
}

if (!function_exists('trackUserActivity')) {
    function trackUserActivity($userId, $contentId, $type, $action) {
        global $conn;
        
        try {
            // Insert ke tabel user_interactions
            $stmt = $conn->prepare("
                INSERT INTO user_interactions 
                (user_id, wisata_id, interaction_type, interaction_data, created_at) 
                VALUES (:user_id, :content_id, :type, :action, NOW())
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':content_id' => $contentId,
                ':type' => $type,
                ':action' => $action
            ]);
    
            // Update popularitas wisata
            if ($type === 'view' || $type === 'favorite') {
                $updateStmt = $conn->prepare("
                    UPDATE wisata 
                    SET popularity = popularity + 1 
                    WHERE id_wisata = :content_id
                ");
                $updateStmt->execute([':content_id' => $contentId]);
            }
    
            return true;
        } catch (PDOException $e) {
            error_log("Track activity error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('addUserRating')) {
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
}

if (!function_exists('getWisataAnalytics')) {
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
}

if (!function_exists('validateUserPreferences')) {
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
}

if (!function_exists('bulkUpdateWisataImages')) {
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
}

if (!function_exists('getUserFavorites')) {
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
}

if (!function_exists('isWisataFavorited')) {
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
}

if (!function_exists('updateUserPreferences')) {
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
}

// Fungsi untuk Rekomendasi Terbaik (Collaborative Filtering)
if (!function_exists('getTopRecommendations')) {
    function getTopRecommendations($conn, $userId) {
        try {
            // 1. Ambil pengguna dengan preferensi serupa
            $stmt = $conn->prepare("
                SELECT u2.id_user 
                FROM users u1
                JOIN preferences p1 ON u1.id_user = p1.id_user
                JOIN preferences p2 ON 
                    p1.alam = p2.alam AND 
                    p1.pantai = p2.pantai AND
                    p1.budaya_sejarah = p2.budaya_sejarah
                JOIN users u2 ON p2.id_user = u2.id_user
                WHERE u1.id_user = :user_id AND u2.id_user != :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $similarUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 2. Ambil destinasi populer dari pengguna serupa
            $stmt = $conn->prepare("
                SELECT w.*, 
                       COUNT(ur.rating) as total_ratings,
                       AVG(ur.rating) as avg_rating
                FROM wisata w
                JOIN user_ratings ur ON w.id_wisata = ur.wisata_id
                WHERE ur.user_id IN (" . implode(',', $similarUsers) . ")
                GROUP BY w.id_wisata
                HAVING avg_rating >= 4.0
                ORDER BY total_ratings DESC, avg_rating DESC
                LIMIT 6
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Top recommendations error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getPreferenceBasedRecommendations')) {
    function getPreferenceBasedRecommendations($conn, $userId) {
        try {
            // Get user preferences
            $stmt = $conn->prepare("
                SELECT * FROM preferences 
                WHERE id_user = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

            // Build category conditions
            $categories = [];
            $params = [':user_id' => $userId];
            
            if ($preferences['alam']) $categories[] = 'alam';
            if ($preferences['budaya_sejarah']) $categories[] = 'budaya_sejarah';
            if ($preferences['pantai']) $categories[] = 'pantai';
            if ($preferences['kota_belanja']) $categories[] = 'kota_belanja';
            if ($preferences['kuliner']) $categories[] = 'kuliner';
            if ($preferences['petualangan']) $categories[] = 'petualangan';
            if ($preferences['relaksasi']) $categories[] = 'relaksasi';

            // Get recommendations based on preferences
            $query = "
                SELECT w.*, 
                       COUNT(uf.wisata_id) as favorite_count,
                       AVG(ur.rating) as avg_rating
                FROM wisata w
                LEFT JOIN user_favorites uf ON w.id_wisata = uf.wisata_id
                LEFT JOIN user_ratings ur ON w.id_wisata = ur.wisata_id
                WHERE w.kategori IN ('" . implode("','", $categories) . "')
                GROUP BY w.id_wisata
                ORDER BY favorite_count DESC, avg_rating DESC
                LIMIT 6
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Preference-based recommendations error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('cacheRecommendations')) {
    function cacheRecommendations($userId, $data, $duration = 3600) {
        $cacheFile = "../cache/recommendations_{$userId}.json";
        file_put_contents($cacheFile, json_encode($data));
    }
}

if (!function_exists('getRecommendationsFromCache')) {
    function getRecommendationsFromCache($userId) {
        $cacheFile = "../cache/recommendations_{$userId}.json";
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        return null;
    }
}

if (!function_exists('checkRateLimit')) {
    function checkRateLimit($userId, $endpoint, $limit = 100) {
        $key = "rate_limit:{$userId}:{$endpoint}";
        $count = isset($_SESSION[$key]) ? $_SESSION[$key] : 0;
        
        if ($count >= $limit) {
            http_response_code(429);
            die(json_encode(['error' => 'Too many requests']));
        }
        
        $_SESSION[$key] = $count + 1;
    }
}

if (!function_exists('logFirebaseError')) {
    function logFirebaseError() {
        $data = json_decode(file_get_contents('php://input'), true);
        $logMessage = sprintf(
            "[%s] === Firebase %s Error ===\nCode: %s\nMessage: %s\nStack Trace:\n%s\n\n",
            $data['timestamp'],
            $data['action'], 
            $data['error_code'],
            $data['error_message'],
            $data['stack_trace']
        );
        file_put_contents('error_log.txt', $logMessage, FILE_APPEND);
    }
}

if (!function_exists('processUserPreferences')) {
    function processUserPreferences($user_id, $selected_categories) {
        global $conn;
        
        try {
            $conn->beginTransaction();

            // Reset preferensi lama
            $stmt_delete = $conn->prepare("DELETE FROM preferences WHERE id_user = :user_id");
            $stmt_delete->bindParam(':user_id', $user_id);
            $stmt_delete->execute();

            // Insert preferensi baru
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

            // Binding nilai preferensi
            $stmt_insert->bindValue(':wisata_alam', in_array('wisata_alam', $selected_categories) ? 1 : 0);
            $stmt_insert->bindValue(':wisata_budaya', in_array('wisata_budaya', $selected_categories) ? 1 : 0);
            $stmt_insert->bindValue(':wisata_sejarah', in_array('wisata_sejarah', $selected_categories) ? 1 : 0);
            $stmt_insert->bindValue(':wisata_kuliner', in_array('wisata_kuliner', $selected_categories) ? 1 : 0);
            $stmt_insert->bindValue(':wisata_belanja', in_array('wisata_belanja', $selected_categories) ? 1 : 0);
            $stmt_insert->bindParam(':user_id', $user_id);
            $stmt_insert->execute();

            $conn->commit();
            return true;

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error processing preferences: " . $e->getMessage());
            return false;
        }
    }
}
?>