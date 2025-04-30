<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;       // <-- Corregir Namespace
use App\Models\State;         // <-- Corregir Namespace
use App\Models\SupplierType;  // <-- Corregir Namespace
use App\Models\Category;      // <-- Corregir Namespace

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Crear País ---
        $countryVenezuela = Country::firstOrCreate(['name' => 'Venezuela']);

        // --- Crear Estados para Venezuela ---
        if ($countryVenezuela) {
            State::firstOrCreate(['name' => 'Zulia', 'country_id' => $countryVenezuela->id]);
            State::firstOrCreate(['name' => 'Distrito Capital', 'country_id' => $countryVenezuela->id]);
            State::firstOrCreate(['name' => 'Miranda', 'country_id' => $countryVenezuela->id]);
            State::firstOrCreate(['name' => 'Carabobo', 'country_id' => $countryVenezuela->id]);
            State::firstOrCreate(['name' => 'Lara', 'country_id' => $countryVenezuela->id]);
            $this->command->info('Países y Estados iniciales creados/verificados.');
        } else {
             $this->command->warn('No se pudo encontrar o crear el país Venezuela.');
        }

        // --- Crear Tipos de Proveedor ---
        SupplierType::firstOrCreate(['name' => 'Mayorista']);
        SupplierType::firstOrCreate(['name' => 'Distribuidor']);
        SupplierType::firstOrCreate(['name' => 'Fabricante']);
        $this->command->info('Tipos de Proveedor iniciales creados/verificados.');


        // --- Crear Categorías de Productos ---
        Category::firstOrCreate(['name' => 'Herramientas Manuales']);
        Category::firstOrCreate(['name' => 'Tornillería']);
        Category::firstOrCreate(['name' => 'Plomería']);
        Category::firstOrCreate(['name' => 'Electricidad']);
        Category::firstOrCreate(['name' => 'Pinturas']);
        $this->command->info('Categorías de Productos iniciales creadas/verificadas.');
    }
}
