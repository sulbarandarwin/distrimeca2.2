<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\SearchController; // <--- Importado
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\SettingController;
use App\Models\State; // Para la ruta API

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () { return view('welcome'); });

Route::get('/dashboard', function () { return view('dashboard'); })
    ->middleware(['auth', 'verified'])->name('dashboard');

// --- RUTA API PARA ESTADOS ---
Route::get('/api/states-by-country/{countryId}', function ($countryId) {
    if (!is_numeric($countryId)) { return response()->json([], 400); }
    $states = State::where('country_id', $countryId)->orderBy('name')->pluck('name', 'id');
    return response()->json($states);
})->middleware('auth')->name('api.states_by_country');

// --- RUTAS QUE REQUIEREN LOGIN ---
Route::middleware('auth')->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Búsqueda
    Route::get('/search', [SearchController::class, 'index'])->name('search.index'); // <-- Ruta para mostrar formulario
    Route::get('/search/results', [SearchController::class, 'results'])->name('search.results');
    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
    Route::post('/search/export', [SearchController::class, 'exportExcel'])->name('search.export');
});

// --- RUTAS DE ADMINISTRACIÓN ---
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('products', ProductController::class);
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/import-export', [ImportExportController::class, 'index'])->name('import_export.index');
        Route::post('/import/suppliers', [ImportExportController::class, 'importSuppliers'])->name('import.suppliers');
        Route::post('/import/products', [ImportExportController::class, 'importProducts'])->name('import.products');
        Route::get('/export/suppliers', [ImportExportController::class, 'exportSuppliers'])->name('export.suppliers');
        Route::get('/export/products', [ImportExportController::class, 'exportProducts'])->name('export.products');
});

// Rutas de autenticación
require __DIR__.'/auth.php';

