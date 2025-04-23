<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanConverted extends Command
{
    // مشخصات دستور artisan
    protected $signature = 'clean:converted';
    protected $description = 'Delete converted files older than 3 days';

    // متد اصلی که اجرا می‌شود
    public function handle()
    {
        // مسیر پوشه فایل‌های تبدیل شده
        $dir = storage_path('app/converted');
        
        // چک کردن اینکه آیا پوشه وجود دارد یا نه
        if (!File::exists($dir)) {
            $this->info("📁 Directory does not exist: $dir");
            return;
        }

        // دریافت لیست فایل‌ها در پوشه
        $files = File::files($dir);
        $deleted = 0;

        // بررسی و حذف فایل‌های قدیمی‌تر از 3 روز
        foreach ($files as $file) {
            if (Carbon::now()->diffInDays(Carbon::createFromTimestamp($file->getMTime())) > 3) {
                File::delete($file);
                $deleted++;
            }
        }

        // چاپ تعداد فایل‌های حذف شده
        $this->info("🧹 Deleted $deleted old converted files from $dir.");
    }
}
