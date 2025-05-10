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
        Schema::create('product_failures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade'); // Si se elimina un producto, se eliminan sus registros de falla

            $table->foreignId('user_id')
                  ->nullable() // El usuario que registra puede ser opcional
                  ->constrained('users')
                  ->onDelete('set null'); // Si se elimina el usuario, user_id en la falla se pone NULL

            $table->text('description')->nullable(); // Descripción de la falla

            // Fecha y hora en que se registró la falla o en que ocurrió.
            // Default al momento actual si no se especifica.
            $table->timestamp('failure_date')->useCurrent();

            $table->timestamps(); // created_at y updated_at del registro de falla
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('product_failures');
    }
};