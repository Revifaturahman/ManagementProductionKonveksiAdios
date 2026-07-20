<?php

use App\Http\Controllers\AuthControllerWebsite;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\FinishedProductController;
use App\Http\Controllers\FrontPageController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionAccountController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionPeriodController;
use App\Http\Controllers\ProductionPlanningController;
use App\Http\Controllers\ProductionReportController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\RawMaterialMasterController;
use App\Http\Controllers\RawMaterialStockMovementController;
use App\Http\Controllers\SemiProductController;
use App\Http\Controllers\VariantController;
use Illuminate\Support\Facades\Route;

Route::get('/switch-menu/{mode}', function ($mode) {

    if (in_array($mode, ['planning', 'main'])) {
        session(['menu_mode' => $mode]);
    }

    return back();
});

// routes/web.php

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthControllerWebsite::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthControllerWebsite::class, 'login'])
        ->name('login.post');

    Route::get('/forgot-password', [AuthControllerWebsite::class, 'forgotPassword'])
        ->name('forgot-password');

    Route::post('/forgot-password', [AuthControllerWebsite::class, 'forgotPasswordPost'])
        ->name('forgot-password.post');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthControllerWebsite::class, 'logout'])
        ->name('logout');

    Route::get('/', [FrontPageController::class, 'index']);

    Route::resource('rawMaterial', RawMaterialController::class);

    Route::resource('productionBatch', ProductionBatchController::class);

    Route::resource('courier', CourierController::class);

    Route::resource('workers', ProductionController::class);

    Route::resource('product', ProductController::class);

    Route::resource('variant', VariantController::class);

    Route::resource('production_inventory', InventoryController::class);

    Route::put(
        '/production-inventory/bulk-update',
        [InventoryController::class, 'bulkUpdate']
    )->name('production_inventory.bulk_update');

    Route::resource(
        '/production_period',
        ProductionPeriodController::class
    );
    
    Route::resource(
        '/production_planning',
        ProductionPlanningController::class
    );

    Route::post(
        '/production-planning/generate-suggestions',
        [
            ProductionPlanningController::class,
            'generateSuggestions'
        ]
    )->name(
        'production_planning.generate_suggestions'
    );

    Route::resource(
        'raw_material_master',
        RawMaterialMasterController::class
    );

    Route::resource(
        'raw_material_stock_movement',
        RawMaterialStockMovementController::class
    );

    Route::get(
        '/inventory-product',
        [InventoryProductController::class, 'index']
    )->name('inventory-product.index');

    Route::post(
        '/production-account',
        [ProductionAccountController::class, 'create']
    )->name('production-account.create');

    Route::delete(
        '/production-account/{id}',
        [ProductionAccountController::class, 'delete']
    )->name('production-account.delete');

    Route::get('/production_report', [ProductionReportController::class, 'index'])->name('reports.index');
});
