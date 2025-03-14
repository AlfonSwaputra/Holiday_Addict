<?php
class SessionManager {
    private const SESSION_TIMEOUT = 1800; // 30 menit dalam detik
    
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerasi ID session setiap 30 menit
        if (!isset($_SESSION['last_regeneration'])) {
            self::regenerateSession();
        } else if (time() - $_SESSION['last_regeneration'] > self::SESSION_TIMEOUT) {
            self::regenerateSession(); 
        }
        
        // Cek timeout
        if (isset($_SESSION['last_activity']) && 
            time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            self::destroy();
            header('Location: /login.php');
            exit;
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    private static function regenerateSession() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    public static function destroy() {
        session_unset();
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user']);
    }
}
?>