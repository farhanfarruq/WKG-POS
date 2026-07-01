#!/bin/bash
export COMPOSER_ALLOW_SUPERUSER=1

cd /var/www/html

# Setup .env if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env
fi

# Ensure the SQLite DB exists
mkdir -p database
if [ ! -f "database/database.sqlite" ]; then
    echo "Creating SQLite database..."
    touch database/database.sqlite
fi

# Fix permissions for directories
chown -R www-data:www-data storage bootstrap/cache database database/database.sqlite 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Check if installation is needed
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

if [ ! -d "node_modules" ] || [ ! -d "public/build" ]; then
    echo "Installing NPM dependencies and building assets..."
    npm install --ignore-scripts
    npm run build
fi

# Generate app key if not set
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating Application Key..."
    php artisan key:generate --ansi
fi

# Run migrations and seed data automatically ONLY if DB is empty
echo "Running database migrations..."
if [ ! -s "database/database.sqlite" ] || [ $(stat -c%s "database/database.sqlite" 2>/dev/null || stat -f%z "database/database.sqlite") -eq 0 ]; then
    php artisan migrate --force
    php artisan db:seed --force
else
    php artisan migrate --force
fi

php artisan db:seed --class='App\Seeders\WorkShiftSeeder' --force


# Fix permissions again after build/migrations
chown -R www-data:www-data storage bootstrap/cache database database/database.sqlite 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Start Apache
echo "Starting Application..."
exec "$@"
