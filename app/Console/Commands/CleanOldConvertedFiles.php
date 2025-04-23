<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanOldConvertedFiles extends Command
{
    protected $signature = 'clean:converted-files';
    protected $description = 'Delete old JPG and PDF files from the storage after a certain number of days';

    public function handle()
    {
        $days = 3; // تعداد روزهایی که فایل‌ها باید نگه‌داری بشن
        $path = storage_path('app/converted');

        if (!File::exists($path)) {
            $this->info("Directory $path does not exist.");
            return;
        }

        $files = File::allFiles($path);
        $deleted = 0;

        foreach ($files as $file) {
            if (in_array($file->getExtension(), ['jpg', 'pdf'])) {
                $lastModified = $file->getMTime();
                if (now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastModified)) > $days) {
                    File::delete($file->getRealPath());
                    $deleted++;
                }
            }
        }

        $this->info("Deleted $deleted old files.");
    }
}

