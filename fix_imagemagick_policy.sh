#!/bin/bash

# مسیر فایل policy.xml
POLICY_FILE="/etc/ImageMagick-6/policy.xml"
# اگر فایل در مسیر دیگر باشد (مثل /etc/ImageMagick/policy.xml) باید آن را جایگزین کنید

# بررسی وجود فایل policy.xml
if [ -f "$POLICY_FILE" ]; then
    echo "Filling PDF permission in policy.xml..."

    # اصلاح دسترسی برای PDF
    sudo sed -i 's|<policy domain="coder" rights="none" pattern="PDF" />|<policy domain="coder" rights="read|write" pattern="PDF" />|' "$POLICY_FILE"

    echo "PDF permissions updated in policy.xml."
else
    echo "File policy.xml does not exist at $POLICY_FILE."
    exit 1
fi

# پاکسازی کش ImageMagick
echo "Cleaning ImageMagick cache..."
sudo rm -rf /var/cache/imagemagick-6/

# ریستارت کردن سرویس‌های مربوطه
echo "Restarting Nginx and PHP-FPM..."
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

echo "All steps completed. You can try to convert PDF again."

