<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importar HasMany

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // <-- AÑADIR ESTA LÍNEA
    ];

    /**
     * Get the states for the country.
     */
    public function states(): HasMany // Definir la relación inversa
    {
        return $this->hasMany(State::class);
    }
}
