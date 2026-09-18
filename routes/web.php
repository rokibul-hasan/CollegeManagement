<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->name('api.')->group(function () {
    Route::get('site', [Api\SiteController::class, 'show'])->name('site');
    Route::get('notices', [Api\NoticeController::class, 'index'])->name('notices.index');
    Route::get('notices/{id}', [Api\NoticeController::class, 'show'])->whereNumber('id')->name('notices.show');
    Route::get('pages/{slug}', [Api\PageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('pages.show');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');
        Route::get('me', [Admin\AuthController::class, 'me'])->name('me');

        Route::middleware('auth')->group(function () {
            Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');

            // Super admin only — kept outside the permission-based group so it works
            // even before the permission tables exist.
            Route::middleware('super-admin')->prefix('system')->name('system.')->group(function () {
                Route::get('/', [Admin\SystemController::class, 'status'])->name('status');
                Route::post('migrate', [Admin\SystemController::class, 'migrate'])->name('migrate');
                Route::post('clear-cache', [Admin\SystemController::class, 'clearCache'])->name('clear-cache');
            });
        });

        Route::middleware(['auth', 'admin'])->group(function () {
            Route::put('profile', [Admin\AuthController::class, 'updateProfile'])->name('profile');
            Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');

            // Read-only: the admin layout shows the site name and logo to everyone with access.
            Route::get('settings', [Admin\SettingController::class, 'show'])->name('settings.show');
            Route::post('settings', [Admin\SettingController::class, 'update'])->middleware('permission:settings.manage')->name('settings.update');

            Route::middleware('permission:menus.manage')->group(function () {
                Route::post('menus/reorder', [Admin\MenuController::class, 'reorder'])->name('menus.reorder');
                Route::apiResource('menus', Admin\MenuController::class)->except('show');
            });

            Route::middleware('permission:notices.manage')->group(function () {
                Route::apiResource('notice-categories', Admin\NoticeCategoryController::class)
                    ->parameters(['notice-categories' => 'notice_category'])
                    ->except('show');
                Route::apiResource('notices', Admin\NoticeController::class);
            });

            Route::middleware('permission:pages.manage')->group(function () {
                Route::apiResource('pages', Admin\PageController::class);
                Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
            });

            Route::apiResource('users', Admin\UserController::class)->except('show')->middleware('permission:users.manage');
            Route::apiResource('roles', Admin\RoleController::class)->except('show')->middleware('permission:roles.manage');
        });
    });
});

Route::view('/{any?}', 'app')->where('any', '^(?!api|uploads|up$).*$')->name('spa');
