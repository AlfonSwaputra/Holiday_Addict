<?php
class Logger {
    private $logFile;
    
    public function __construct($type) {
        $this->logFile = "../logs/{$type}_" . date('Y-m-d') . ".log";
    }
    
    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp][$level] $message\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
?>