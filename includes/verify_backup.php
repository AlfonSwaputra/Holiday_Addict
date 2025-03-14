<?php
$backupFile = 'backup/database/backup_20250313_154653.sql';
if(file_exists($backupFile)) {
    echo "Backup file exists and size: " . filesize($backupFile) . " bytes";
}
?>