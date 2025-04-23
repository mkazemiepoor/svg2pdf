
# SVG Converter API

این پروژه یک API ساده برای آپلود فایل‌های SVG و تبدیل آن‌ها به خروجی‌های PNG و PDF است. فایل‌ها به صورت asynchronous پردازش شده و خروجی‌ها در مسیر `storage/app/public` ذخیره می‌شوند.

---

## 🚀 نصب و اجرا

```bash
git clone https://github.com/your-username/your-repo.git
cd your-repo
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan queue:work
php artisan serve
```

> ⚠️ اطمینان حاصل کنید که ابزار `rsvg-convert` روی سیستم نصب شده باشد:
```bash
sudo apt install librsvg2-bin
```

---

## 🧪 API Endpoints

### 1. بارگذاری فایل‌های SVG

**URL:** `POST /api/svg/upload`  
**Headers:**
```
Content-Type: multipart/form-data
Accept: application/json
```

**Body:**
```form
files[]: file1.svg
files[]: file2.svg
...
```

**Response:**
```json
{
  "batch_id": "49bfa34a-df5a-4cd7-b4f4-9f3cc4e3e962",
  "status": "Processing",
  "url": "http://your-domain.com",
  "svg": [
    "/absolute/path/to/file1.svg",
    "/absolute/path/to/file2.svg"
  ]
}
```

---

### 2. بررسی وضعیت تبدیل فایل‌ها

**URL:** `GET /api/svg/status/{batch_id}`

**Response:**
```json
{
  "status": "success",  // یا "processing"
  "pdf_exists": true,
  "pdf": "http://your-domain.com/storage/{batch_id}.pdf",
  "pngs": [
    "http://your-domain.com/storage/{batch_id}-file1.png",
    "http://your-domain.com/storage/{batch_id}-file2.png"
  ],
  "actual_pngs": 2
}
```

---

## 🧰 ساختار سیستم

- `SvgController`: دریافت فایل و dispatch کردن job
- `ConvertImageJob`: اجرای async برای تبدیل‌ها
- `ImageToPdfService`: تبدیل فایل‌ها و ساخت PDF چند صفحه‌ای
- Queue: برای پردازش background
- Storage: برای ذخیره فایل‌ها در `storage/app/public`

---

## 📁 مسیرهای فایل

| نوع فایل | مسیر ذخیره |
|----------|-------------|
| SVG | `storage/app/public/uuid-filename.svg` |
| PNG | `storage/app/public/uuid-filename.png` |
| PDF | `storage/app/public/uuid.pdf` |

---

## 📦 وابستگی‌ها

- Laravel 10+
- PHP 8.1+
- [clegginabox/pdf-merger](https://github.com/clegginabox/pdf-merger)
- ابزار `rsvg-convert` برای تبدیل SVG به PNG/PDF

---

## 🛠 نکات توسعه

- فایل‌های PNG به صورت جداگانه ذخیره می‌شوند.
- یک فایل PDF نهایی از همه صفحات ایجاد می‌شود.
- پردازش به صورت async توسط Queue انجام می‌شود.
- امکان گسترش سیستم برای OCR، افزودن واترمارک یا فشرده‌سازی وجود دارد.

---

## 📃 لایسنس

MIT License
