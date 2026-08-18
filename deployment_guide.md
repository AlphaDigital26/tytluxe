# TYT Luxe: Hostinger VPS Terminal Deployment Guide

Since you are logged into the Hostinger web terminal as `root` on an Ubuntu VPS, you will need to manually install the web server (Nginx), PHP, and the Database (MySQL). 

Follow these terminal commands exactly as written.

---

## Phase 1: Install Required Software (LEMP Stack)

First, update your server's package list and install the required software. Run these commands one by one:

```bash
# Update package list
apt update && apt upgrade -y

# Install Nginx (Web Server)
apt install nginx -y

# Install MySQL (Database)
apt install mysql-server -y

# Install PHP 8.2 and required extensions for Laravel
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip unzip curl -y

# Install Composer (PHP Package Manager)
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Node.js & npm (for building Vite/Frontend assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

---

## Phase 2: Create the Database

We need to create a MySQL database for TYT Luxe.

1. Open the MySQL prompt:
```bash
mysql
```
2. Run the following SQL commands (replace `your_secure_password` with a real password!):
```sql
CREATE DATABASE tytluxe_db;
CREATE USER 'tytluxe_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON tytluxe_db.* TO 'tytluxe_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---
CREATE DATABASE tytluxe_db;
CREATE USER 'tytluxe_user'@'localhost' IDENTIFIED BY 'Tyt2026';
GRANT ALL PRIVILEGES ON tytluxe_db.* TO 'tytluxe_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
## Phase 3: Upload and Configure the Project

1. Navigate to the web directory:
```bash
cd /var/www/
```

2. Assuming you have pushed your project to GitHub (or you can clone it via Git):
```bash
# Clone the project (replace with your actual git URL)
git clone https://github.com/yourusername/tytluxe.git

# Move into the folder
cd tytluxe
```

3. Install Laravel and Node dependencies:
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

4. Configure `.env`:
```bash
cp .env.example .env
nano .env
```
*(In nano, update `APP_URL=https://tytluxe.in`, your `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` that you just created. Also set `QUEUE_CONNECTION=database` and add your Hostinger SMTP details. Press `CTRL+X`, then `Y`, then `Enter` to save).*

5. Finalize Laravel:
```bash
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan optimize
```

6. Set Permissions so Nginx can read the files:
```bash
chown -R www-data:www-data /var/www/tytluxe
chmod -R 775 /var/www/tytluxe/storage /var/www/tytluxe/bootstrap/cache
```

---

## Phase 4: Configure Nginx to Serve the Website

1. Create a new Nginx configuration file for your domain:
```bash
nano /etc/nginx/sites-available/tytluxe
```

2. Paste this exact configuration (change `tytluxe.in` if your domain is different):
```nginx
server {
    listen 80;
    server_name tytluxe.in www.tytluxe.in;
    root /var/www/tytluxe/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
*(Save and exit using `CTRL+X`, `Y`, `Enter`)*

3. Enable the site and restart Nginx:
```bash
ln -s /etc/nginx/sites-available/tytluxe /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

---

## Phase 5: Setup Background Queue (Supervisor)

To send emails in the background without freezing the website, we use Supervisor.

1. Install Supervisor:
```bash
apt install supervisor -y
```

2. Create a worker file:
```bash
nano /etc/supervisor/conf.d/tytluxe-worker.conf
```

3. Paste the following:
```ini
[program:tytluxe-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tytluxe/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/tytluxe/storage/logs/worker.log
stopwaitsecs=3600
```
*(Save and exit)*

4. Start the worker:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start tytluxe-worker:*
```

---

## Phase 6: Enable HTTPS (Free SSL)

Point your domain (`tytluxe.in`) to the VPS IP address in your DNS settings before running this!

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d tytluxe.in -d www.tytluxe.in
```
Follow the prompts, and Certbot will automatically secure your site with HTTPS!
