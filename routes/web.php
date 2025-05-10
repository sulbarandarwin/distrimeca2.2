<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
// --- AJUSTE DE IMPORTS PARA DASHBOARD ---
use App\Http\Controllers\DashboardController; // Para el dashboard general
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController; // Para el dashboard de Admin (con alias)
// --- FIN AJUSTE ---
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ProductFailureController;
use App\Models\State;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\AiSearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// --- RUTAS AUTENTICADAS GENERALES ---
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::patch('/dark-mode', [ProfileController::class, 'updateDarkModePreference'])->name('dark-mode.update');
    });

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/results', [SearchController::class, 'index'])->name('search.results');
    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
    Route::post('/search/export', [SearchController::class, 'exportExcel'])->name('search.export');
    Route::get('/search/autocomplete-supplier', [SearchController::class, 'autocompleteSupplier'])->name('search.autocomplete-supplier');

    Route::get('/api/states-by-country/{countryId}', function ($countryId) {
        if (!is_numeric($countryId)) { return response()->json([], 400); }
        $states = State::where('country_id', $countryId)->orderBy('name')->pluck('name', 'id');
        return response()->json($states);
    })->name('api.states_by_country');

    // --- RUTAS PARA BÚSQUEDA IA ---
    Route::prefix('ai-search')->name('ai.search.')->middleware('auth')->group(function () {
        Route::get('/', [AiSearchController::class, 'index'])->name('index');
        Route::post('/products', [AiSearchController::class, 'searchProducts'])->name('products');
    });

}); // Fin grupo auth

// --- RUTAS PARA CREAR INVITACIONES ---
Route::middleware(['auth', 'permission:enviar invitaciones'])->prefix('invitations')->name('invitations.')->group(function () {
    Route::get('/create', [InvitationController::class, 'create'])->name('create');
    Route::post('/', [InvitationController::class, 'store'])->name('store');
});

// --- RUTAS PÚBLICAS PARA ACEPTAR INVITACIÓN ---
Route::get('/invitation/accept/{token}', [InvitationController::class, 'accept'])->middleware('signed')->name('invitation.accept');
Route::post('/invitation/register', [InvitationController::class, 'processRegistration'])->middleware('guest')->name('invitation.register');

// --- RUTAS PARA LOGIN SOCIAL (GOOGLE) ---
Route::get('/auth/google/redirect', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');


// --- GRUPO DE ADMINISTRACIÓN ---
Route::middleware(['auth', 'verified'])
    ->prefix('admin')->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
             ->middleware('role:Admin')
             ->name('dashboard');

        Route::resource('users', UserController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('products', ProductController::class);

        // --- RUTAS PARA FALLAS DE PRODUCTOS ---
        Route::resource('failures', ProductFailureController::class)->parameters(['failures' => 'productFailure']);
        Route::get('failures/export', [ProductFailureController::class, 'export']) // DESCOMENTADA
             ->name('failures.export')
             ->middleware('permission:export product_failures'); // Permiso aplicado
        // --- FIN RUTAS FALLAS ---

        Route::prefix('audit-log')->name('audit-log.')->middleware('permission:view audit log|clean audit log')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/export', [AuditLogController::class, 'export'])->name('export');
            Route::post('/clean/older-3-months', [AuditLogController::class, 'cleanOlderThan3Months'])->name('clean.older-3-months')->middleware('permission:clean audit log');
            Route::post('/clean/by-year', [AuditLogController::class, 'cleanByYear'])->name('clean.by-year')->middleware('permission:clean audit log');
            Route::post('/clean/all', [AuditLogController::class, 'cleanAll'])->name('clean.all')->middleware('permission:clean audit log');
        });

        Route::prefix('settings')->name('settings.')->middleware('permission:manage settings')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/', [SettingController::class, 'update'])->name('update');
        });

        Route::prefix('import-export')->name('import_export.')->group(function () {
            Route::get('/', [ImportExportController::class, 'index'])->middleware('permission:import suppliers|import products|export suppliers|export products')->name('index');
            Route::post('/import/suppliers', [ImportExportController::class, 'importSuppliers'])->middleware('permission:import suppliers')->name('import.suppliers');
            Route::post('/import/products', [ImportExportController::class, 'importProducts'])->middleware('permission:import products')->name('import.products');
            Route::get('/export/suppliers', [ImportExportController::class, 'exportSuppliers'])->middleware('permission:export suppliers')->name('export.suppliers');
            Route::get('/export/products', [ImportExportController::class, 'exportProducts'])->middleware('permission:export products')->name('export.products');
        });

        Route::prefix('roles')->name('roles.')->middleware(['permission:manage roles'])->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('update.permissions');
        });
    });

require __DIR__.'/auth.php';
// La llave '}' extra al final ha sido eliminada