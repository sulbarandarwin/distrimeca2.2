<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity; // Asegúrate que estas líneas estén si usas ActivityLog
use Spatie\Activitylog\LogOptions;          // Asegúrate que estas líneas estén si usas ActivityLog

class Product extends Model
{
    use HasFactory, LogsActivity; // Añade LogsActivity si lo estás usando

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'supplier_id',
        'category_id',
        // 'id', // <-- ¡ASEGÚRATE DE QUE 'id' NO ESTÉ AQUÍ!
    ];

    /**
     * Get the supplier that owns the product.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Método para configurar el log de actividad (si lo usas)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Producto {$eventName}");
    }
}
