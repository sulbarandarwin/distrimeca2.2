<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea la tabla pivote para la relación muchos-a-muchos entre suppliers y users.
     */
    public function up(): void
    {
        Schema::create('supplier_user', function (Blueprint $table) {
            // Clave foránea para la tabla suppliers
            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->onDelete('cascade'); // Si se borra un proveedor, se borran sus relaciones con usuarios

            // Clave foránea para la tabla users
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // Si se borra un usuario, se borran sus relaciones con proveedores

            // Definir la clave primaria compuesta para evitar duplicados
            $table->primary(['supplier_id', 'user_id']);

            // No necesitamos timestamps (created_at, updated_at) en esta tabla pivote generalmente
        });
    }

    /**
     * Reverse the migrations.
     * Elimina la tabla pivote.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_user');
    }
};
