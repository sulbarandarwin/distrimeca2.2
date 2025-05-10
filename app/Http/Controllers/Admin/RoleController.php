<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;       // Modelo Role de Spatie
use Spatie\Permission\Models\Permission; // Modelo Permission de Spatie
use Illuminate\Support\Facades\DB;       // Para agrupar permisos (opcional)
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Muestra la lista de roles (excepto Admin, opcionalmente).
     */
    public function index()
    {
        // El middleware 'permission:manage roles' ya protege esta ruta
        
        $roles = Role::where('name', '!=', 'Admin') // Excluir Admin por seguridad
                     ->orderBy('name')
                     ->paginate(15); // O usa tu setting de paginación

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Muestra el formulario para editar los permisos de un rol específico.
     */
    public function edit(Role $role)
    {
        // El middleware 'permission:manage roles' ya protege esta ruta

        // Doble chequeo para no editar Admin
        if ($role->name === 'Admin') {
             abort(403, 'No se permite editar los permisos del rol Administrador directamente.');
        }

        // Obtener todos los permisos disponibles, agrupados por prefijo para la vista
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            // Intentar agrupar por la primera palabra antes de un espacio o guion bajo
            return explode(' ', $permission->name)[0] ?? 
                   explode('_', $permission->name)[0] ?? 
                   'otros'; // Grupo por defecto si no hay espacio/guion
        });

        // Permisos actuales del rol
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Actualiza los permisos asignados a un rol específico.
     */
    public function updatePermissions(Request $request, Role $role)
    {
         // El middleware 'permission:manage roles' ya protege esta ruta

         // Doble chequeo para no editar Admin
         if ($role->name === 'Admin') {
             abort(403, 'No se permite editar los permisos del rol Administrador directamente.');
         }

        // Validar que 'permissions' sea un array (puede ser vacío) y que los valores existan
        $validated = $request->validate([
            'permissions' => 'nullable|array', 
            'permissions.*' => 'string|exists:permissions,name,guard_name,web', // Validar contra permisos 'web'
        ]);

        try {
            $permissionsToSync = $validated['permissions'] ?? []; 
            
            // Sincronizar permisos
            $role->syncPermissions($permissionsToSync);

            Log::info("Permisos actualizados para rol '{$role->name}' por usuario ID: " . auth()->id(), ['synced_permissions' => $permissionsToSync]);

            // Redirigir a la lista de roles, no a la edición
            return redirect()->route('admin.roles.index')->with('success', "Permisos para el rol '{$role->name}' actualizados correctamente.");

        } catch (\Exception $e) {
            Log::error("Error al actualizar permisos para rol '{$role->name}': " . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocurrió un error al actualizar los permisos.');
        }
    }
}
