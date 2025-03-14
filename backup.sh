#!/bin/bash

# Set variabel
DB_NAME="holiday_addict"
BACKUP_PATH="backup/database"
DATE=$(date +%Y%m%d_%H%M%S)

# Buat backup
mysqldump -u root $DB_NAME > $BACKUP_PATH/backup_$DATE.sql

# Hapus backup lebih dari 30 hari
find $BACKUP_PATH -name "backup_*.sql" -mtime +30 -delete
