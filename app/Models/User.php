<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Descomentar si implementas verificación de email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // 'supplier_id', // ELIMINADO o Comentado - la relación principal es vía tabla pivote
        'dark_mode_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dark_mode_enabled' => 'boolean',
        ];
    }

    /**
     * Los proveedores asociados a este usuario.
     * Un usuario (ej. un comercial que trabaja para varias empresas proveedoras)
     * puede estar asociado a múltiples entidades Supplier.
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_user', 'user_id', 'supplier_id')
                    ->withTimestamps(); // Opcional: si quieres trackear cuándo se hizo la asociación
    }

    // Método para configurar el log de actividad
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Usuario {$this->email} ({$this->id}) fue {$eventName}");
    }

    public function failures(): HasMany
    {
        return $this->hasMany(ProductFailure::class);
    }
    // --- FIN DE LA RELACIÓN ---
}