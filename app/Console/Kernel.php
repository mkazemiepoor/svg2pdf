<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');  // این خط دستورات را بارگذاری می‌کند

        require base_path('routes/console.php');  // دستورات اضافی را بارگذاری می‌کند
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ﺎﯿﻨﺟﺍ ﮎﺎﻤﻧﺩ ﺖﻤﯾﺯ ﮎﺭﺪﻧ ﻑﺎﯿﻟ<200c>ﻫﺎﯾ ﻕﺪﯿﻤﯾ ﺭﻭ ﺰﻣﺎﻧ<200c>ﺒﻧﺪﯾ ﻢﯾ<200c>ﮑﻨﯿﻣ
        $schedule->command('clean:converted')->daily();  // دستور clean:converted را به صورت روزانه تنظیم می‌کند
    }
}

