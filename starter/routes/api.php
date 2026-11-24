<?php

use App\Http\Controllers\CraneDesignController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::apiResource('designs', CraneDesignController::class)->except('update');
Route::put('designs/{design}', [CraneDesignController::class, 'update'])->name('designs.update');
