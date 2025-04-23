<?php

namespace App\Jobs;

use App\Services\ImageToPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConvertImageJob implements ShouldQueue
{
    use Dispatchable;//,Queueable, SerializesModels, InteractsWithQueue;

    protected $batchId;
    protected $svgPaths;

    public function __construct($batchId, $svgPaths)
    {
        $this->batchId = $batchId;
        $this->svgPaths = $svgPaths;
    }

    public function handle(ImageToPdfService $imageToPdfService)
    //public function handle()
    {
        Log::info("Converting SVG to PDF and PNG for batch {$this->batchId}");

        Log::debug("Calling convertSvgToImageAndPdf with", [
            'svgPaths' => $this->svgPaths,
            'batchId' => $this->batchId
        ]);
        $result = $imageToPdfService->convertSvgToImageAndPdf($this->svgPaths, $this->batchId);
        Log::debug("Conversion result", ['result' => $result]);

        
        if (!$result) {
            Log::error("Failed to process SVGs for batch {$this->batchId}");
            return;
        }

        Log::info("Conversion completed for batch {$this->batchId}");
    }
}

