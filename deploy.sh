#!/bin/bash

# TYT Luxe Deployment Script
# Run on the live server to deploy updates:  bash deploy.sh
# First time on a server, also see the one-time steps at the bottom.

set -e # Exit immediately if a command exits with a non-zero status

APP_DIR=/var/www/tytluxe.in
BRANCH=master            # merge feature into master (pull request) before deploying
BACKUP_DIR=/var/backups/tytluxe

# If any step fails, say what state the site is in and how to recover,
# instead of stopping silently with the site still in maintenance mode.
on_error() {
    echo ""
    echo "❌ Deployment stopped at line $1."
    echo "   The site is still in maintenance mode (visitors see the 'back soon' page)."
    echo "   Fix the problem shown above and run 'bash deploy.sh' again,"
    echo "   or bring the old version back online with:  cd $APP_DIR && php artisan up"
    echo "   A database backup from before this deploy is in $BACKUP_DIR (if the backup step ran)."
}
trap 'on_error $LINENO' ERR

echo "🚀 Starting deployment process for TYT Luxe..."

# Navigate to the project directory
cd "$APP_DIR"

# Put application into maintenance mode
echo "🚧 Putting application into maintenance mode..."
php artisan down || true

# Pull the latest changes from the git repository
echo "📥 Pulling latest code from git ($BRANCH)..."
git pull origin "$BRANCH"

# Install/Update PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install/Update Node.js dependencies and build assets
echo "🎨 Building frontend assets..."
npm ci
npm run build
# A leftover "hot" file (from running 'npm run dev') makes every page load
# CSS/JS from a dev server that isn't running. Never wanted on the live site.
rm -f public/hot

# Back up the database before changing it (keeps the last 10 backups)
echo "💾 Backing up the database..."
mkdir -p "$BACKUP_DIR"
DB_DATABASE=$(php artisan tinker --execute="echo config('database.connections.mysql.database');" 2>/dev/null)
DB_USERNAME=$(php artisan tinker --execute="echo config('database.connections.mysql.username');" 2>/dev/null)
DB_PASSWORD=$(php artisan tinker --execute="echo config('database.connections.mysql.password');" 2>/dev/null)
DB_HOST=$(php artisan tinker --execute="echo config('database.connections.mysql.host');" 2>/dev/null)
MYSQL_PWD="$DB_PASSWORD" mysqldump --single-transaction --quick -h "$DB_HOST" -u "$DB_USERNAME" "$DB_DATABASE" \
    | gzip > "$BACKUP_DIR/tytluxe-$(date +%Y%m%d-%H%M%S).sql.gz"
ls -1t "$BACKUP_DIR"/tytluxe-*.sql.gz | tail -n +11 | xargs -r rm --

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Public link for uploaded photos (packages, blog, hotels). Safe to repeat.
php artisan storage:link 2>/dev/null || true

# Clear and optimize Laravel & Filament caches
echo "🧹 Optimizing Laravel and Filament caches..."
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

# Restart the queue worker so it picks up code changes
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Fix permissions to the correct directory
echo "🔒 Setting correct permissions..."
sudo chown -R www-data:www-data "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# Bring application out of maintenance mode
echo "✅ Bringing application out of maintenance mode..."
php artisan up

echo "🎉 Deployment completed successfully!"

# ---------------------------------------------------------------------------
# One-time steps (run by hand, not on every deploy):
#
#   Tidy the hotel amenity list once after the October 2026 release
#   (shows what it will change first; add --delete to apply):
#       php artisan app:clean-amenities
#       php artisan app:clean-amenities --delete
#
#   Scheduler — must be in the server's crontab (crontab -e -u www-data):
#       * * * * * cd /var/www/tytluxe.in && php artisan schedule:run >> /dev/null 2>&1
#
#   Queue worker — must be kept running (supervisor), e.g.:
#       php artisan queue:work --sleep=3 --tries=3 --max-time=3600
# ---------------------------------------------------------------------------
