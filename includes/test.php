<?php
require 'db.php';
require 'api.php';

$preferences = [
    'alam' => 1,
    'pantai' => 1,
    'budaya_sejarah' => 0
];

$result = getRecommendationsFromAPI($preferences);
var_dump($result);
?>