<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PageSeoController;
use App\Http\Controllers\PricingPlanController;
use App\Http\Controllers\DeveloperApiController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\YourPackageController;
use App\Http\Controllers\SaleController;

Route::get('/frodly-test', function (Request $request) {

    set_time_limit(0);
    ini_set('max_execution_time', 0);

    $numbers = [
        '01681636068',
        "01738606158",
        "01726319619",
        "01879200750",
        "01641389658",
        // "01812337616",
        // "01946377865",
        // "01711506053",
        // "01917984002",
        // "01796355555",
        // "01772366352",
        // "01537657779",
        // "01909302126",
        // "01879407777",
        // "01859001413",
        // "01304040910",
        // "01919858585",
        // "01919220992",
        // "01836257538",
        // "01957269480",
        // "01883270119",
        // "01798168537",
        // "01311145385",
        // "01979247649",
        // "01718117901",
        // "01776296648",
        // "01324523404",
        // "01852210052",
        // "01701014050",
        // "01788332338",
        // "01316469005",
    ];
    $responses = [];

    foreach ($numbers as $number) {
        $result = \App\Helpers\FrodlyHelper::getSteadFast($number);
        // ✅ LOG REQUEST (time + phone + ip)
        // Log::info('Courier API request', [
        //     'number' => $number,
        //     'time'   => now()->toDateTimeString(),
        //     'result' => $result,
        // ]);
        $responses[$number] = $result;
    }

    return response()->json([
        'status' => true,
        'data'   => $responses,
    ]);
});


Route::get('/sync-permissions', [AdminController::class, 'resyncPermissions'])->name('sync.permissions');
Route::get('/cc', function () {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    // \Illuminate\Support\Facades\Artisan::call('config:cache');
    return 'Cleared!';
});

Route::get('/', [HomeController::class, 'welcome'])->name('home');
Route::get('/checkout/{plan?}', [HomeController::class, 'checkout'])->name('checkout');
Route::post('/place-order', [HomeController::class, 'placeOrder'])->name('placeorder');
Route::get('/order-success/{orderId}', [HomeController::class, 'orderSuccess'])->name('order.success');

Route::get('/page/frodly', [HomeController::class, 'pageFrodly'])->name('page.frodly'); // Not used
Route::get('/get/frodly', [HomeController::class, 'getFrodly'])->name('get.frodly');

// Admin dashboard
Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/change-password', [ProfileController::class, 'editPassword'])->name('password.change');
    Route::put('/change-password', [ProfileController::class, 'updatePassword'])->name('password.update');
});


Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // Your Package
    Route::get('/your-package', [YourPackageController::class, 'index'])->name('your-package.index');
    // Route::post('/your-package/store', [YourPackageController::class, 'store'])->name('your-package.store');
    // Route::delete('/your-package/{id}', [YourPackageController::class, 'destroy'])->name('your-package.destroy');

    Route::post('/orders', [YourPackageController::class, 'storeDomain'])->name('domains.store');
    Route::put('/domains/{domain}', [YourPackageController::class, 'updateDomain'])->name('domains.update');
    Route::delete('/domains/{domain}', [YourPackageController::class, 'destroyDomain'])->name('domains.destroy');

    // Client Management
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients/store', [ClientController::class, 'store'])->name('clients.store');
    Route::post('/clients/update', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Pricing Management
    Route::get('/pricing-plans', [PricingPlanController::class, 'index'])->name('pricing-plans.index');
    Route::post('/pricing-plans/store', [PricingPlanController::class, 'store'])->name('pricing-plans.store');
    Route::delete('/pricing-plans/{id}', [PricingPlanController::class, 'destroy'])->name('pricing-plans.destroy');

    // Sales Management
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::post('/sales/store', [SaleController::class, 'store'])->name('sales.store');
    Route::put('/sales/{id}', [SaleController::class, 'update'])->name('sales.update'); // <-- Add this
    Route::delete('/sales/{id}', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::post('/sales/{sale}/upgrade', [SaleController::class, 'upgrade'])->name('sales.upgrade');


    // Developer API
    Route::get('/developer-api', [DeveloperApiController::class, 'index'])->name('developer-api.index');
    Route::post('/developer-api/generate-token', [DeveloperApiController::class, 'generateToken'])->name('developer-api.generate-token');


    /**----------------------------------------------------------------------------------------------
     * ----------------------------------------------------------------------------------------------
     * BACKEND TEMPLATE
     * ----------------------------------------------------------------------------------------------
     * ----------------------------------------------------------------------------------------------
     */
    Route::resource('roles', RoleController::class);

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/store', [ClientController::class, 'store'])->name('users.store');
    Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('setting.index');
    Route::post('/settings-update', [SettingController::class, 'update'])->name('setting.update');

    // SEO settings
    Route::get('seo-pages',[PageSeoController::class,'index'])->name('settings.seo.index');
    Route::post('seo-pages/{page}',[PageSeoController::class,'update'])->name('settings.seo.update');
});

require __DIR__.'/auth.php';
