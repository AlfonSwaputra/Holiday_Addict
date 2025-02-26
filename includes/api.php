<?php
session_start();

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header("Location: ../index.php");
    exit;
}

function getRecommendationsFromAPI($preferences) {
    // Buat hashtag berdasarkan preferensi user
    $hashtags = [];
    foreach ($preferences as $key => $value) {
        if ($value == 1) {
            $hashtags[] = $key;
        }
    }
    
    // Set hashtag untuk panggilan API
    $preferences['hashtag'] = !empty($hashtags) ? $hashtags[0] : 'wisata';
    
    $tiktokData = fetchFromTikTokAPI($preferences);
    var_dump($tiktokData); // Debug data TikTok
    $instagramData = fetchFromInstagramAPI($preferences);
    var_dump($instagramData); // Debug data Instagram

    return hybridRecommenderSystem($tiktokData, $instagramData, $preferences);
}


function fetchFromTikTokAPI($preferences) {
    $hashtags = array_keys(array_filter($preferences, function($value) {
        return $value == 1;
    }));
    
    $hashtag = !empty($hashtags) ? $hashtags[0] : 'wisata';
    
    // Panggil TikTok Scraper
    $output = shell_exec("node ../asset/js/tiktokScraper.js " . escapeshellarg($hashtag));
    $videos = json_decode($output, true);
    
    // Validasi data sebelum mapping
    if (!is_array($videos)) {
        return [];
    }
    
    return array_map(function($video) {
        return [
            'nama_wisata' => $video['desc'] ?? 'Destinasi Wisata',
            'categories' => extractCategories($video['desc'] ?? ''),
            'description' => $video['desc'] ?? '',
            'video_url' => $video['videoUrl'] ?? '',
            'thumbnail_url' => $video['thumbnailUrl'] ?? '',
            'source' => 'tiktok',
            'url' => $video['url'] ?? ''
        ];
    }, $videos);
}

function fetchFromInstagramAPI($preferences) {
    $hashtags = array_keys(array_filter($preferences, function($value) {
        return $value == 1;
    }));
    
    $hashtag = !empty($hashtags) ? $hashtags[0] : 'wisata';
    
    // Panggil Instagram Scraper
    $output = shell_exec("node ../asset/js/instagramScraper.js " . escapeshellarg($hashtag));
    $posts = json_decode($output, true);
    
    // Validasi data sebelum mapping
    if (!is_array($posts)) {
        return [];
    }
    
    return array_map(function($post) {
        return [
            'nama_wisata' => $post['caption'] ?? 'Destinasi Wisata',
            'categories' => extractCategories($post['caption'] ?? ''),
            'description' => $post['caption'] ?? '',
            'image_url' => $post['imageUrl'] ?? '',
            'source' => 'instagram',
            'url' => $post['url'] ?? ''
        ];
    }, $posts);
}

function hybridRecommenderSystem($tiktokData, $instagramData, $preferences) {
    // Inisialisasi array kosong jika data null
    $tiktokData = $tiktokData ?? [];
    $instagramData = $instagramData ?? [];
    
    $weightedPreferences = calculateCategoryWeight($preferences);
    $recommendations = [];

    foreach (array_merge($tiktokData, $instagramData) as $content) {
        $similarity = calculateSimilarity($weightedPreferences, $content['categories'] ?? []);
        if ($similarity > 0.6) {
            $content['similarity_score'] = $similarity;
            $recommendations[] = $content;
        }
    }

    usort($recommendations, function($a, $b) {
        return $b['similarity_score'] <=> $a['similarity_score'];
    });

    return $recommendations;
}

function calculateCategoryWeight($preferences) {
    $weights = [
        'alam' => 0.8,
        'budaya_sejarah' => 0.7,
        'pantai' => 0.75,
        'kota_belanja' => 0.6,
        'kuliner' => 0.85,
        'petualangan' => 0.9,
        'relaksasi' => 0.65
    ];
    
    $weightedPreferences = [];
    foreach ($preferences as $category => $value) {
        if (isset($weights[$category])) {
            $weightedPreferences[$category] = $value * $weights[$category];
        }
    }
    return $weightedPreferences;
}

function calculateSimilarity($userPreferences, $contentCategories) {
    $similarity = 0;
    $totalWeight = 0;

    foreach ($userPreferences as $category => $weight) {
        if (isset($contentCategories[$category])) {
            $similarity += $weight * $contentCategories[$category];
            $totalWeight += $weight;
        }
    }

    return $totalWeight > 0 ? $similarity / $totalWeight : 0;
}

function trackUserBehavior($userId, $contentId, $action) {
    global $conn;

    // Ensure that the $conn is properly initialized with the DB connection
    if (!$conn) {
        die('Database connection is not available.');
    }

    $stmt = $conn->prepare("INSERT INTO user_behavior (user_id, content_id, action, timestamp) 
                           VALUES (:user_id, :content_id, :action, NOW())");
    $stmt->execute([
        ':user_id' => $userId,
        ':content_id' => $contentId,
        ':action' => $action // view, like, share, etc
    ]);

    updateUserPreferences($userId, $contentId);
}

/**
 * Generates new recommendations for the given user.
 *
 * This function retrieves the user's preferences from the database, then calls the `getRecommendationsFromAPI()` function to generate new recommendations based on those preferences.
 *
 * @param int $userId The ID of the user to generate recommendations for.
 * @return array An array of recommended content.
 */
function generateNewRecommendations($userId) {
    global $conn;

    // Ambil preferensi user
    $stmt = $conn->prepare("SELECT * FROM preferences WHERE id_user = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dapatkan rekomendasi dari API
    return getRecommendationsFromAPI($preferences);
}

function getCachedRecommendations($userId) {
    // $cacheKey = "recommendations_" . $userId;
    // $cache = new Redis();

    // try {
    //     $cache->connect('127.0.0.1', 6379);
    // } catch (Exception $e) {
    //     die('Error connecting to Redis: ' . $e->getMessage());
    // }

    // $cachedData = $cache->get($cacheKey);
    // if ($cachedData) {
    //     return json_decode($cachedData, true);
    // }

    // $recommendations = generateNewRecommendations($userId);
    // $cache->setex($cacheKey, 3600, json_encode($recommendations)); // Cache for 1 hour

    // return $recommendations;
    return generateNewRecommendations($userId);
}

$recommendations = getCachedRecommendations($userId);

function getUserPreferences($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM preferences WHERE id_user = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateUserPreferences($userId, $contentId) {
    global $conn;
    
    // Ubah query untuk menggunakan id_content
    $stmt = $conn->prepare("SELECT categories FROM content WHERE id_content = :content_id");
    $stmt->execute([':content_id' => $contentId]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update preferensi user berdasarkan interaksi
    if ($content) {
        $categories = json_decode($content['categories'], true);
        foreach ($categories as $category) {
            $stmt = $conn->prepare("UPDATE preferences 
                                  SET $category = $category + 0.1 
                                  WHERE id_user = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        }
    }
}


if (empty($recommendations)) {
    $preferences = getUserPreferences($userId);
    $recommendations = getRecommendationsFromAPI($preferences);
}

// Track user view
trackUserBehavior($userId, 'view_recommendations', 'view');
?>
