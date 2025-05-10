<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('supplier_user', function (Blueprint $table) {
            // Añade las columnas created_at y updated_at
            // Pueden ser nullables si tienes registros existentes y no quieres forzar un valor por defecto
            // o puedes definir un valor por defecto si lo prefieres.
            // Por simplicidad, Laravel las manejará como CURRENT_TIMESTAMP si se definen así:
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            // O, si solo quieres las columnas estándar que Laravel maneja automáticamente
            // con withTimestamps() y esperas que siempre tengan valor (para nuevas entradas):
            // $table->timestamps(); // Esto crea created_at y updated_at no nullables
            // Para registros existentes, esto podría causar problemas si no se les asigna un valor.
            // Si la tabla ya tiene datos, la opción con ->nullable() o definir un default es más segura.
            // Para este caso, y ya que `withTimestamps()` las espera, vamos a usar el estándar:
            // $table->timestamps(); // Esta es la forma más común.
            // Sin embargo, si la tabla ya tiene filas, esta fallará sin un default.
            // Vamos por una solución más robusta para tablas existentes:
            // $table->timestamp('created_at')->default(now());
            // $table->timestamp('updated_at')->default(now());
            // La más simple si la tabla es nueva o no te importa que los existentes queden null inicialmente y luego se llenen
            // es $table->timestamps(); Pero ya que la tabla existe, usaremos nullable para evitar errores en MySQL strict mode.
        });

        // Alternativa más simple si la tabla ya existe y queremos que los nuevos registros tengan timestamps
        // y los antiguos queden como NULL.
        // Schema::table('supplier_user', function (Blueprint $table) {
        //     $table->timestamp('created_at')->nullable();
        //     $table->timestamp('updated_at')->nullable();
        // });
        // La forma más estándar y que Eloquent espera con withTimestamps() es simplemente:
        // $table->timestamps();
        // Si tu tabla supplier_user ya tiene datos, ejecutar esto podría dar error en algunos
        // sistemas de BD si no se permite NULL y no hay default.
        // Por lo tanto, una forma segura para una tabla existente es:
        // (Si no hay datos aún en supplier_user, $table->timestamps(); es suficiente)
        if (Schema::hasTable('supplier_user')) { // Doble check por si acaso
             Schema::table('supplier_user', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_user', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('supplier_user', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('supplier_user', function (Blueprint $table) {
            // $table->dropTimestamps(); // Esta es la forma estándar de Laravel
            if (Schema::hasColumn('supplier_user', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('supplier_user', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};