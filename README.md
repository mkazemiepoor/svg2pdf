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
- `GET /api/status/{job_id}` — Check status of a conversion job

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