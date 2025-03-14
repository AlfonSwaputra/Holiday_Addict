<?php
require 'includes/db.php';

if($conn) {
    echo "Database connected successfully";
} else {
    echo "Connection failed";
}
?>