<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Puedes descomentar esto si quieres crear usuarios de prueba con factories
        // \App\Models\User::factory(10)->create();

        // Llamamos a los seeders específicos que hemos creado
        $this->call([
            RolesAndPermissionsSeeder::class, // Ejecuta el seeder de roles y permisos
            LocationSeeder::class,          // Ejecuta el seeder de países y estados
            // Aquí puedes añadir llamadas a otros seeders si los creas en el futuro
            // Por ejemplo: CategorySeeder::class, SupplierTypeSeeder::class (si los separas)
        ]);

        // Nota: El usuario admin por defecto ahora se crea dentro de RolesAndPermissionsSeeder.php
    }
}
