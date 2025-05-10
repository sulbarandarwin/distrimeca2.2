<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- Asegúrate que esté importado
use Spatie\Activitylog\Traits\LogsActivity; 
use Spatie\Activitylog\LogOptions;        
use App\Models\User; // <-- AÑADIR IMPORT DE USER

class Supplier extends Model
{
    use HasFactory, LogsActivity; 

    protected $fillable = [
        'name', 'rif', 'phone1', 'phone2', 'email', 'country_id', 'state_id',
    ];

    // Tus relaciones existentes
    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function state(): BelongsTo { return $this->belongsTo(State::class); }
    public function types(): BelongsToMany { return $this->belongsToMany(SupplierType::class, 'supplier_supplier_type', 'supplier_id', 'supplier_type_id'); }

    // --- NUEVA RELACIÓN: users (ManyToMany) ---
    /**
     * Los usuarios (probablemente con rol Proveedor) asociados a este proveedor.
     */
    public function users(): BelongsToMany
    {
        // Modelo relacionado, tabla pivote, clave foránea de este modelo, clave foránea del modelo relacionado
        return $this->belongsToMany(User::class, 'supplier_user', 'supplier_id', 'user_id');
    }
    // --- FIN NUEVA RELACIÓN ---

    // Método para configurar el log de actividad
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Proveedor {$eventName}");
    }
}
