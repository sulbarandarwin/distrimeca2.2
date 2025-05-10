<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() // <--- MÉTODO INDEX
    {
        // Eager load roles and suppliers to prevent N+1 queries in the view
        $users = User::with(['roles', 'suppliers'])->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Admin')->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        return view('admin.users.create', compact('roles', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('UserController@store: Inicio del método. Datos recibidos del formulario:', $request->all());

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'suppliers' => ['nullable', 'array'],
            'suppliers.*' => ['integer', 'exists:suppliers,id'],
        ]);

        Log::info('UserController@store: Datos validados:', $validatedData);

        $userData = Arr::except($validatedData, ['roles', 'suppliers', 'password', 'password_confirmation']);
        $userData['password'] = Hash::make($validatedData['password']);

        try {
            $user = User::create($userData);
            Log::info("UserController@store: Usuario creado con ID: {$user->id}, Nombre: {$user->name}");
        } catch (\Exception $e) {
            Log::error("UserController@store: Error al crear el usuario en la BD: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.users.create')->with('error', 'Error al crear el usuario. Revise los logs.')->withInput();
        }

        if (!empty($validatedData['roles'])) {
            Log::info("UserController@store: Intentando sincronizar roles para el usuario ID {$user->id} con IDs:", $validatedData['roles']);
            try {
                // Convertir los IDs de rol a enteros (por si acaso)
                $roleIds = array_map('intval', $validatedData['roles']);
                $rolesToSync = Role::whereIn('id', $roleIds)->get();

                if ($rolesToSync->count() !== count($roleIds)) {
                     Log::warning("UserController@store: No todos los IDs de roles validados fueron encontrados como objetos Role. IDs validados:", $roleIds, "Roles encontrados:", $rolesToSync->pluck('id')->toArray());
                }

                if ($rolesToSync->isNotEmpty()) {
                    $user->syncRoles($rolesToSync);
                    $user->refresh()->load('roles');
                    Log::info("UserController@store: Roles sincronizados usando objetos Role. Roles actuales del usuario: ", $user->getRoleNames()->toArray());
                } else {
                     Log::warning("UserController@store: No se encontraron objetos Role válidos para sincronizar con el usuario ID {$user->id}.");
                }
            } catch (\Exception $e) {
                Log::error("UserController@store: Error al sincronizar roles para el usuario ID {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            }
        } else {
            Log::warning("UserController@store: No se proporcionaron roles para sincronizar para el usuario ID {$user->id}.");
        }

        if (isset($validatedData['roles']) && !empty($validatedData['roles'])) {
            $isProveedor = $user->hasRole('Proveedor');
            if ($isProveedor && !empty($validatedData['suppliers'])) {
                 $user->suppliers()->sync($validatedData['suppliers']);
                 Log::info("UserController@store: Proveedores sincronizados para Usuario ID {$user->id} (Rol Proveedor detectado).", ['supplier_ids' => $validatedData['suppliers']]);
            } else {
                 $user->suppliers()->detach();
                 if($isProveedor){ Log::info("UserController@store: Rol Proveedor detectado para Usuario ID {$user->id} pero no se enviaron suppliers. Proveedores desasociados."); }
                 else { Log::info("UserController@store: Proveedores desasociados para Usuario ID {$user->id} porque el rol Proveedor no está entre los asignados."); }
            }
        } else {
            Log::info("UserController@store: No hay roles definidos para el usuario ID {$user->id}, desasociando proveedores.");
            $user->suppliers()->detach();
        }
        Log::info("UserController@store: Fin del método store para usuario ID {$user->id}.");
        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load('roles', 'suppliers');
        $roles = Role::where('name', '!=', 'Admin')->orderBy('name')->get();
        $currentUserRoleId = $user->roles->first()?->id;
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $userSupplierIds = $user->suppliers->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'currentUserRoleId', 'suppliers', 'userSupplierIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Log::info("UserController@update: Actualizando usuario ID {$user->id}. Datos recibidos:", $request->all());
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'suppliers' => ['nullable', 'array'],
            'suppliers.*' => ['integer', 'exists:suppliers,id'],
        ]);
        Log::info("UserController@update: Datos validados para usuario ID {$user->id}:", $validatedData);

        $userData = Arr::except($validatedData, ['role_id', 'suppliers', 'password', 'password_confirmation', '_token', '_method']);
        if (!empty($validatedData['password'])) {
            $userData['password'] = Hash::make($validatedData['password']);
        }
        $user->update($userData);
        Log::info("UserController@update: Datos básicos del usuario ID {$user->id} actualizados.");

        $selectedRole = Role::findById($validatedData['role_id']);
        if ($selectedRole) {
            $user->syncRoles([$selectedRole->id]);
            $user->load('roles');
            Log::info("UserController@update: Rol sincronizado para Usuario ID {$user->id}. Rol actual: " . $selectedRole->name);
        } else {
            Log::error("UserController@update: Rol ID {$validatedData['role_id']} validado pero no encontrado para usuario ID {$user->id}.");
        }

        $supplierIdsToSync = $validatedData['suppliers'] ?? [];
        if ($selectedRole && $selectedRole->name === 'Proveedor') {
            $user->suppliers()->sync($supplierIdsToSync);
            Log::info("UserController@update: Proveedores sincronizados para Usuario ID {$user->id} (Rol Proveedor). IDs:", $supplierIdsToSync);
        } else {
            $user->suppliers()->detach();
            Log::info("UserController@update: Proveedores desasociados para Usuario ID {$user->id} porque su rol no es Proveedor o fue cambiado.");
        }
        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === 1 || (Auth::check() && $user->id === Auth::id())) {
            Log::warning("Intento de eliminar usuario protegido ID {$user->id} por el usuario ID " . (Auth::id() ?? 'N/A'));
            return back()->with('error', 'No puedes eliminar este usuario.');
        }
        try {
            Log::info("UserController@destroy: Intentando eliminar usuario ID {$user->id} por usuario ID " . (Auth::id() ?? 'N/A'));
            $user->syncRoles([]);
            $user->suppliers()->detach();
            $user->delete();
            Log::info("Usuario ID {$user->id} eliminado exitosamente.");
            return redirect()->route('admin.users.index')->with('success', '¡Usuario eliminado exitosamente!');
        } catch (\Exception $e) {
            Log::error("Error al eliminar usuario ID {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocurrió un error inesperado al eliminar el usuario.');
        }
    }
}