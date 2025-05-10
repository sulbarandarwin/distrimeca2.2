<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver cualquier producto (la lista).
     * Este método es llamado por $this->authorize('viewAny', Product::class);
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver productos'); // El permiso base es suficiente para la lista general
    }

    /**
     * Determina si el usuario puede ver un producto específico.
     * Este método es llamado por $this->authorize('view', $product);
     */
    public function view(User $user, Product $product): bool
    {
        // Primero, verificar el permiso base 'ver productos'
        if (!$user->can('ver productos')) {
            return false;
        }

        // Admin y Vendedor pueden ver cualquier producto si tienen el permiso base
        if ($user->hasRole('Admin') || $user->hasRole('Vendedor')) {
            return true;
        }

        // Proveedor solo puede ver los productos de los proveedores a los que está asociado
        if ($user->hasRole('Proveedor')) {
            return $user->suppliers()->where('suppliers.id', $product->supplier_id)->exists();
        }

        // Cliente puede ver cualquier producto si tiene el permiso base
        // (ya que la información sensible del proveedor se oculta en la vista/controlador)
        if ($user->hasRole('Cliente')) {
            return true;
        }

        return false; // Por defecto, si ninguna condición anterior se cumple pero tiene el permiso.
                      // O ajustar según necesidad para otros roles.
    }

    /**
     * Determina si el usuario puede crear productos.
     */
    public function create(User $user): bool
    {
        return $user->can('crear productos');
    }

    /**
     * Determina si el usuario puede actualizar el producto.
     */
    public function update(User $user, Product $product): bool
    {
        // Admin con permiso 'editar todos los productos' puede editar cualquiera
        if ($user->can('editar todos los productos') && $user->hasRole('Admin')) {
            return true;
        }

        // Vendedor con permiso 'editar todos los productos' (si así se configura el permiso)
        if ($user->can('editar todos los productos') && $user->hasRole('Vendedor')) {
             return true; // O ajustar si el vendedor solo edita algunos específicos
        }


        // Proveedor con permiso 'editar productos propios' puede editar los de sus proveedores asociados
        if ($user->can('editar productos propios') && $user->hasRole('Proveedor')) {
            return $user->suppliers()->where('suppliers.id', $product->supplier_id)->exists();
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar el producto.
     */
    public function delete(User $user, Product $product): bool
    {
        // Admin con permiso 'eliminar productos'
        if ($user->can('eliminar productos') && $user->hasRole('Admin')) {
            return true;
        }

        // Si quieres que un Proveedor pueda eliminar sus propios productos (y tiene el permiso general 'eliminar productos')
        // if ($user->can('eliminar productos') && $user->hasRole('Proveedor')) {
        //     return $user->suppliers()->where('suppliers.id', $product->supplier_id)->exists();
        // }
        // Actualmente tu seed de permisos probablemente solo da 'eliminar productos' al Admin.

        return false;
    }

    // public function restore(User $user, Product $product): bool { ... }
    // public function forceDelete(User $user, Product $product): bool { ... }
}