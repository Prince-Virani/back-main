<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Middleware\AdminAuth;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\CommonPageController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AdPositionController;
use App\Http\Controllers\AdTxtController;
use App\Http\Controllers\TagManagerController;
use App\Models\Website;
use App\Http\Controllers\AdUnitController;
use App\Http\Controllers\AdUnitSizeController;
use App\Http\Controllers\Api\AdUnitDetailController;
use App\Http\Controllers\Api\CreateAdUnitController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\VerticalController;
use App\Http\Controllers\GoogleAdSettingController;
use App\Http\Controllers\APIKeyController;
use App\Http\Controllers\NativeAdController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\FirestoreAppSettingController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;

Route::get('/', [AdminAuthController::class, 'login'])->name('home');

Auth::routes();

Route::get('/login', [AdminAuthController::class, 'login'])->name('login');
// Route::post('/login', [AdminAuthController::class, 'authenticate'])->name('authenticate');
// Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Protected Admin Routes (Require Auth)
Route::group(['middleware' => ['auth']], function () {
    Route::get('/dashboard', function () {
        return view('pages/dashboard');
    })->name('dashboard');


    Route::get('/pages', function () {
        $websites = Website::where('status_flag', 0)->get();
        return view('pages/pages.index', compact('websites'));
    })->name('pages.index');
    Route::get('/pages/data-list', [PageController::class, 'index'])->name('pages.data-list');
    Route::resource('pages', PageController::class)->except(['index']);



    Route::get('/Commonpages', function () {
        $websites = Website::where('status_flag', 0)->get();
        return view('pages/Commonpages.index', compact('websites'));
    })->name('Commonpages.index');
    Route::get('/Commonpages/data', [CommonPageController::class, 'index'])->name('Commonpages.data');
    // Route::resource('Commonpages', CommonPageController::class)->except(['index']);
    Route::resource('Commonpages', CommonPageController::class)->except(['index'])->names([
        'create' => 'Commonpages.create',
        'store' => 'Commonpages.store',
        'edit' => 'Commonpages.edit',
        'update' => 'Commonpages.update',
        'destroy' => 'Commonpages.destroy',
    ]);

    Route::resource('categories', CategoryController::class)->names([
        'index' => 'categories.index',
        'create' => 'categories.create',
        'store' => 'categories.store',
        'edit' => 'categories.edit',
        'update' => 'categories.update',
        'destroy' => 'categories.destroy',
    ]);

    Route::resource('verticals', VerticalController::class)->names([
        'index' => 'verticals.index',
        'create' => 'verticals.create',
        'store' => 'verticals.store',
        'edit' => 'verticals.edit',
        'update' => 'verticals.update',
        'destroy' => 'verticals.destroy',
    ]);

    Route::resource('websites', WebsiteController::class)->names([
        'index' => 'websites.index',
        'create' => 'websites.create',
        'store' => 'websites.store',
        'edit' => 'websites.edit',
        'update' => 'websites.update',
        'destroy' => 'websites.destroy',
    ]);


    Route::patch('/websites/{website}/toggle-ads', [WebsiteController::class, 'toggleAds'])->name('websites.toggleAds');
    Route::patch('/websites/{website}/toggle-analytics', [WebsiteController::class, 'toggleAnalytics'])->name('websites.toggleAnalytics');
    Route::patch('/websites/{website}/purge-cache', [WebsiteController::class, 'purgeCache'])
        ->name('websites.purge-cache');
    Route::patch('websites/{website}/pause-zone', [WebsiteController::class, 'pauseZone'])
        ->name('websites.pause-zone');
    Route::post('websites/{website}/clear-cache', [WebsiteController::class, 'clearCache'])->name('websites.clearCache');
    Route::post('websites/clear-all-cache', [WebsiteController::class, 'clearAllCache'])->name('websites.clearAllCache');
    Route::get('websites/{website}/view-cache', [WebsiteController::class, 'viewCache'])->name('websites.viewCache');



    Route::resource('themes', ThemeController::class)->names([
        'index' => 'themes.index',
        'create' => 'themes.create',
        'store' => 'themes.store',
        'edit' => 'themes.edit',
        'update' => 'themes.update',
        'destroy' => 'themes.destroy',
    ]);


    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/api/ga-data', [AnalyticsController::class, 'fetchGaData'])->name('api.ga-data');

    Route::resource('adpositions', AdPositionController::class)->names([
        'index' => 'adpositions.index',
        'create' => 'adpositions.create',
        'store' => 'adpositions.store',
        'edit' => 'adpositions.edit',
        'update' => 'adpositions.update',
        'destroy' => 'adpositions.destroy',
    ]);

    Route::resource('adstxt', AdTxtController::class)->names([
        'index' => 'adstxt.index',
        'create' => 'adstxt.create',
        'store' => 'adstxt.store',
        'edit' => 'adstxt.edit',
        'update' => 'adstxt.update',
        'destroy' => 'adstxt.destroy',
    ]);


    Route::resource('tagmanagers', TagManagerController::class)->names([
        'index' => 'tagmanagers.index',
        'create' => 'tagmanagers.create',
        'store' => 'tagmanagers.store',
        'edit' => 'tagmanagers.edit',
        'update' => 'tagmanagers.update',
        'destroy' => 'tagmanagers.destroy',
    ]);

    Route::get('/ad-units', [AdUnitController::class, 'index'])->name('ad-units.index');
    Route::get('/ad-unit-details/{adUnitId}', [AdUnitDetailController::class, 'show']);
    Route::post('/create-ad-unit', [CreateAdUnitController::class, 'store']);
    Route::patch('/adunits/{id}/toggle-status', [AdUnitController::class, 'toggleStatus'])->name('adunits.toggleStatus');
    Route::patch('/adunits/{id}/toggle-lazy', [AdUnitController::class, 'toggleLazy'])->name('adunits.toggleLazy');
    Route::delete('/adunits/{id}', [AdUnitController::class, 'destroy'])->name('adunits.destroy');


    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [ApplicationController::class, 'index'])->name('index');
        Route::get('/create', [ApplicationController::class, 'create'])->name('create');
        Route::post('/', [ApplicationController::class, 'store'])->name('store');
        Route::get('{application}/edit', [ApplicationController::class, 'edit'])->name('edit');
        Route::get('{application}/fetchData', [ApplicationController::class, 'fetchData'])->name('fetchData');
        Route::put('applications/{application}', [ApplicationController::class, 'update'])->name('update');
        Route::get('{application}/play-json', [ApplicationController::class, 'viewPlayJson'])->name('play-json');
    });

    Route::resource('google-settings', GoogleAdSettingController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->names([
            'index' => 'google-settings.index',
            'create' => 'google-settings.create',
            'store' => 'google-settings.store',
            'edit' => 'google-settings.edit',
            'update' => 'google-settings.update',
        ]);


    Route::get('api_keys', [APIKeyController::class, 'index'])->name('api_keys.index');
    Route::post('api_keys/{apiKey}/toggle-status', [APIKeyController::class, 'toggleStatus']);
    Route::delete('/applications/{application}/api-keys', [ApplicationController::class, 'deleteApiKeyFromBody'])
        ->name('applications.api_keys.delete');

    Route::get('/native-ads',      [NativeAdController::class, 'index'])->name('native_ads.index');
    Route::get('/native-ads/create', [NativeAdController::class, 'create'])->name('native_ads.create');
    Route::post('/native-ads',     [NativeAdController::class, 'store'])->name('native_ads.store');
    Route::patch('native-ads/{native_ad}/toggle-status', [NativeAdController::class, 'toggleStatus'])
        ->name('native_ads.toggle');

    Route::resource('links', LinkController::class)
        ->except(['show', 'create', 'edit'])
        ->names([
            'index'   => 'links.index',
            'store'   => 'links.store',
            'update'  => 'links.update',
            'destroy' => 'links.destroy',
        ]);

    Route::patch('links/{link}/toggle', [LinkController::class, 'toggle'])->name('links.toggle');

    // Route::resource('firestore-app-settings', FirestoreAppSettingController::class)->except(['show']);

    Route::resource('firestore-app-settings', FirestoreAppSettingController::class)
        ->except(['show'])
        ->names([
            'index'   => 'firestore-app-settings.index',
            'store'   => 'firestore-app-settings.store',
            'create'  => 'firestore-app-settings.create',
            'edit'    => 'firestore-app-settings.edit',
            'update'  => 'firestore-app-settings.update',
            'destroy' => 'firestore-app-settings.destroy',
        ]);

    Route::post('firestore-app-settings/{firestore_app_setting}/toggle', [FirestoreAppSettingController::class, 'toggle'])
        ->name('firestore-app-settings.toggle');

    Route::resource('users', UserController::class)->names([
        'index' => 'users.index',
        'create' => 'users.create',
        'store' => 'users.store',
        'edit' => 'users.edit',
        'update' => 'users.update',
        'destroy' => 'users.destroy',
    ]);

    Route::resource('roles', RoleController::class)->names([
        'index' => 'roles.index',
        'create' => 'roles.create',
        'store' => 'roles.store',
        'show' => 'roles.show',
        'edit' => 'roles.edit',
        'update' => 'roles.update',
        'destroy' => 'roles.destroy',
    ]);
});
