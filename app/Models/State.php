<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo

class State extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',         // <-- AÑADIR ESTA LÍNEA
        'country_id',   // <-- AÑADIR ESTA LÍNEA
    ];

    /**
     * Get the country that owns the state.
     */
    public function country(): BelongsTo // Definir la relación
    {
        return $this->belongsTo(Country::class);
    }
}
