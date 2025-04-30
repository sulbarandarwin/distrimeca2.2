<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity; // <-- AÑADIR
use Spatie\Activitylog\LogOptions;          // <-- AÑADIR

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity; // <-- AÑADIR LogsActivity

    protected $fillable = [
        'name',
        'email',
        'password',
        // 'supplier_id', // Si lo añadiste antes para el rol Proveedor
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Método para configurar el log de actividad
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']) // Loguea solo cambios en estos campos
            ->logOnlyDirty() // Loguea solo si los campos especificados realmente cambiaron
            ->dontSubmitEmptyLogs() // No guardar logs si no cambió nada de lo especificado
            ->setDescriptionForEvent(fn(string $eventName) => "Usuario {$eventName}"); // Descripción personalizada
    }
}
