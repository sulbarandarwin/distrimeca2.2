<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Para comprobaciones de autorización más finas si se necesitan
use Illuminate\Validation\Rule; // Para reglas de validación más complejas
use Spatie\Permission\Models\Role; // Para buscar el ID del rol 'Proveedor'

class StoreInvitationRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     * La autorización básica por rol ya se aplica en la ruta (web.php).
     * Aquí podríamos añadir lógica más específica si fuera necesario.
     * Por ejemplo: ¿Puede un 'Vendedor' invitar a cualquier rol o solo a 'Cliente'/'Proveedor'?
     */
    public function authorize(): bool
    {
        // Por ahora, si pasó el middleware de la ruta, permitimos la validación.
        return true; 
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Buscamos el ID del rol 'Proveedor' para usarlo en la validación condicional.
        // Es importante que el nombre 'Proveedor' coincida exactamente con como está en tu tabla 'roles'.
        $proveedorRole = Role::where('name', 'Proveedor')->first();
        $proveedorRoleId = $proveedorRole ? $proveedorRole->id : null; // Obtenemos el ID si el rol existe

        return [
            'email' => [
                'required', // El email es obligatorio
                'email',    // Debe tener formato de email válido
                // Debe ser único en la tabla 'invitations' DONDE 'registered_at' sea NULL
                // Esto evita enviar múltiples invitaciones PENDIENTES al mismo email.
                Rule::unique('invitations')->where(function ($query) {
                    return $query->whereNull('registered_at');
                }),
                // Debe ser único también en la tabla 'users'
                // Esto evita invitar a alguien que ya tiene una cuenta activa.
                'unique:users,email', 
            ],
            'role_id' => [
                'required',        // El rol es obligatorio
                'exists:roles,id', // El ID del rol debe existir en la tabla 'roles'
            ],
            'associated_supplier_id' => [
                'nullable', // Puede ser nulo (no siempre se requiere)
                // Es obligatorio SI Y SOLO SI el 'role_id' enviado coincide con el ID del rol 'Proveedor'
                Rule::requiredIf(function () use ($proveedorRoleId) {
                    // Dentro de esta función, $this->input() accede a los datos de la petición actual.
                    return $this->input('role_id') == $proveedorRoleId;
                }),
                // Si se proporciona un ID de proveedor, debe existir en la tabla 'suppliers'
                'exists:suppliers,id', 
            ],
        ];
    }

    /**
     * Obtiene mensajes personalizados para los errores de validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Ya existe una cuenta de usuario o una invitación pendiente para este correo electrónico.',
            'role_id.required' => 'Debe seleccionar un rol para el invitado.',
            'associated_supplier_id.required_if' => 'Debe seleccionar un proveedor cuando el rol asignado es "Proveedor".',
            'associated_supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            // Puedes añadir más mensajes personalizados aquí si lo deseas
        ];
    }
}