<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity; // <-- AÑADIR
use Spatie\Activitylog\LogOptions;          // <-- AÑADIR

class Supplier extends Model
{
    use HasFactory, LogsActivity; // <-- AÑADIR LogsActivity

    protected $fillable = [
        'name', 'rif', 'phone1', 'phone2', 'email', 'country_id', 'state_id',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(SupplierType::class, 'supplier_supplier_type', 'supplier_id', 'supplier_type_id');
    }

    // Método para configurar el log de actividad
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Loguea todos los campos fillable excepto timestamps
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Proveedor {$eventName}");
    }
}
