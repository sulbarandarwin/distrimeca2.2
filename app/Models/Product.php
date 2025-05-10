<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Comentar también estas líneas 'use' para la prueba
// use Spatie\Activitylog\Traits\LogsActivity; 
// use Spatie\Activitylog\LogOptions;       

class Product extends Model
{
    // Quitar LogsActivity de la línea 'use'
    use HasFactory; // <--- SOLO dejamos HasFactory aquí

    /**
     * The attributes that are mass assignable.
     * (Confirmamos que coincide con tu DB)
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'supplier_id',
        'category_id',
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
         return $this->belongsTo(Category::class)->withDefault([
             'name' => 'Sin Categoría' 
         ]);
    }

    public function failures(): HasMany
    {
        return $this->hasMany(ProductFailure::class);
    }
    // --- FIN DE LA RELACIÓN ---

    /* // Comentar TODO el método, desde ANTES de 'public function' hasta DESPUÉS del '}' final
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Producto {$eventName}");
    }
    */ 
} // Fin de la clase Product