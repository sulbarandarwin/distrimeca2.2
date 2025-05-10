<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password; // Importar regla de contraseña

class ProcessInvitationRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     * Como es un registro público (aunque con token), retornamos true.
     * La validez del token se verifica en las reglas y/o controlador.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
                // El token DEBE existir en la tabla 'invitations'
                // Y la invitación NO debe haber sido usada (registered_at es NULL)
                 Rule::exists('invitations')->where(function ($query) {
                    return $query->whereNull('registered_at');
                }),
            ],
            // Validamos el email aunque sea readonly, por si acaso se manipula
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            // Usamos las reglas de contraseña por defecto de Laravel (longitud, etc.)
            // y 'confirmed' para asegurar que password y password_confirmation coincidan
            'password' => ['required', 'confirmed', Password::defaults()],
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
              'token.exists' => 'El token de invitación no es válido o ya ha sido utilizado.',
              'name.required' => 'El nombre completo es obligatorio.',
              'password.required' => 'La contraseña es obligatoria.',
              'password.confirmed' => 'La confirmación de contraseña no coincide.',
              // Mensajes para las reglas internas de Password::defaults() (opcional, Laravel suele tenerlos)
              // 'password.min' => 'La contraseña debe tener al menos :min caracteres.', 
          ];
     }
}