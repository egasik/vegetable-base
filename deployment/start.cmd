@echo off
cd /d "%~dp0.."
where php >nul 2>nul
if errorlevel 1 (
  echo PHP not found. Add PHP 8.4.1+ to PATH.
  pause
  exit /b 1
)
php artisan serve --host=127.0.0.1 --port=8000
pause
