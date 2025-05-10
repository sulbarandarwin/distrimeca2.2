<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade la columna para la preferencia del modo oscuro.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Añadimos una columna booleana que puede ser NULL
            // true = prefiere modo oscuro
            // false = prefiere modo claro
            // NULL = usar preferencia del sistema/navegador (comportamiento por defecto)
            $table->boolean('dark_mode_enabled')->nullable()->after('remember_token'); // O después de otra columna que prefieras
        });
    }

    /**
     * Reverse the migrations.
     * Elimina la columna si hacemos rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // No necesitamos dropForeign aquí porque no es una clave foránea
            $table->dropColumn('dark_mode_enabled');
        });
    }
};
