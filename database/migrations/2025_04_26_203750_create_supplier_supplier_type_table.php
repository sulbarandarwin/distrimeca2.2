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
        // Tabla pivote para relación muchos-a-muchos entre suppliers y supplier_types
        Schema::create('supplier_supplier_type', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                  ->constrained('suppliers')      // Referencia id en tabla suppliers
                  ->cascadeOnDelete();           // Si se borra un proveedor, se borra la relación

            $table->foreignId('supplier_type_id')
                  ->constrained('supplier_types') // Referencia id en tabla supplier_types
                  ->cascadeOnDelete();           // Si se borra un tipo, se borra la relación

            // Clave primaria compuesta para evitar duplicados
            $table->primary(['supplier_id', 'supplier_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_supplier_type');
    }
};