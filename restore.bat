@echo off
set DB_NAME=holiday_addict
set BACKUP_FILE=%1

if "%1"=="" (
    echo Please provide backup file name
    exit /b 1
)

"C:\xampp\mysql\bin\mysql.exe" -u root %DB_NAME% < %BACKUP_FILE%

if %ERRORLEVEL% EQU 0 (
    echo Database restored successfully
) else (
    echo Failed to restore database
)
