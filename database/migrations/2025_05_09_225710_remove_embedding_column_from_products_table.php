<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Este método se ejecuta cuando corres `php artisan migrate`.
     * Aquí es donde eliminaremos la columna.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Primero, verificamos si la columna 'embedding' realmente existe
            // para evitar errores si esta migración se corre más de una vez
            // o si la columna ya fue eliminada manualmente.
            if (Schema::hasColumn('products', 'embedding')) {
                $table->dropColumn('embedding');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Este método se ejecuta cuando corres `php artisan migrate:rollback`.
     * Aquí volvemos a añadir la columna por si necesitas revertir.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Volvemos a añadir la columna como podría haber estado
            // (JSON nullable es una suposición, si era otro tipo, ajústalo).
            // Verificamos si NO existe para evitar errores al revertir múltiples veces.
            if (!Schema::hasColumn('products', 'embedding')) {
                $table->json('embedding')->nullable();
            }
        });
    }
};