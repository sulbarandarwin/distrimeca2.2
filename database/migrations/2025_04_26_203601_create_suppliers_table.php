<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); // Llave primaria 'id'
            $table->string('name'); // Nombre proveedor (Requerido)
            $table->string('rif')->unique()->nullable(); // RIF (Único si se da, opcional)
            $table->string('phone1')->nullable(); // Teléfono 1 (Opcional)
            $table->string('phone2')->nullable(); // Teléfono 2 (Opcional)
            $table->string('email')->unique()->nullable(); // Email (Único si se da, opcional)

            // Claves foráneas Ubicación (Asumimos requeridas)
            $table->foreignId('country_id')
                  ->constrained('countries') // Referencia id en tabla countries
                  ->restrictOnDelete();       // Evita borrar país si tiene proveedores

            $table->foreignId('state_id')
                  ->constrained('states')    // Referencia id en tabla states
                  ->restrictOnDelete();      // Evita borrar estado si tiene proveedores

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};