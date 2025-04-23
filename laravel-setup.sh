#!/bin/bash

LARAVEL_PATH="/var/www/svg-converter"
PHP_VERSION="8.3"
USER="www-data"
GROUP="www-data"

echo "🔧 Setting Laravel permissions..."
sudo chown -R $USER:$GROUP $LARAVEL_PATH
sudo find $LARAVEL_PATH -type f -exec chmod 644 {} \;
sudo find $LARAVEL_PATH -type d -exec chmod 755 {} \;
sudo chmod -R ug+rwx $LARAVEL_PATH/storage $LARAVEL_PATH/bootstrap/cache

echo "🔄 Restarting PHP-FPM ($PHP_VERSION)..."
sudo systemctl restart php${PHP_VERSION}-fpm

echo "🔄 Restarting Nginx..."
sudo systemctl restart nginx

echo "🔄 Restarting MariaDB..."
sudo systemctl restart mariadb

echo "🛠 Running Laravel migrate and queue restart..."
cd $LARAVEL_PATH || exit
sudo -u $USER php artisan migrate --force
sudo -u $USER php artisan queue:restart

echo "⚙️ Creating Laravel queue worker systemd service..."
sudo tee /etc/systemd/system/laravel-worker.service > /dev/null <<EOF
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=$USER
Group=$GROUP
Restart=always
ExecStart=/usr/bin/php $LARAVEL_PATH/artisan queue:work --sleep=3 --tries=3 --timeout=90
WorkingDirectory=$LARAVEL_PATH

[Install]
WantedBy=multi-user.target
EOF

echo "🔁 Reloading and starting laravel-worker service..."
sudo systemctl daemon-reexec
sudo systemctl daemon-reload
sudo systemctl enable laravel-worker
sudo systemctl restart laravel-worker

echo "🧹 Creating cronjob for queue cleaner..."
sudo tee /etc/cron.d/laravel-cleaner > /dev/null <<EOF
0 3 * * * $USER /usr/bin/php $LARAVEL_PATH/artisan clean:converted >> $LARAVEL_PATH/storage/logs/cleaner.log 2>&1
EOF

echo "🔁 Restarting cron service..."
sudo systemctl restart cron

echo "✅ All Laravel services and configurations are now set!"

