#!/bin/bash

# مسیر پروژه
PROJECT_PATH="/var/www/svg-converter"

# یوزر لینوکسی فعلی
CURRENT_USER=$(whoami)

echo "🔧 Applying permission fixes for Laravel project at $PROJECT_PATH"
echo "👤 Current user: $CURRENT_USER"

# اطمینان از وجود پوشه‌ها
cd "$PROJECT_PATH" || { echo "❌ Project path not found!"; exit 1; }

# اضافه کردن یوزر فعلی به گروه www-data
echo "➕ Adding $CURRENT_USER to group www-data"
sudo usermod -a -G www-data "$CURRENT_USER"

# تغییر گروه و مالکیت
echo "📦 Changing ownership to $CURRENT_USER:www-data"
sudo chown -R "$CURRENT_USER":www-data storage bootstrap/cache

# تنظیم دسترسی‌ها
echo "🔐 Setting folder permissions to 775"
sudo chmod -R 775 storage bootstrap/cache

# فعال کردن setgid برای حفظ گروه در دایرکتوری‌های جدید
echo "🔄 Applying setgid bit on directories"
sudo find storage bootstrap/cache -type d -exec chmod g+s {} \;

echo "✅ Done! Please logout and login again or run 'newgrp www-data' to apply group changes."

