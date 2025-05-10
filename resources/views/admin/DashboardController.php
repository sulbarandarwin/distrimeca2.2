<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use Spatie\Activitylog\Models\Activity; // Necesario para el log de actividad
use Illuminate\Support\Facades\Auth; // Aunque no se use directamente 'Auth::user()' aquí, es buena práctica si lo necesitaras

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard de administración.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener los conteos para las tarjetas de estadísticas
        $userCount = User::count();
        $supplierCount = Supplier::count();
        $productCount = Product::count();

        // Obtener las últimas 5 actividades del sistema para el log
        // Asegúrate de que spatie/laravel-activitylog esté configurado y funcionando
        $latestActivities = Activity::latest()->take(5)->get();

        // Pasamos las variables a la vista 'admin.dashboard'
        return view('admin.dashboard', compact(
            'userCount',
            'supplierCount',
            'productCount',
            'latestActivities'
            // Puedes añadir Auth::user() aquí si tu vista lo necesita, ej: 'user' => Auth::user()
        ));
    }
}