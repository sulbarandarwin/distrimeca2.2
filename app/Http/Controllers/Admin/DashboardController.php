<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Setting; // Para obtener configuraciones si es necesario
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth; // Para el usuario actual si es necesario
use Illuminate\Support\Facades\Route; // Para verificar si las rutas existen

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard de administración con estadísticas y accesos directos.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // --- Estadísticas del Sistema ---
        $userCount = User::count();
        $supplierCount = Supplier::count();
        $productCount = Product::count();
        $activeRoles = \Spatie\Permission\Models\Role::count(); // Conteo de roles definidos

        // Ejemplo: Conteo de usuarios por rol principal (puedes ajustar esto)
        $usersByRole = User::selectRaw('
                SUM(CASE WHEN model_has_roles.role_id = (SELECT id FROM roles WHERE name = "Admin") THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN model_has_roles.role_id = (SELECT id FROM roles WHERE name = "Proveedor") THEN 1 ELSE 0 END) as proveedor_count,
                SUM(CASE WHEN model_has_roles.role_id = (SELECT id FROM roles WHERE name = "Vendedor") THEN 1 ELSE 0 END) as vendedor_count,
                SUM(CASE WHEN model_has_roles.role_id = (SELECT id FROM roles WHERE name = "Cliente") THEN 1 ELSE 0 END) as cliente_count
            ')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->first();


        // --- Última Actividad del Sistema ---
        $latestActivities = Activity::with('causer') // Cargar el causante para mostrar el nombre
                                    ->latest()
                                    ->take(5) // Obtener las últimas 5 actividades
                                    ->get();

        // --- Accesos Directos ---
        // Creamos un array de accesos directos verificando si el usuario tiene permiso y si la ruta existe
        $quickAccessLinks = [];

        if (Auth::user()->can('manage users') && Route::has('admin.users.index')) {
            $quickAccessLinks['users'] = [
                'route' => route('admin.users.index'),
                'label' => 'Gestionar Usuarios',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' // Icono de usuarios
            ];
        }
        if (Auth::user()->can('manage suppliers') && Route::has('admin.suppliers.index')) {
            $quickAccessLinks['suppliers'] = [
                'route' => route('admin.suppliers.index'),
                'label' => 'Gestionar Proveedores',
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z' // Icono de "tienda" o similar
            ];
        }
        if (Auth::user()->can('view products') && Route::has('admin.products.index')) { // O un permiso más específico si es necesario
            $quickAccessLinks['products'] = [
                'route' => route('admin.products.index'),
                'label' => 'Gestionar Productos',
                'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' // Icono de productos
            ];
        }
        if (Auth::user()->can('manage roles') && Route::has('admin.roles.index')) {
            $quickAccessLinks['roles'] = [
                'route' => route('admin.roles.index'),
                'label' => 'Roles y Permisos',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.588-3.766z' // Icono de escudo
            ];
        }
         if (Auth::user()->can('manage settings') && Route::has('admin.settings.index')) {
            $quickAccessLinks['settings'] = [
                'route' => route('admin.settings.index'),
                'label' => 'Ajustes Generales',
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM10 11a1 1 0 110-2 1 1 0 010 2z' // Icono de ajustes
            ];
        }
        if (Auth::user()->can('view audit log') && Route::has('admin.audit-log.index')) {
            $quickAccessLinks['audit'] = [
                'route' => route('admin.audit-log.index'),
                'label' => 'Registro de Auditoría',
                'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M17.636 17.636l-2.828-2.828a7.5 7.5 0 10-1.414 1.414l2.828 2.828A1 1 0 0017.636 17.636z' // Icono de historial/log
            ];
        }
        // Puedes añadir más aquí, por ejemplo, Importar/Exportar, Invitaciones, etc.

        // Nombre de la aplicación desde la configuración
        $appName = config('app.name', 'Distrimeca');

        return view('admin.dashboard', compact(
            'appName',
            'userCount',
            'supplierCount',
            'productCount',
            'activeRoles',
            'usersByRole',
            'latestActivities',
            'quickAccessLinks'
        ));
    }
}