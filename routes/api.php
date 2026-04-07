<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ApplicationController;
use App\Http\Middleware\VerifyApiKey;

Route::middleware(['verify.apikey'])->group(function () {
    Route::get('/app-ads', [ApplicationController::class, 'getAppAdsByPackage']);
});