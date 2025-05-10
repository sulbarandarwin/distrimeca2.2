<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Definición de Permisos ---
        $permissions = [
            // Usuarios
            'ver usuarios',
            'gestionar usuarios',
            // Proveedores
            'ver proveedores',
            'gestionar proveedores',
            // Productos
            'ver productos',
            'crear productos',
            'editar productos propios',
            'editar todos los productos',
            'eliminar productos',
            // Invitaciones
            'enviar invitaciones',
            // Import/Export
            'importar proveedores',
            'importar productos',
            'exportar suppliers',
            'exportar products',
            // Auditoría
            'ver auditoria',
            'limpiar auditoria',
            // Configuración
            'gestionar configuracion',
            // Roles y Permisos (Meta)
            'gestionar roles',

            // --- NUEVOS PERMISOS PARA FALLAS DE PRODUCTOS ---
            'view product_failures',        // Ver listado de fallas
            'manage product_failures',      // CRUD completo para fallas (crear, editar, eliminar)
            'export product_failures',      // Exportar registros de fallas
            // Si prefieres mayor granularidad para fallas, puedes descomentar y usar estos:
            // 'create product_failures',
            // 'edit product_failures',
            // 'delete product_failures',
            // --- FIN NUEVOS PERMISOS ---
        ];

        // Crear o encontrar cada permiso
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }
        $this->command->info('Permisos (incluyendo para la sección de Fallas) creados/verificados.');

        // --- Creación de Roles ---
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $roleVendedor = Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        $roleProveedor = Role::firstOrCreate(['name' => 'Proveedor', 'guard_name' => 'web']);
        $roleCliente = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        $this->command->info('Roles creados/verificados.');

        // --- Asignación de Permisos a Roles ---

        // Admin: Asignar todos los permisos definidos
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $roleAdmin->syncPermissions($allPermissions);
        $this->command->info('Todos los permisos asignados al rol Admin.');

        // Vendedor: Ajusta según necesidad
        $vendedorPermissions = [
            'ver proveedores',
            'ver productos',
            'crear productos',
            'enviar invitaciones',
            // Si el Vendedor debe ver o gestionar fallas, añade aquí:
            // 'view product_failures',
            // 'manage product_failures',
        ];
        $roleVendedor->syncPermissions($vendedorPermissions);
        $this->command->info('Permisos asignados al rol Vendedor.');

        // Proveedor: Ajusta según necesidad
        $proveedorPermissions = [
            'ver productos',
            'editar productos propios',
            'importar productos',
            // Si el Proveedor debe ver fallas (quizás solo de sus propios productos,
            // lo cual se controlaría en el Policy de ProductFailure):
            // 'view product_failures',
        ];
        $roleProveedor->syncPermissions($proveedorPermissions);
        $this->command->info('Permisos asignados al rol Proveedor.');

        // Cliente: Ajusta según necesidad
        $clientePermissions = [
            'ver productos',
        ];
        $roleCliente->syncPermissions($clientePermissions);
        $this->command->info('Permisos asignados al rol Cliente.');

        // --- Crear Usuario Admin por Defecto ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@distrimeca.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'), // ¡CAMBIA ESTO EN PRODUCCIÓN!
                'email_verified_at' => now(), // Opcional: Marcar como verificado
            ]
        );
        // Asegurar que el usuario admin siempre tenga el rol Admin
        if (!$adminUser->hasRole('Admin')) {
            $adminUser->assignRole($roleAdmin);
        }
        $this->command->info('Usuario Administrador por defecto creado/verificado y rol asignado.');

        // Es buena práctica resetear la caché de permisos de Spatie al final del seeder.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Caché de permisos de Spatie reiniciada.');
    }
}