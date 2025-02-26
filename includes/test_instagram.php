<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "Menguji Instagram Scraper\n";
try {
    $output = shell_exec("node " . dirname(__DIR__) . "/asset/js/instagramScraper.js wisata");
    echo "Output mentah:\n";
    var_dump($output);
    
    echo "\nData yang sudah diproses:\n";
    $data = json_decode($output, true);
    var_dump($data);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>