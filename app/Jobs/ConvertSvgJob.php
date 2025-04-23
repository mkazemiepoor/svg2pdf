<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\Process\Process;

class ConvertSvgJob implements ShouldQueue
{
    use Queueable;

    protected $uuid;
    protected $svgPath;

    public function __construct($uuid, $svgPath)
    {
        $this->uuid = $uuid;
        $this->svgPath = Storage::disk('local')->path($svgPath);
    }

    public function handle()
    {
        Log::info("🧪 Using updated ConvertSvgJob at line: " . __LINE__);
        $outputDir = storage_path("app/public/converted");
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $pdfPath = "{$outputDir}/{$this->uuid}.pdf";
        $jpgPath = "{$outputDir}/{$this->uuid}.jpg";

        $pdf = new Process(['rsvg-convert', '-f', 'pdf', '-o', $pdfPath, $this->svgPath]);
        $jpg = new Process(['rsvg-convert', '-f', 'png', '-o', $jpgPath, $this->svgPath]);

        $pdf->run();
        $jpg->run();

        if ($pdf->isSuccessful() && $jpg->isSuccessful()) {
            Storage::disk('local')->put("svg/{$this->uuid}.status", 'done');
        } else {
            Storage::disk('local')->put("svg/{$this->uuid}.status", 'error');
        }
    }
}

