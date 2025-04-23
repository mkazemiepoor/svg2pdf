<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SvgController;

Route::middleware('api')->post('svg/upload', [SvgController::class, 'upload']);
Route::middleware('api')->get('svg/status/{batch_id}', [SvgController::class, 'status']);

