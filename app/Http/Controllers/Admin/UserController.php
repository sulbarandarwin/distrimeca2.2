<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash; // Para encriptar la contraseña
use Illuminate\Validation\Rules\Password; // Para reglas de validación de contraseña
use Illuminate\Validation\Rule; // Para reglas de validación avanzadas (como unique ignorando al usuario actual)

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener usuarios paginados (ej: 10 por página)
        $users = User::paginate(10);

        // Pasar los usuarios a la vista
        return view('admin.users.index', compact('users')); // Usamos compact() como atajo para ['users' => $users]
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener todos los roles disponibles para mostrarlos en el formulario
        $roles = Role::all();

        // Retornar la vista del formulario, pasando la lista de roles
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            // 1. Validación de los datos del formulario
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class], // Asegura email único en tabla users
            'password' => ['required', 'confirmed', Password::defaults()], // 'confirmed' verifica que coincida con password_confirmation
            'roles' => ['required', 'array'], // Asegura que se envíe al menos un rol
            'roles.*' => ['exists:roles,id'] // Asegura que cada ID de rol exista en la tabla 'roles'
        ]);

        // 2. Crear el usuario si la validación pasa
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // ¡Importante! Encriptar la contraseña
        ]);

        // ----- INICIO: CORRECCIÓN AÑADIDA -----
        // Asegurarse de que los IDs de los roles sean enteros
        $roleIds = $validatedData['roles'];
        $integerRoleIds = array_map('intval', $roleIds);
        // ----- FIN: CORRECCIÓN AÑADIDA -----

        // 3. Asignar los roles validados (ahora como enteros) al nuevo usuario
        // Usamos syncRoles que puede trabajar directamente con los IDs de los roles validados
        $user->syncRoles($integerRoleIds); // <--- Pasamos el array de enteros

        // 4. Redirigir a la lista de usuarios con un mensaje de éxito
        // El mensaje 'success' se guarda en la sesión y se puede mostrar en la vista index
        return redirect()->route('admin.users.index')->with('success', '¡Usuario creado exitosamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Normalmente no se usa para gestión de usuarios, se usa edit.
        // Puedes dejarlo vacío o redirigir a edit.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
            // Obtener todos los roles disponibles
            $roles = Role::all();

            // Retornar la vista del formulario de edición, pasando el usuario y los roles
            return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
                // 1. Validación de los datos del formulario
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id) // ¡Importante! Ignora el email actual del propio usuario al validar unicidad
                ],
                'password' => ['nullable', 'confirmed', Password::defaults()], // 'nullable' hace la contraseña opcional
                'roles' => ['required', 'array'],
                'roles.*' => ['exists:roles,id']
            ]);

            // 2. Actualizar datos básicos del usuario (nombre y email)
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ]);

            // 3. Actualizar contraseña SOLO SI se proporcionó una nueva
            // $request->filled() verifica que el campo no esté vacío
            if ($request->filled('password')) {
                // Validar de nuevo solo la contraseña (ya que era opcional antes)
                $request->validate([
                    'password' => ['required', 'confirmed', Password::defaults()],
                ]);
                // Asignar la nueva contraseña hasheada
                $user->password = Hash::make($request->password);
                $user->save(); // Guardar el cambio de contraseña
            }

            // 4. Sincronizar los roles (quita los viejos, añade los nuevos seleccionados)
            // Asegurarse de que los IDs de los roles sean enteros
            $roleIds = $validatedData['roles'];
            $integerRoleIds = array_map('intval', $roleIds);
            $user->syncRoles($integerRoleIds);

            // 5. Redirigir a la lista de usuarios con un mensaje de éxito
            return redirect()->route('admin.users.index')->with('success', '¡Usuario actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
                // Protección 1: No permitir que un usuario se borre a sí mismo
            if (auth()->user()->id === $user->id) {
                return back()->with('error', '¡No puedes eliminar tu propia cuenta!');
            }

            // Protección 2: No permitir borrar al último Admin (opcional pero recomendado)
            // Comprueba si el usuario a borrar tiene el rol 'Admin' Y si solo queda 1 admin en total
            if ($user->hasRole('Admin') && User::role('Admin')->count() === 1) {
                return back()->with('error', '¡No puedes eliminar al último administrador!');
            }

            // Si pasa las protecciones, borra el usuario
            // Spatie debería encargarse de borrar las relaciones en model_has_roles automáticamente
            $user->delete();

            // Redirigir a la lista con mensaje de éxito
            return redirect()->route('admin.users.index')->with('success', '¡Usuario eliminado exitosamente!');
    }
}