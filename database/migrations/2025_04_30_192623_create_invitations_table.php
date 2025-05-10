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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id(); // Columna ID autoincremental
            $table->string('email')->index(); // Email del invitado, indexado para búsquedas rápidas
            $table->string('token')->unique(); // Token único para el enlace de invitación
            
            // Clave foránea para el rol asignado (tabla 'roles' de Spatie)
            // onDelete('cascade'): si se borra el rol, se borran las invitaciones asociadas
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade'); 
            
            // Clave foránea para el usuario que envió la invitación (tabla 'users')
            // onDelete('cascade'): si se borra el usuario invitador, se borran sus invitaciones enviadas
            $table->foreignId('invited_by_user_id')->constrained('users')->onDelete('cascade'); 
            
            // Clave foránea para el proveedor asociado (tabla 'suppliers')
            // nullable(): Puede ser nulo (no todas las invitaciones son para proveedores)
            // onDelete('set null'): si se borra el proveedor, el campo en la invitación se pone nulo
            $table->foreignId('associated_supplier_id')->nullable()->constrained('suppliers')->onDelete('set null'); 
            
            $table->timestamp('expires_at'); // Fecha/hora de expiración del token
            $table->timestamp('registered_at')->nullable(); // Fecha/hora en que se usó la invitación (se registró el usuario)
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};