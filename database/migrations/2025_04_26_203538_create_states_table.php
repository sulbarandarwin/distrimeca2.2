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
        Schema::create('states', function (Blueprint $table) {
            $table->id(); // Llave primaria 'id'
            $table->string('name'); // Nombre del estado/provincia
            $table->foreignId('country_id')
                  ->constrained('countries') // Referencia id en tabla countries
                  ->restrictOnDelete();       // Evita borrar país si tiene estados asociados
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};