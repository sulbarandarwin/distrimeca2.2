<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFailure extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * (Opcional si el nombre de la tabla es el plural del nombre del modelo en snake_case)
     *
     * @var string
     */
    // protected $table = 'product_failures'; // Laravel lo infiere automáticamente

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'description',
        'failure_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'failure_date' => 'datetime', // Asegura que failure_date sea un objeto Carbon
    ];

    /**
     * Get the product that has this failure.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who reported/registered this failure.
     */
    public function user(): BelongsTo
    {
        // Asegúrate que el modelo User exista en App\Models\User
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Sistema/Desconocido' // Nombre por defecto si user_id es null
        ]);
    }
}