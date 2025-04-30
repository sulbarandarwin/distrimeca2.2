<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Importar BelongsToMany

class SupplierType extends Model
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
     * The suppliers that belong to the type.
     */
    public function suppliers(): BelongsToMany // Definir relación inversa
    {
        return $this->belongsToMany(Supplier::class, 'supplier_supplier_type', 'supplier_type_id', 'supplier_id');
    }
}
