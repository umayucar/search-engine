<?php

use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ContentSyncController;
use Illuminate\Support\Facades\Route;

// Search API routes
Route::prefix('search')->group(function () {
    Route::get('/', [SearchController::class, 'search'])->name('api.search');
    Route::get('/stats', [SearchController::class, 'stats'])->name('api.search.stats');
});

// Content sync API routes
Route::prefix('sync')->group(function () {
    Route::post('/', [ContentSyncController::class, 'sync'])->name('api.sync');
    Route::get('/status', [ContentSyncController::class, 'status'])->name('api.sync.status');
});
