# SVG to PDF & PNG Converter (Laravel Project)

This Laravel application allows clients to upload one or more SVG files. The system converts these SVGs into:
- PNG images (one per page)
- A single multi-page PDF

Converted files are stored in `storage/app/public`, and the status of conversion is tracked.

## Features

- Accepts one or more SVG files via API
- Embeds externally linked images (e.g., via `<image xlink:href="http://...">`) as Base64 into the SVG
- Converts each SVG into:
  - A single PDF file (multi-page if multiple SVGs)
  - Separate PNG files (one PNG per SVG)
- Saves the output files in the storage directory
- Exposes API endpoints to track conversion status

## Technologies Used

- Laravel
- PHP-FPM
- Nginx
- MariaDB
- Queues (Redis/Database)
- SVG parsing and conversion libraries

## Setup

### Prerequisites

- PHP 8.1+
- Composer
- Nginx or Apache
- Laravel 10+
- Redis (optional, for queue performance)
- ImageMagick or similar for conversion

### Installation

```bash
git clone https://github.com/YOUR_USERNAME/svg2pdf.git
cd svg2pdf
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

Set up your database and configure `.env` accordingly.

### Queue Worker

To start queue workers and process jobs:

```bash
php artisan queue:work
```

You can also use `supervisor` or a systemd service to keep workers running in production.

### Running Conversion Manually (For Testing)

You can manually dispatch a job if needed:

```php
// Example
dispatch(new \App\Jobs\ConvertSvgJob($svgJobId));
```

## API Endpoints

- `POST /api/upload` — Upload one or more SVG files
    CURL Sample:
        curl -X POST http://172.16.3.1/api/svg/upload -F "files[]=@/home/www/portaltvto.com/application/modules/tasis/svg/b.svg" -F "files[]=@/home/www/portaltvto.com/application/modules/tasis/svg/a.svg"
        Responce:
        {"batch_id":"505e6a0a-5737-4f5a-8c24-0d12c21d2edb","status":"Processing","url":"http:\/\/172.16.3.1","svg":["\/var\/www\/svg-converter\/storage\/app\/public\/svg\/505e6a0a-5737-4f5a-8c24-0d12c21d2edb-cDd9PziU6y.svg","\/var\/www\/svg-converter\/storage\/app\/public\/svg\/505e6a0a-5737-4f5a-8c24-0d12c21d2edb-g8qNQ9dfcn.svg"]}
- `GET /api/status/{job_id}` — Check status of a conversion job
    CURL Sample:
        curl http://172.16.3.1/api/svg/status/505e6a0a-5737-4f5a-8c24-0d12c21d2edb
        Responce:
        {"status":"success","pdf_exists":true,"pdf":"http:\/\/172.16.3.1\/storage\/505e6a0a-5737-4f5a-8c24-0d12c21d2edb.pdf","pngs":{"12":"http:\/\/172.16.3.1\/storage\/505e6a0a-5737-4f5a-8c24-0d12c21d2edb-505e6a0a-5737-4f5a-8c24-0d12c21d2edb-cDd9PziU6y.png","13":"http:\/\/172.16.3.1\/storage\/505e6a0a-5737-4f5a-8c24-0d12c21d2edb-505e6a0a-5737-4f5a-8c24-0d12c21d2edb-g8qNQ9dfcn.png"},"actual_pngs":2}

## Output Files

Converted files are saved in:

```
storage/app/public/
├── {job_id}/
    ├── output.pdf
    ├── page_1.png
    ├── page_2.png
    └── ...
```

## License

MIT License.