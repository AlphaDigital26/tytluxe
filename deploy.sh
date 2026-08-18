#!/bin/bash

# TYT Luxe Deployment Script
# This script should be run on your live server to deploy updates.

set -e # Exit immediately if a command exits with a non-zero status

echo "🚀 Starting deployment process for TYT Luxe..."

# Navigate to the project directory
# Change this if your project is located elsewhere on the server
cd /var/www/tytluxe

# Put application into maintenance mode
echo "🚧 Putting application into maintenance mode..."
php artisan down || true

# Pull the latest changes from the git repository
echo "📥 Pulling latest code from git..."
git pull origin main # Change 'main' to your actual branch name if different

# Install/Update PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install/Update Node.js dependencies and build assets
echo "🎨 Building frontend assets..."
npm install
npm run build

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear and optimize Laravel caches
echo "🧹 Optimizing Laravel caches..."
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan event:cache

# Restart the queue worker so it picks up code changes
echo "🔄 Restarting queue workers..."
php artisan queue:restart
# Alternatively, restart supervisor if you prefer:
# sudo supervisorctl restart tytluxe-worker:*

# Fix permissions just in case
echo "🔒 Setting correct permissions..."
sudo chown -R www-data:www-data /var/www/tytluxe
sudo chmod -R 775 /var/www/tytluxe/storage /var/www/tytluxe/bootstrap/cache

# Bring application out of maintenance mode
echo "✅ Bringing application out of maintenance mode..."
php artisan up

echo "🎉 Deployment completed successfully!"
