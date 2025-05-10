<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Importa el modelo Role de Spatie (ajusta el namespace si es necesario)
use Spatie\Permission\Models\Role;
// Importa el modelo User si no se detecta automáticamente
use App\Models\User;
// Importa el modelo Supplier si no se detecta automáticamente
use App\Models\Supplier;


class Invitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'role_id',
        'invited_by_user_id',
        'associated_supplier_id',
        'expires_at',
        'registered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    /**
     * Get the role associated with the invitation.
     */
    public function role()
    {
        // Asegúrate de que 'Spatie\Permission\Models\Role' es el namespace correcto
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter()
    {
        // El segundo argumento especifica la foreign key en la tabla 'invitations'
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * Get the supplier associated with the invitation (if any).
     */
    public function associatedSupplier()
    {
        // El segundo argumento especifica la foreign key en la tabla 'invitations'
        return $this->belongsTo(Supplier::class, 'associated_supplier_id');
    }
}