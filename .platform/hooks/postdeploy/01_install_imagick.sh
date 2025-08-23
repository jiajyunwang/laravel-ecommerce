#!/bin/bash

echo "🔧 安裝系統套件與 Node.js..."
yum install -y ImageMagick ImageMagick-devel gcc php-devel php-pear
curl -fsSL https://rpm.nodesource.com/setup_18.x | bash -
yum install -y nodejs

echo "📁 建立必要目錄..."
mkdir -p /var/app/current/storage/logs
mkdir -p /var/app/current/storage/framework/cache
mkdir -p /var/app/current/storage/framework/sessions
mkdir -p /var/app/current/storage/framework/views
mkdir -p /var/app/current/bootstrap/cache

echo "🔐 修改權限..."
chown -R webapp:webapp /var/app/current/storage
chown -R webapp:webapp /var/app/current/bootstrap/cache
chmod -R 775 /var/app/current/storage
chmod -R 775 /var/app/current/bootstrap/cache

echo "🧩 安裝 imagick 擴充..."
yes '' | pecl install imagick

echo "📦 安裝 NPM 套件與建置..."
cd /var/app/current
npm install
npm run build

echo "🔐 允許 Git 安全目錄設定..."
git config --global --add safe.directory /var/app/current

echo "🎼 安裝 Composer 套件..."
/usr/bin/composer.phar update
/usr/bin/composer.phar install --no-dev --optimize-autoloader

sudo yum install -y gcc make autoconf glibc-headers gcc-c++ php-devel php-pear
sudo pecl install redis
echo "extension=redis.so" | sudo tee /etc/php.d/20-redis.ini > /dev/null
sudo systemctl restart php-fpm
sudo systemctl restart nginx

php /var/app/current/artisan queue:work redis --daemon --sleep=3 --tries=3 >> /var/log/laravel-worker.log 2>&1 &

echo "✅ 部署腳本完成！"

