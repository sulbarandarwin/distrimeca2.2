<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// El nombre de la clase debe coincidir con el nombre del archivo (CamelCase)
return new class extends Migration 
{
    /**
     * Run the migrations.
     * Este método se ejecuta cuando corres `php artisan migrate`.
     */
    public function up(): void
    {
        // Usamos Schema::table para modificar una tabla existente ('users')
        Schema::table('users', function (Blueprint $table) {
            // Definimos la nueva columna 'supplier_id'
            $table->foreignId('supplier_id') // Crea una columna unsignedBigInteger llamada supplier_id
                  ->nullable()               // Permite que la columna sea NULL (para usuarios no proveedores)
                  ->after('email_verified_at') // Posición opcional (después de qué columna añadirla)
                  ->constrained('suppliers')   // Establece la clave foránea que referencia a la columna 'id' de la tabla 'suppliers'
                  ->onDelete('set null');    // Acción al borrar un proveedor: poner NULL el supplier_id de los usuarios asociados
                  // Alternativas para onDelete:
                  // ->onDelete('cascade'); // Borraría los usuarios si se borra su proveedor (¡Cuidado!)
                  // ->onDelete('restrict'); // Impediría borrar un proveedor si tiene usuarios asociados
        });
    }

    /**
     * Reverse the migrations.
     * Este método se ejecuta cuando corres `php artisan migrate:rollback`.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
             // Para revertir, primero eliminamos la restricción de clave foránea
             // Laravel usa la convención 'tabla_columna_foreign' para el nombre del índice
             $table->dropForeign(['supplier_id']); 
             
             // Luego, eliminamos la columna
             $table->dropColumn('supplier_id');
        });
    }
};
