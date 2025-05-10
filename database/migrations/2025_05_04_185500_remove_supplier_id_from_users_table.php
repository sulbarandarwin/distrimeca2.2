<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina la columna supplier_id y su clave foránea.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
             // Primero intentar eliminar la clave foránea (el nombre puede variar si no seguiste la convención)
             // Laravel < 9 usaba 'users_supplier_id_foreign', Laravel 9+ puede usar un nombre más largo
             // Intenta con la convención, si falla, busca el nombre exacto en tu DB.
             try {
                $table->dropForeign(['supplier_id']);
             } catch (\Exception $e) {
                 // Loguear si no se encuentra la FK, pero continuar para borrar columna
                 \Illuminate\Support\Facades\Log::warning('No se pudo eliminar la clave foránea supplier_id en users: ' . $e->getMessage());
             }
             
             // Luego eliminar la columna
             $table->dropColumn('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     * Vuelve a añadir la columna (como era antes).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                  ->nullable()
                  ->after('email_verified_at') // O donde estuviera
                  ->constrained('suppliers')
                  ->onDelete('set null');
        });
    }
};
