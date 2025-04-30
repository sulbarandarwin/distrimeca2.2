<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Importar el modelo User
use Illuminate\Support\Facades\Hash; // <-- ¡¡LÍNEA AÑADIDA!!

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Crear Permisos ---
        Permission::firstOrCreate(['name' => 'access admin']);
        Permission::firstOrCreate(['name' => 'view users']);
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'view suppliers']);
        Permission::firstOrCreate(['name' => 'manage suppliers']);
        Permission::firstOrCreate(['name' => 'view products']);
        Permission::firstOrCreate(['name' => 'create products']);
        Permission::firstOrCreate(['name' => 'edit products']);
        Permission::firstOrCreate(['name' => 'delete products']);
        Permission::firstOrCreate(['name' => 'edit own products']);
        Permission::firstOrCreate(['name' => 'view audit log']);
        Permission::firstOrCreate(['name' => 'manage settings']);
        Permission::firstOrCreate(['name' => 'invite users']);


        // --- Crear Roles ---
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleVendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $roleProveedor = Role::firstOrCreate(['name' => 'Proveedor']);
        $roleCliente = Role::firstOrCreate(['name' => 'Cliente']);


        // --- Asignar Permisos a Roles ---
        // Admin: Implícitamente tiene todos
        // $roleAdmin->givePermissionTo(Permission::all()); // Opcional ser explícito

        // Vendedor
        $roleVendedor->syncPermissions([
            'access admin', 'view users', 'view suppliers', 'view products',
            'create products', 'view audit log', 'invite users',
        ]);

        // Proveedor
        $roleProveedor->syncPermissions(['edit own products']);

        // Cliente
        $roleCliente->syncPermissions(['view products']);


        $this->command->info('Roles y Permisos específicos creados/actualizados.');

        // Crear usuario Admin por defecto
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@distrimeca.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password') // Ahora sí encuentra Hash
            ]
        );
        // Asignar el rol Admin
        $adminUser->assignRole($roleAdmin);
        $this->command->info('Usuario Administrador por defecto creado/verificado.');
    }
}
