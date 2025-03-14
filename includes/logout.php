<?php
require_once 'session_manager.php';
SessionManager::destroy();
echo json_encode(['success' => true]);
?>