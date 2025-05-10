<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cambia el índice único de 'code' a un índice compuesto en 'supplier_id' y 'code'.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 1. Intentar eliminar el índice único existente en 'code'
            // El nombre por defecto suele ser 'products_code_unique'. 
            // Si le diste otro nombre, ajústalo aquí.
            try {
                 $table->dropUnique('products_code_unique');
                 // O si usaste ->unique() directamente en la columna:
                 // $table->dropUnique(['code']); 
             } catch (\Exception $e) {
                 // Loguear si no se encuentra, podría no existir explícitamente
                 \Illuminate\Support\Facades\Log::warning('Índice único en "code" no encontrado o ya eliminado: ' . $e->getMessage());
             }

            // 2. Añadir el nuevo índice único compuesto
            $table->unique(['supplier_id', 'code'], 'products_supplier_code_unique'); // Nombre explícito para el índice
        });
    }

    /**
     * Reverse the migrations.
     * Vuelve a poner el índice único solo en 'code' (si existía)
     * y elimina el índice compuesto.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 1. Eliminar el índice compuesto
             $table->dropUnique('products_supplier_code_unique');

             // 2. Volver a añadir el índice único solo en 'code' (si lo tenías antes)
             // Si la columna 'code' permite NULLs, no puedes poner unique directamente aquí
             // a menos que tu versión de DB lo soporte o lo manejes de otra forma.
             // Considera si realmente necesitas revertir esto o si el índice compuesto es suficiente.
             // $table->unique('code', 'products_code_unique'); 
        });
    }
};
