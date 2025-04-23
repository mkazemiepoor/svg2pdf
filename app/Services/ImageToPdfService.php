<?php
namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImageToPdfService
{
public function convertSvgToImageAndPdf(array $svgPaths, string $batchId): bool
{
    Log::info("🚀 Running convertSvgToImageAndPdf implementation V3");
    Log::debug("🔍 SVG paths received for batch {$batchId}: " . json_encode($svgPaths));

    $tmpDir = storage_path('app/tmp');
    if (!File::exists($tmpDir)) {
        File::makeDirectory($tmpDir, 0755, true);
        Log::debug("📁 Created tmp folder: {$tmpDir}");
    }

    $pdfPages = [];
    foreach ($svgPaths as $svgPath) {
        Log::debug("🔍 Processing file: {$svgPath}");
        if (!file_exists($svgPath)) {
            Log::error("🚫 SVG file does not exist at: {$svgPath}");
            continue;
        }
        if (!is_readable($svgPath)) {
            Log::error("🚫 SVG file is not readable: {$svgPath}");
            continue;
        }

        $baseName = pathinfo($svgPath, PATHINFO_FILENAME);
        $outputName = "{$batchId}-{$baseName}";

        $pngPath = storage_path("app/public/{$outputName}.png");
        $pdfPagePath = "{$tmpDir}/{$outputName}.pdf";

        if (!File::exists(storage_path('app/public'))) {
            File::makeDirectory(storage_path('app/public'), 0755, true);
        }
        // Create PNG
        $binary = '/usr/bin/rsvg-convert'; // or use `which rsvg-convert` to be sure

        //$pngCommand = "rsvg-convert -f png -o '{$pngPath}' '{$svgPath}'";
        $pngCommand = "{$binary} -f png -o '{$pngPath}' '{$svgPath}' 2>&1";
	exec($pngCommand. ' 2>&1', $pngOutput, $pngResultCode);
        Log::debug("🖼️ PNG command: {$pngCommand}");
        Log::debug("🖼️ PNG exec result code: {$pngResultCode}");
        //Log::debug("🖼️ PNG exec output: " . implode("\n", $pngOutput));

        if ($pngResultCode !== 0 || !file_exists($pngPath)) {
            Log::error("❌ rsvg-convert PNG failed for {$svgPath}");
            continue;
        }

        // Create single-page PDF from SVG
        //$pdfCommand = "rsvg-convert -f pdf -o '{$pdfPagePath}' '{$svgPath}' 2>&1";
        $pdfCommand = "{$binary} -f pdf -o '{$pdfPagePath}' '{$svgPath}' 2>&1";
        exec($pdfCommand, $pdfOutput, $pdfResultCode);
        Log::debug("📄 PDF command: {$pdfCommand}");
        Log::debug("📄 PDF result code: {$pdfResultCode}");
        //Log::debug("🖼️ PDF exec output: " . implode("\n", $pdfOutput));

        if ($pdfResultCode === 0 && file_exists($pdfPagePath)) {
            $pdfPages[] = $pdfPagePath;
        } else {
            Log::error("❌ rsvg-convert PDF failed for {$svgPath}");
        }
    }

    // Merge PDFs if available
    if (count($pdfPages) > 0) {
        Log::debug("Merge PDFs if available");
        $mergedPdfPath = storage_path("app/public/{$batchId}.pdf");
        //Log::debug("Merge PDFs path:{$mergedPdfPath}");
        $pdf = new \Clegginabox\PDFMerger\PDFMerger;
        foreach ($pdfPages as $page) {
            $pdf->addPDF($page, 'all');
        }
        $pdf->merge('file', $mergedPdfPath);
        Log::info("📦 Merged PDF created at: {$mergedPdfPath}");
    } else {
        Log::error("🚫 No PDFs created for batch {$batchId}");
    }

    // Clean up temp files
    foreach ($pdfPages as $tmpPdf) {
        File::delete($tmpPdf);
    }
    
    if(file_exists($mergedPdfPath)){
        return true;
    }
    else{
        return false;
    }
}

}

