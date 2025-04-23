<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ConvertImageJob;
use App\Services\ImageToPdfService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SvgController extends Controller
{
    public function upload(Request $request)
    {
        Log::debug('Upload Request Data:', $request->all());

        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'required|file|mimetypes:image/svg+xml,text/xml,application/xml,text/plain|max:10240',
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation Error:', $e->errors());
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }

        $batchId = (string) Str::uuid();
        $svgPaths = [];
        Log::debug('for:');
        foreach ($request->file('files') as $file) {
            $filename = $batchId . '-' . Str::random(10) . '.svg';
            $path = 'public/svg/' . $filename;

            try {
                $storedPath = Storage::disk('public')->putFileAs('svg', $file, $filename);
                $fullPath = storage_path('app/public/' . $storedPath);

                if (!file_exists($fullPath)) {
                    Log::error("File claims to be stored but not found at: " . $fullPath);
                } else {
                    Log::info("File stored successfully at: " . $fullPath);
                    $svgPaths[] = $fullPath; // ✅ این خط اضافه شد
                }
            } catch (\Exception $e) {
                Log::error("Exception while storing file: " . $e->getMessage());
            }
        }

    // Dispatch the conversion job
        Log::debug("🚚 Dispatching ConvertImageJob with SVG paths:", $svgPaths);
        ConvertImageJob::dispatch($batchId, $svgPaths);

        return response()->json([
            'batch_id' => $batchId,
            'status' => 'Processing',
            'url' => url(''),
            'svg' => $svgPaths
        ]);
    }

    public function status($batchId)
    {
        $disk = Storage::disk('public');

        // بررسی فایل PDF نهایی
        $pdfPath = "{$batchId}.pdf";
        $pdfExists = $disk->exists($pdfPath);

        // همه فایل‌های public (در مسیر storage/app/public)
        $allFiles = $disk->files();

        // فیلتر فقط PNGهای مربوط به batchId
        $pngs = array_filter($allFiles, function ($f) use ($batchId) {
            return str_starts_with($f, "{$batchId}-") && str_ends_with($f, '.png');
        });

        // اگر PDF وجود داشته باشه و حداقل یک PNG باشه = complete
        $isComplete = $pdfExists && count($pngs) > 0;

        return response()->json([
            'status' => $isComplete ? 'success' : 'processing',
            'pdf_exists' => $pdfExists,
            'pdf' => $pdfExists ? url("storage/{$pdfPath}") : null,
            'pngs' => array_map(fn($f) => url("storage/{$f}"), $pngs),
            'actual_pngs' => count($pngs),
        ]);
    }
    
    public function statusold($batchId)
    {
        $pdfExists = Storage::disk('public')->exists("{$batchId}.pdf");
        $jpgs = Storage::files("public/app/public/{$batchId}");

        return response()->json([
            'pdf_exists' => $pdfExists,
            'pdf' => url("storage/{$batchId}.pdf"),
            'jpgs' => $jpgs
        ]);
    }
}

