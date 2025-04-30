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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Llave primaria 'id'
            $table->string('name'); // Nombre del producto (Requerido)
            $table->string('code')->nullable()->unique(); // Código/SKU (Opcional, pero único si se da)
            $table->text('description')->nullable(); // Descripción (Opcional)
            $table->decimal('price', 10, 2)->nullable(); // Precio (Ej: 12345678.99, opcional) - Ajusta precisión y escala si necesitas

            // Clave foránea para el Proveedor
            $table->foreignId('supplier_id')
                  ->constrained('suppliers') // Referencia id en tabla suppliers
                  ->cascadeOnDelete();       // Si se borra el proveedor, se borran sus productos

            // Clave foránea para la Categoría (opcional, si un producto puede no tener categoría)
            $table->foreignId('category_id')
                  ->nullable()               // Permite que un producto no tenga categoría
                  ->constrained('categories') // Referencia id en tabla categories
                  ->nullOnDelete();          // Si se borra la categoría, el category_id del producto se pone NULL

            $table->timestamps(); // created_at y updated_at

            // Índices
            // $table->index('supplier_id');
            // $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
