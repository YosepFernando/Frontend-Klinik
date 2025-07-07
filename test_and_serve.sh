#!/bin/bash

echo "=== CLEARING LARAVEL CACHE AND LOGS ==="

# Clear all caches
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear

# Clear session data (optional - akan logout semua user)
# php artisan session:flush

echo "Cache cleared successfully"

# Create/clear log file untuk testing
echo "=== PREPARING LOG FILE ==="
touch storage/logs/laravel.log
> storage/logs/laravel.log

echo "Log file prepared"

echo "=== STARTING LARAVEL SERVER ==="
echo "Server akan berjalan di http://127.0.0.1:8000"
echo "Silakan test login sebagai pelanggan dan akses recruitment apply"
echo "Setelah test, check log dengan: tail -f storage/logs/laravel.log"

php artisan serve
