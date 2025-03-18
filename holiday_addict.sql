-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2025 at 05:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `holiday_addict`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetWisataRecommendations` (IN `userId` INT)   BEGIN
    SELECT w.* 
    FROM wisata w
    JOIN preferences p ON p.id_user = userId
    WHERE (
        (p.alam = 1 AND w.kategori = 'alam') OR
        (p.pantai = 1 AND w.kategori = 'pantai') OR
        (p.budaya_sejarah = 1 AND w.kategori = 'budaya_sejarah')
    )
    ORDER BY w.rating DESC
    LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateWisataRating` (IN `wisataId` INT)   BEGIN
    UPDATE wisata w
    SET w.rating = (
        SELECT AVG(CAST(JSON_EXTRACT(ur.ratings_data, CONCAT('$."', wisataId, '"')) AS DECIMAL(3,2)))
        FROM user_ratings ur
        WHERE JSON_EXTRACT(ur.ratings_data, CONCAT('$."', wisataId, '"')) IS NOT NULL
    )
    WHERE w.id_wisata = wisataId;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id_content` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`categories`)),
  `source` varchar(50) DEFAULT NULL,
  `content_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_detail`
--

CREATE TABLE `kategori_detail` (
  `id_kategori_detail` int(11) NOT NULL,
  `kategori` enum('alam','budaya_sejarah','pantai','kota_belanja','kuliner','petualangan','relaksasi') NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `deskripsi_kategori` text DEFAULT NULL,
  `rekomendasi_kunjungan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preferences`
--

CREATE TABLE `preferences` (
  `id_preferensi` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `alam` tinyint(1) DEFAULT NULL,
  `budaya_sejarah` tinyint(1) DEFAULT NULL,
  `pantai` tinyint(1) DEFAULT NULL,
  `kota_belanja` tinyint(1) DEFAULT NULL,
  `kuliner` tinyint(1) DEFAULT NULL,
  `petualangan` tinyint(1) DEFAULT NULL,
  `relaksasi` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `preferences`
--

INSERT INTO `preferences` (`id_preferensi`, `id_user`, `alam`, `budaya_sejarah`, `pantai`, `kota_belanja`, `kuliner`, `petualangan`, `relaksasi`) VALUES
(69, 116, 1, 1, 1, 0, 0, 0, 0),
(70, 117, 0, 1, 1, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `id_recommendation` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `name_user` varchar(255) DEFAULT NULL,
  `email_user` varchar(100) NOT NULL,
  `password_user` varchar(255) NOT NULL,
  `birthdate_user` date NOT NULL,
  `gender_user` enum('male','female') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `name_user`, `email_user`, `password_user`, `birthdate_user`, `gender_user`) VALUES
(116, 'Altar Weasley', 'altarweasley@gmail.com', '$2y$10$MY5eFtJt5pcDKvDqe0rRbul8U2jUTfCwccZ//cT.90UCvhjG8YUHe', '1998-02-05', 'male'),
(117, 'Alfon Swaputra', 'alfonswaputra3@gmail.com', '', '0000-00-00', 'male');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id_favorite` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `wisata_id` int(11) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favorites`
--

INSERT INTO `user_favorites` (`id_favorite`, `user_id`, `wisata_id`, `added_at`) VALUES
(23, 116, 53, '2025-03-12 22:14:32'),
(32, 117, 52, '2025-03-15 21:35:19'),
(60, 117, 53, '2025-03-15 21:46:54'),
(86, 117, 13, '2025-03-17 21:30:52');

-- --------------------------------------------------------

--
-- Table structure for table `user_interactions`
--

CREATE TABLE `user_interactions` (
  `id_interaction` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `wisata_id` int(11) DEFAULT NULL,
  `interaction_type` enum('view','favorite','share','rating') DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_interactions`
--

INSERT INTO `user_interactions` (`id_interaction`, `user_id`, `wisata_id`, `interaction_type`, `timestamp`) VALUES
(1, 116, 14, 'favorite', '2025-03-13 16:24:09'),
(2, 116, 24, 'favorite', '2025-03-13 21:56:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_ratings`
--

CREATE TABLE `user_ratings` (
  `id_user_rating` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `ratings_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`ratings_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_ratings`
--

INSERT INTO `user_ratings` (`id_user_rating`, `id_user`, `ratings_data`, `created_at`, `updated_at`) VALUES
(4, 116, '{\"6\": \"8\", \"17\": \"10\", \"13\": \"0\"}', '2025-03-15 20:54:31', '2025-03-15 21:18:16'),
(10, 117, '{\"17\": \"10\", \"14\": \"0\", \"24\": \"0\", \"13\": \"10\"}', '2025-03-17 13:24:02', '2025-03-17 21:31:46');

-- --------------------------------------------------------

--
-- Table structure for table `wisata`
--

CREATE TABLE `wisata` (
  `id_wisata` int(11) NOT NULL,
  `nama_wisata` varchar(100) NOT NULL,
  `kategori` enum('alam','budaya_sejarah','pantai','kota_belanja','kuliner','petualangan','relaksasi') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `maps_url` varchar(255) DEFAULT NULL,
  `maps_embed` text DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `sosial_media` text DEFAULT NULL,
  `hashtag` varchar(100) DEFAULT NULL,
  `image_url_1` varchar(2000) DEFAULT NULL,
  `image_url_2` varchar(2000) DEFAULT NULL,
  `image_url_3` varchar(2000) DEFAULT NULL,
  `fasilitas` text DEFAULT NULL,
  `transportasi` text DEFAULT NULL,
  `jam_operasional` varchar(100) DEFAULT NULL,
  `harga_tiket` varchar(100) DEFAULT NULL,
  `tips_kunjungan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wisata`
--

INSERT INTO `wisata` (`id_wisata`, `nama_wisata`, `kategori`, `deskripsi`, `lokasi`, `maps_url`, `maps_embed`, `rating`, `sosial_media`, `hashtag`, `image_url_1`, `image_url_2`, `image_url_3`, `fasilitas`, `transportasi`, `jam_operasional`, `harga_tiket`, `tips_kunjungan`) VALUES
(1, 'Taman Nasional Tesso Nilo', 'alam', 'Habitat gajah sumatera dengan hutan tropis yang luas', 'Pelalawan & Indragiri Hulu', 'https://maps.app.goo.gl/mN79zUV9KGswoT9V6', NULL, NULL, NULL, NULL, 'https://www.mongabay.co.id/wp-content/uploads/2023/02/Tesso-Nilo3.jpg', 'https://awsimages.detik.net.id/community/media/visual/2021/12/04/kelahiran-bayi-gajah-sumatera-di-riau-2_169.jpeg?w=1200', 'https://cdn.antaranews.com/cache/800x533/2019/08/13/Karhutla-Ancam-Gajah-Sumatera-130819-FBA-1_1.jpg', NULL, NULL, NULL, NULL, NULL),
(2, 'Suaka Margasatwa Kerumutan', 'alam', 'Kawasan konservasi harimau dan satwa liar langka', 'Pelalawan', NULL, NULL, NULL, NULL, NULL, 'https://bbksda-riau.id/images/summernote/1fc4186bf2d70ae5ed78ce8ae02a669d.jpg', 'https://bbksda-riau.id/images/summernote/df87c31d2d74787277d5f42ba0b02f9a.jpg', 'https://lh5.googleusercontent.com/p/AF1QipPDkAmy2cXWYQr4jv0c7LBfznisUgjMX-R3-X57=w540-h312-n-k-no', NULL, NULL, NULL, NULL, NULL),
(3, 'Danau Zamrud', 'alam', 'Danau alami dengan keindahan alam yang masih asri', 'Siak', NULL, NULL, NULL, NULL, NULL, 'https://jadesta.kemenparekraf.go.id/imgpost/27096.jpg', 'https://lh5.googleusercontent.com/p/AF1QipPc5vihgm7rK_n6mlvYF6Y0eEgUFqGhJOpxflKo=w540-h312-n-k-no', 'https://lh5.googleusercontent.com/p/AF1QipOwH3Jo3EYFJMeJIyLmxAaWf6iH2tBmZbDV2UuP=w540-h312-n-k-no', NULL, NULL, NULL, NULL, NULL),
(4, 'Air Terjun Sungai Kopu', 'alam', 'Air terjun bertingkat dengan air jernih dan sejuk', 'Kuantan Singingi', NULL, NULL, NULL, NULL, '', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS9nv_9p7rgnoTYnJ5Kx-ZamTafjIN5FBUDIg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6WiW2aC4sZmWzCp1m-skXTOlrBbga18wKSMhblRhYwyZRLs9SxjbzFUA-Q7w_jQ1Zcew&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPiRlAmh-dF69CVg3MTfesKmHGya94rdoZLg&s', NULL, NULL, NULL, NULL, NULL),
(5, 'Danau Naga Sakti', 'alam', 'Danau mistis dengan legenda naga penjaga', 'Kampar', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7QHeEu0JMxt-5f2m2u_j5YoCy9psghtRFmQ&s', 'https://portal.riau24.com/news/20190114/riau24_1547471732.jpg', 'https://www.riauonline.co.id/foto/bank/images2/Danau-Naga-Sakti.jpg', NULL, NULL, NULL, NULL, NULL),
(6, 'Hutan Bakau Sungai Apit', 'alam', 'Ekosistem mangrove dengan keanekaragaman hayati tinggi', 'Siak', NULL, NULL, NULL, NULL, NULL, 'https://www.tanjungkuras.desa.id/wp-content/uploads/WhatsApp-Image-2020-10-29-at-17.41.44.jpeg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSuNUOpMDUZzkI5L1ndCXAeDCaPevLWUquh84Qw3zR7fAKOZS-3lpy-AsRTFsaTCGWRnhI&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTa3BJVYH7yD4js3XVNbiwAldIzBpOZOi8jlw&s', NULL, NULL, NULL, NULL, NULL),
(7, 'Air Terjun Pangkalan Kapas', 'alam', 'Air terjun tersembunyi dengan pemandangan eksotis', 'Kampar', NULL, NULL, NULL, NULL, NULL, 'http://inspirasi.avonturin.id/wp-content/uploads/2022/01/foto-gambar-Air-Terjun-Batang-Kapas-Lubuk-Bigau-Kampar-Kiri-Hulu-Riau-@trepelin.jpg', 'https://ksmtour.com/media/images/articles23/air-terjun-batang-kapas-kepulauan-riau.png', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTBDIlj1rYvTaBLvYWxosJBqs5LQWUQR6Cw6XN0R40Ia-slXiHGwp9N3Q-jzfpVT3PwwEc&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(9, 'Sungai Siak', 'alam', 'Sungai historis dengan panorama kota Pekanbaru', 'Pekanbaru & Siak', NULL, NULL, NULL, NULL, NULL, 'https://i0.wp.com/infopku.com/wp-content/uploads/2020/12/Eksotisnya-Sungai-Siak.jpg?fit=1280%2C720&ssl=1', 'https://static.promediateknologi.id/crop/0x0:0x0/750x500/webp/photo/p1/995/2024/11/25/tn2-11-307965013.png', 'https://thumb.viva.co.id/media/frontend/thumbs3/2016/07/22/57918dba5b7b0-water-front-city-wfc-sungai-siak-pekanbaru_1265_711.jpg', NULL, NULL, NULL, NULL, NULL),
(11, 'Istana Siak Sri Indrapura', 'budaya_sejarah', 'Istana megah peninggalan Kesultanan Siak', 'Siak', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQop1No3-EoKCyh25VXSKiV2AsPnrxlp-jLMg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSQkHSkXI5t0uNfVcfftwYyJDKeUxSVvfdBAKlkxasFeMgiQ9u0-C3XY18mpMyOW4dOYYA&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSOhCbRFqQvRe-XyQz1p873zj5qay-uWg8dGA&s', NULL, NULL, NULL, NULL, NULL),
(12, 'Masjid Raya Pekanbaru', 'budaya_sejarah', 'Masjid bersejarah dengan arsitektur khas Melayu', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQlEXFO1XmcsfK2j6mXT52n8wTf5dM9ogZ1dg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRW5uQXQK43NN2LQEzpE7C2_tWJy4wNCR0RUw&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSs7nA9_8lxftm_Q8ni3qErY9fa1ADW_VFTCg&s', NULL, NULL, NULL, NULL, NULL),
(13, 'Kompleks Makam Raja-Raja Siak', 'budaya_sejarah', 'Situs pemakaman raja-raja Kesultanan Siak', 'Siak', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSGlSEzYQTiomSPpB0EbQD6iGroEDc4e6WP5A&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSqHT2oi-D-EsZjtFnGF6O0kYUlLukWOyfqKg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQSzopvCFtROpY2jhu2lRjz7HLnICM_D-Keaw&s', NULL, NULL, NULL, NULL, NULL),
(14, 'Istana Rokan', 'budaya_sejarah', 'Istana bersejarah dari Kesultanan Rokan', 'Rokan Hulu', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR0zmPCLm2vuyhCGI7IZb9259_eCPKSomsm2Q&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRXw0OrgVao-UbB0Xdyg7vns3o_bWX8Cfu35A&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXE-KVRlSRSk9JXDY3vDck4cMhKEp_Vei6Kw&s', NULL, NULL, NULL, NULL, NULL),
(15, 'Balai Adat Riau', 'budaya_sejarah', 'Pusat budaya dan tradisi Melayu Riau', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSOv-0NC19YnRp822WTucbtCv4m58wshwghzA&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRlYfrQFhWkXYS8x9fcPDsYz_dp-bQHfDKmBQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT2fjiuGrEUKITwRN_e6Vnaqx1SvxFFvHm0LXL1_NGvaqjKgxa1biYTNUo7xWIw8bkLCSg&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(16, 'Museum Sang Nila Utama', 'budaya_sejarah', 'Museum sejarah dan budaya Riau', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://rentalhiacepekanbaru.com/wp-content/uploads/2020/10/Museum-Sang-Nila-Utama.jpg', 'https://museum.co.id/wp-content/uploads/2020/09/Koleksi-Museum-Sang-Nila-utama.jpg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTZbrV8dgShS3a7oDEnQlEXmu4Jce6HHAnlUw&s', NULL, NULL, NULL, NULL, NULL),
(17, 'Replika Istana Kerajaan Indragiri', 'budaya_sejarah', 'Gambaran kejayaan kerajaan Indragiri', 'Indragiri Hulu (Rengat)', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTEzbdjUAYuuBV4PlRVvCQnK-wVEqVzB5K2IQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRuEoKFlPjC1AkGwcEO8T4saGvRc8oZa7cT9A&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQkOFC9Rc7YbgKORMRS7HfgmbJDQoPk1dKw0A&s', NULL, NULL, NULL, NULL, NULL),
(18, 'Candi Muara Takus', 'budaya_sejarah', 'Candi peninggalan Buddha di Riau', 'Kampar', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQsHaGyej8p-2saVsCXxBL6yQzEV0kgvL5nEw&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2_geicwKg0C0Z6HuAntukp0CugbA4NZlqmA&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXhlzhegB81oe3oWmBgNh1M272nLiLSIgxTA&s', NULL, NULL, NULL, NULL, NULL),
(19, 'Rumah Singgah Sultan Siak', 'budaya_sejarah', 'Rumah bersejarah Sultan Siak di Pekanbaru', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://bertuahpos.com/wp-content/uploads/2022/09/IMG_20220928_145810_733.jpg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3KUNdQ_9k23ly45CWbgzpg7JqTeUDF9C_9g&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRaaR1s7BgpXbN6O944Sfui1Jg0LtpsWp7YWA&s', NULL, NULL, NULL, NULL, NULL),
(20, 'Kawasan Bandar Senapelan', 'budaya_sejarah', 'Kawasan tua dengan peninggalan sejarah Pekanbaru', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://jadesta.kemenparekraf.go.id/imgpost/62212_medium.jpg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTGnbkBsI4bansdWU1d6KX3RriDjtzHa4w672sKXZFRb3IrOkfr-Kq2CmhOWPpJamv8c_k&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQYwzCD6ohN5GATOsRkj8q67D_9zg0kSUr_O7DXaypns9TBt3Tsg49c_Y9_OIiMYJUmuyc&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(21, 'Pantai Solop', 'pantai', 'Pantai dengan pasir unik berwarna coklat keemasan', 'Indragiri Hilir', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTfcvGPIZvbEDXxYRKGZkbV4bkj6wFxC4_I_-mBbFteCewuJYPpJCOLIMzzfQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2zaXQaZo8bgekrblrYw48sja0Z7hkXbFH1Q&s', 'https://1.bp.blogspot.com/-vFGDN_64uUg/XeOTx8cwAUI/AAAAAAAAPbQ/JoP54t5V6zghe7Od9KjBD8hDMDuqzdiaACLcBGAsYHQ/s1600/IMG-20191201-WA0039.jpg', NULL, NULL, NULL, NULL, NULL),
(22, 'Pantai Teluk Makmur', 'pantai', 'Pantai eksotis dengan ombak tenang', 'Dumai', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1C8XxVczngGj9PX2_RISOAnD596kHkUMXPQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSsgnituPL6dosxN52e1W2ARRa4r05aiDPEJg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRHKv_f5yeJ0DH6K8ByGRSrUhSJXZbPhebTZw&s', NULL, NULL, NULL, NULL, NULL),
(23, 'Pantai Tanjung Lapin', 'pantai', 'Destinasi tropis dengan pasir putih memikat', 'Rupat', NULL, NULL, NULL, NULL, NULL, 'https://static.promediateknologi.id/crop/0x0:0x0/0x0/webp/photo/p2/236/2024/12/05/Jelajahi-Keindahan-Tanjung-Lapin-di-Rupat-Fest-3901395050.jpg', 'https://i0.wp.com/www.dumaiposnews.com/wp-content/uploads/2022/12/IMG-20221213-WA0150-1.jpg?resize=1140%2C760&ssl=1', 'https://www.melayupedia.com/foto_berita/2022/02/2022-02-21-pesona-pasir-putih-sepanjang-17-km-di-pantai-lapin-riau.jpg', NULL, NULL, NULL, NULL, NULL),
(24, 'Pantai Ketapang', 'pantai', 'Pantai indah dengan pohon kelapa yang rindang', 'Bengkalis (Rupat Utara)', NULL, NULL, NULL, NULL, NULL, 'https://asset-2.tstatic.net/jabar/foto/bank/images/pantai-ketapang-indramayu.jpg', 'https://lh3.googleusercontent.com/p/AF1QipP7Q7uOdayiIz5XoQldcn230pWJbFfUV924aJdl=s680-w680-h510', 'https://lh3.googleusercontent.com/p/AF1QipOmCVLah8l4kibVr4vtlS26b7jWJfSleeTc1vaH=s680-w680-h510', NULL, NULL, NULL, NULL, NULL),
(25, 'Pantai Tanjung Medang', 'pantai', 'Pantai terpencil dengan keindahan alami', 'Bengkalis (Rupat Utara)', NULL, NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/p/AF1QipMuSLGdC5cti5L1znyTO9whFiOGJFr1hAPod_E=s680-w680-h510', 'https://lh3.googleusercontent.com/p/AF1QipNQk722q-l-TemIvLoGhcxrIbRyDomvmM2K1XNr=s680-w680-h510', 'https://lh3.googleusercontent.com/p/AF1QipOMWPV4dOeBhO9RO0JJ3_wJmCxXmGtbLk4FhFUV=s680-w680-h510', NULL, NULL, NULL, NULL, NULL),
(26, 'Pantai Marina', 'pantai', 'Pantai berbatu eksotis dengan panorama laut luas', 'Dumai', NULL, NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/p/AF1QipOe4Mc3rhOLFFEkDFPA29zbLFRlnseGPXMaJmJw=s680-w680-h510', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQwfQvcwVwSO3GkhLfi2FDLe2vkdl7TMz-saA_LeY50pD3n_CPSMABjwRHOJNeAnLdMi20&usqp=CAU', 'https://lh3.googleusercontent.com/p/AF1QipPSvlUhw9hfnL3tSAR97UAq4Apn3MlfTX_dXAlj=s680-w680-h510', NULL, NULL, NULL, NULL, NULL),
(27, 'Mall SKA', 'kota_belanja', 'Pusat perbelanjaan modern terbesar di Pekanbaru', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRuW2KRRo6UMw357_DNHq-aLN365Mg0sqpizQ&s\r\n', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTCMFNTbXMw5_BRe40m2PGVRNQbXghAqmGkHA&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgKfX4QgaTP-8YgKnrbnoKngGsQhJh04c6vg&s', NULL, NULL, NULL, NULL, NULL),
(28, 'Mall Pekanbaru', 'kota_belanja', 'Destinasi belanja dengan berbagai tenant menarik', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRXemhFAOziRS6DBZMIQ884K2Li1SmFJgUS9g&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTpVQHZ1eCY96pH-AGOPNH7t7p3ueVeR8-o7w&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTxEEernnWo1Cj-5v-wsqsUstjlAd9MprejMw&s', NULL, NULL, NULL, NULL, NULL),
(29, 'Living World', 'kota_belanja', 'Mall dengan konsep gaya hidup modern', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://www.pekanbaru.go.id/berkas_file/menu/berkas/mp-img-17-38-17645.png', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQNauEbNK-CLNrVfOlHouneJyFf2YBtCqRuCQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8MRXvrEKp4QSTvqYlLEP8ArqNapIMwszsfQ&s', NULL, NULL, NULL, NULL, NULL),
(30, 'Mal Ciputra Seraya', 'kota_belanja', 'Pusat hiburan dan belanja keluarga', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTSy83xevZ0i2xG-sZkaQp5C0rdWdBO9Ru9Y_wfOiiBQaSFQ56xw5KmKdWlALJApxiGFvM&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRXur8kHCkkW0bwyo4hUSUuDs9Jm_z19gWtL0syLuOA9a_Gldd20MU0cJ5kKbCWHzqoIYM&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQtgLeXFh0guPwY2CqDJzH7tWTUWSTUlZSO58jR0n2Vc-Y5xDh07nh6w_8O2mbgTBwFdO0&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(31, 'Plaza Sukaramai', 'kota_belanja', 'Pusat grosir dan perbelanjaan klasik Pekanbaru', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSc6JpKOn3Xr8225ry6LH6HBKJ6cDakJTspTossb_-J2D02zeVe6i_n4ZDCPP2Q4EHA7SQ&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQum8zxgS0z09Tp85aayDp76WVQbFQ9DUiZb0gRlE70sd4-Bk7NEfQbTKR-OIkyjFt7mbY&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRR5BmqpZ2rZ20vBFcMGiiZTcnf8Hio2QashQ&s', NULL, NULL, NULL, NULL, NULL),
(32, 'Pasar Bawah', 'kota_belanja', 'Pasar tradisional dengan aneka oleh-oleh khas Riau', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR2rwZIZbMboptauklhnEc3DWD2wf-uK8ZFXw&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzdgKcvv-1EbOo2wO42Mx8qmH8q6hoSbk-rg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQYjIH_k8ogwn0TGb-E0rcwEWikmFyJjqlBQg&s', NULL, NULL, NULL, NULL, NULL),
(33, 'Kawasan Kuliner Pondok Melati', 'kuliner', 'Tempat kuliner dengan makanan khas Riau', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://assets.kompasiana.com/items/album/2022/08/24/126980685-206540971047167-2285447062848023074-n-63059dfec8351212cd1ed092.jpg?t=o&v=300', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcROMENbSN1nywsa3QWMiCDu5iXLwvcyZZoRAn3iPcWUFu-cX4UreElqjT3oGpdT9qrEWZU&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTnPhok9ipYqdDcqrqBQB-8A0SFZVtWPml24qbqBMpfZ8H5br2vwU4IbG6GfTlF5juiQu4&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(34, 'Kompleks Purna MTQ', 'budaya_sejarah', 'Area ikonik dengan monumen MTQ', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS_PqoEJGMLKHn8iW729BU6sO0ANVt-Km0sR12djjIeXlNJZUzqsq9HUNWy-O-O0oXLje8&usqp=CAU', 'https://riauterkini.com/berita/1666165788-picsay.jpg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSpQihDFu24YgGqpZnEoZxXwg0540C6niK9RA&s', NULL, NULL, NULL, NULL, NULL),
(35, 'Pasar Rakyat Palapa', 'kota_belanja', 'Pasar tradisional ramai di pusat Pekanbaru', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRHzSlWDf2SUcDiTn8EjAPxSkpKVfbekchr-Q&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTNn7byHYw3MueSrBJMHHiVG12UTe-Ic95u4Apuo3KO5gzPVWFb9y10SOpTVxftCSIEsQQ&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHESHYqHoSvtpeOfIyByjUtkubkb6K4ZYcbA&s', NULL, NULL, NULL, NULL, NULL),
(37, 'Kuliner Bundaran Keris', 'kuliner', 'Pusat jajanan dan nongkrong anak muda di Pekanbaru', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQHR8k1G5Ik3RQTadGeUVBMp2wJJucEO38x9A&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4sbHngqV2Y-3jqacvgqM0u1qDlgnUSJqldQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQywoCcEt56EAeBBBFAYtRFVglOLvIK5t9j7w&s', NULL, NULL, NULL, NULL, NULL),
(39, 'Pondok Patin HM Yunus', 'kuliner', 'Restoran terkenal dengan hidangan ikan patin lezat', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTaGAGJus1SegOjBsJdS3KeM8XAcHrXuXrEmw&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQLUc8JtyAPEuuKz-5HACUFzZReqdCYegxHufbf58l5L_zu3Ek7DycfKbS4MytCJbLrfx8&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRzKiIxZSqCD4xM6voZANNpJxmja6jtU8IGTbjco7k61xvhq2zhI554AgP1Gx5XxcDiHX0&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(40, 'Kawasan Kuliner Jalan Nangka', 'kuliner', 'Surga kuliner malam di Pekanbaru', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQfUfIbucDe5VYz8OelNgmUkMqgAPJqVtZwht58q-4yiQsJJGw2WgG-oBCYhxDIXbIDnsM&usqp=CAU', 'https://assets.promediateknologi.id/crop/0x0:0x0/750x500/webp/photo/2023/02/05/2425934376.jpg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQXGW5B6n_7UPosuC9nK9Eg93TZvr8IeziG612cVU0icgETWTzfHvklawGtLa3TLxoAtS0&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(42, 'Bukit Suligi', 'petualangan', 'Spot pendakian dengan panorama pegunungan menakjubkan', 'Rokan Hulu', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzYvIri78Qn_8OVOVy_FZRwM8J6a7Vui7W_BFvLUNGmzkVKscqEx4vZLwo-XteafJUVY8&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSIMFvV8hJd9eWYtcmZoTsxwSYbvzfb4M6zJscnPORA5ahPx7LyyF_-89Af3BarT4OpG3k&usqp=CAU', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSe29wsAyY4ML0dNBNsu14LhC1t2c2lVgDa4HQgsf7atb-3ZD3eR7Cu-Ui-TJjwg_ymLU8&usqp=CAU', NULL, NULL, NULL, NULL, NULL),
(43, 'Arung Jeram Sungai Kampar', 'petualangan', 'Tantangan arus deras bagi pecinta adrenalin', 'Kampar', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTe8w61BY9CEMGjoZZrsRu4zZ0mcWZJ7RHV7g&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf2n8WqaLsafqMV6iKQRXo-elTMCtEUACu1A&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTg1H_1b0s2mgh6ASDHeFc6RJW6JprsMofyng&s', NULL, NULL, NULL, NULL, NULL),
(44, 'Taman Hutan Raya Sultan Syarif Hasyim', 'petualangan', 'Kawasan hutan konservasi alami', 'Siak', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSzb-_BCQ6uUHLzf2ATblkqzZXHQqhwAKHEEQ&s', 'https://avonturin.id/wp-content/uploads/2022/06/Apa-itu-tahura-taman-hutan-raya-adalah-avonturin.id-20211202_103353.jpg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTk-81bLw5NQBHyrSHu45RoUpj3AaGDFseJwg&s', NULL, NULL, NULL, NULL, NULL),
(46, 'Kawasan Konservasi Gajah Minas', 'petualangan', 'Tempat konservasi dan edukasi gajah sumatera', 'Siak', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR2ClqDWEzJpnZgIY07SMLC5a_LUNKX-7AObw&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT10XpksNDBNN5WvSy1JaINzXxfHoWCkf9FCg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQjjdYldbX7yNnmcYq0IysNzrGBExaaEwm4hA&s', NULL, NULL, NULL, NULL, NULL),
(47, 'Trek Taman Nasional Tesso Nilo', 'petualangan', 'Trekking seru di habitat satwa liar', 'Pelalawan', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQFhB1R0MF1oqYxHnFejs0-uo2-fVoHGbVu2w&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8b5wbC824SLdhtxlfC4lNzeHv9R_ARMbeoQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQpAgoFFBIsttGIw4kBp24WOZSxyjElpWCD_A&s', NULL, NULL, NULL, NULL, NULL),
(48, 'Alam Mayang Recreation Park', 'relaksasi', 'Tempat rekreasi keluarga dengan danau buatan', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTvoIT2AetlUTsiq0F4mekdGjqZwIxhN_jgvw&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSqwqvbATHhu5VOe5nAJFO7IpeXrAFtwiVMeA&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSx4hgYWDdTwl_FvB1uHFQp_9VL8P1j9fL1zA&s', NULL, NULL, NULL, NULL, NULL),
(49, 'Taman Agrowisata Tenayan Raya', 'relaksasi', 'Wisata edukasi dan pertanian modern', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSq6r2V4h1wWJGrS5N9dWBbkvRE8lHZpQNJ4Q&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR3117bS4Djuq0O5uil1WX2-f-8cURcNR9vVw&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTTHOUn0MWwjb4kluRO-_tl0cynlqw4r2mcfQ&s', NULL, NULL, NULL, NULL, NULL),
(50, 'Danau Bandar Kayangan', 'relaksasi', 'Destinasi santai dengan suasana tenang', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSEmRDJ5NqmQABpTLNs6i-0N7ku3wnI32dWbQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcToQtLvIcxxoWYlCQ6UmQQFMQzCGp8sSzCdzg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTAs5wIpkkxqT9BB5mkaPraUCgI0AOCrGT0ew&s', NULL, NULL, NULL, NULL, NULL),
(52, 'Taman Rekreasi Stanum', 'relaksasi', 'Tempat wisata keluarga dengan wahana permainan', 'Pekanbaru', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4sF6R0OQOcQ28a-2KQirmnbztS8nZAbL3sg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTT2hBLbZ8lfAXkja23qhIvBS_3QY75H49EtQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQNKgjQxN9bhvnqaGs6SepTZPlsoYM4lvSedQ&s', NULL, NULL, NULL, NULL, NULL),
(53, 'Wisata Danau Raja', 'relaksasi', 'Danau bersejarah dengan suasana asri', 'Indragiri Hulu (Rengat)', NULL, NULL, NULL, NULL, NULL, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRyjHWRAsYMOlS69MP64q_KHoynT21LI8KVrQ&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTopycnfVqbeBC8_SbDeRIhhk-8PenV8Wj7jg&s', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXZv66kcxDKMaF4ApJ9f82hbxC4FwqliemuQ&s', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wisata_reviews`
--

CREATE TABLE `wisata_reviews` (
  `id_review` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wisata_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id_content`);

--
-- Indexes for table `kategori_detail`
--
ALTER TABLE `kategori_detail`
  ADD PRIMARY KEY (`id_kategori_detail`),
  ADD UNIQUE KEY `kategori` (`kategori`);

--
-- Indexes for table `preferences`
--
ALTER TABLE `preferences`
  ADD PRIMARY KEY (`id_preferensi`),
  ADD KEY `user_id` (`id_user`),
  ADD KEY `idx_user_preferences` (`id_user`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`id_recommendation`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email_user` (`email_user`),
  ADD UNIQUE KEY `unique_email` (`email_user`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id_favorite`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`wisata_id`),
  ADD KEY `wisata_id` (`wisata_id`),
  ADD KEY `idx_user_favorites` (`user_id`,`wisata_id`);

--
-- Indexes for table `user_interactions`
--
ALTER TABLE `user_interactions`
  ADD PRIMARY KEY (`id_interaction`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `wisata_id` (`wisata_id`),
  ADD KEY `idx_user_interactions` (`user_id`,`wisata_id`);

--
-- Indexes for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD PRIMARY KEY (`id_user_rating`),
  ADD UNIQUE KEY `unique_user` (`id_user`),
  ADD KEY `idx_user_ratings` (`id_user`);

--
-- Indexes for table `wisata`
--
ALTER TABLE `wisata`
  ADD PRIMARY KEY (`id_wisata`),
  ADD KEY `idx_wisata_kategori` (`kategori`);
ALTER TABLE `wisata` ADD FULLTEXT KEY `idx_wisata_search` (`nama_wisata`,`deskripsi`);

--
-- Indexes for table `wisata_reviews`
--
ALTER TABLE `wisata_reviews`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `wisata_id` (`wisata_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `id_content` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_detail`
--
ALTER TABLE `kategori_detail`
  MODIFY `id_kategori_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preferences`
--
ALTER TABLE `preferences`
  MODIFY `id_preferensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `id_recommendation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id_favorite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `user_interactions`
--
ALTER TABLE `user_interactions`
  MODIFY `id_interaction` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_ratings`
--
ALTER TABLE `user_ratings`
  MODIFY `id_user_rating` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `wisata`
--
ALTER TABLE `wisata`
  MODIFY `id_wisata` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `wisata_reviews`
--
ALTER TABLE `wisata_reviews`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `preferences`
--
ALTER TABLE `preferences`
  ADD CONSTRAINT `preferences_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`wisata_id`) REFERENCES `wisata` (`id_wisata`);

--
-- Constraints for table `user_interactions`
--
ALTER TABLE `user_interactions`
  ADD CONSTRAINT `user_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `user_interactions_ibfk_2` FOREIGN KEY (`wisata_id`) REFERENCES `wisata` (`id_wisata`);

--
-- Constraints for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD CONSTRAINT `user_ratings_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `wisata_reviews`
--
ALTER TABLE `wisata_reviews`
  ADD CONSTRAINT `wisata_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `wisata_reviews_ibfk_2` FOREIGN KEY (`wisata_id`) REFERENCES `wisata` (`id_wisata`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
