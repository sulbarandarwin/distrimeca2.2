<?php

namespace App\Http\Controllers;

// --- Laravel/PHP Imports ---
use Illuminate\Http\Request; // Necesario para accept()
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL; // Para generar URLs firmadas
use Illuminate\Support\Str; // Para generar el token
use Illuminate\Auth\Events\Registered; // Evento estándar de registro

// --- App Specific Imports ---
use App\Http\Requests\StoreInvitationRequest; // Validación para crear invitación
use App\Http\Requests\ProcessInvitationRequest; // Validación para aceptar invitación
use App\Models\Invitation;
use App\Models\Supplier;
use App\Models\User; // Modelo de Usuario
use App\Mail\SendInvitationMail; // Clase Mailable
use Spatie\Permission\Models\Role; // Modelo Role de Spatie

class InvitationController extends Controller
{
    /**
     * Muestra el formulario para crear una nueva invitación.
     * Accesible por Admin y Vendedor.
     */
    public function create()
    {
        // Obtener roles que se pueden invitar (ej. todos excepto Admin)
        $roles = Role::where('name', '!=', 'Admin')->orderBy('name')->get();

        // Obtener proveedores para el dropdown (se usa condicionalmente)
        // Asegúrate de que el modelo Supplier exista y tenga un campo 'name'
        $suppliers = Supplier::orderBy('name')->get();

        // Lógica opcional para restringir roles según quién invita
        // if (Auth::user()->hasRole('Vendedor')) {
        //     $roles = Role::whereIn('name', ['Proveedor', 'Cliente'])->orderBy('name')->get();
        // }

        return view('invitations.create', compact('roles', 'suppliers'));
    }

    /**
     * Guarda una nueva invitación en la base de datos y envía el correo de invitación.
     * Accesible por Admin y Vendedor.
     *
     * @param StoreInvitationRequest $request El objeto Request validado automáticamente.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreInvitationRequest $request)
    {
        // La validación se maneja automáticamente por StoreInvitationRequest

        try {
            // 1. Generar token único
            $token = Str::random(40);

            // 2. Calcular fecha de expiración
            $expires_at = now()->addDays((int) config('invitations.expire_days', 2));

            // 3. Preparar datos
            $invitationData = [
                'email'                 => $request->validated('email'),
                'role_id'               => $request->validated('role_id'),
                'associated_supplier_id'=> $request->validated('associated_supplier_id'),
                'invited_by_user_id'    => Auth::id(),
                'token'                 => $token,
                'expires_at'            => $expires_at,
            ];

            // 4. Crear invitación en BD
            $invitation = Invitation::create($invitationData);

            // 5. Enviar email (o registrar en log si MAIL_MAILER=log)
            Mail::to($invitation->email)->send(new SendInvitationMail($invitation));

            // 6. Redirigir con éxito
            return redirect()->route('invitations.create')
                             ->with('success', '¡Invitación enviada correctamente a ' . $invitation->email . '!');

        } catch (\Exception $e) {
            // Registrar error detallado
            Log::error('Error al crear/enviar invitación: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token']),
                'exception' => $e
            ]);
            // Redirigir de vuelta con error genérico
             return redirect()->back()
                             ->withInput()
                             ->with('error', 'Hubo un problema al crear o enviar la invitación. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Muestra el formulario para que un usuario invitado acepte la invitación y complete el registro.
     * Ruta pública (protegida por firma).
     *
     * @param \Illuminate\Http\Request $request
     * @param string $token El token de invitación de la URL.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function accept(Request $request, string $token)
    {
        // El middleware 'signed' en web.php ya valida la firma y expiración básica de la URL.
        // Si quisiéramos ser extra cuidadosos:
        // if (! $request->hasValidSignature()) {
        //     return redirect()->route('login')->with('error', 'El enlace de invitación no es válido o ha expirado (firma inválida).');
        // }

        // 1. Buscar la invitación por el token
        $invitation = Invitation::where('token', $token)->first();

        // 2. Validar la invitación
        if (!$invitation) {
            Log::warning('Intento de aceptar invitación con token no encontrado: ' . $token);
            return redirect()->route('login')->with('error', 'El token de invitación proporcionado no es válido.');
        }

        if ($invitation->registered_at !== null) {
            Log::warning('Intento de aceptar invitación ya utilizada. Token: ' . $token . ' Email: ' . $invitation->email);
            return redirect()->route('login')->with('error', 'Esta invitación ya ha sido utilizada para registrar una cuenta.');
        }

        // Comprobación adicional de expiración (aunque 'signed' debería cubrirlo)
        if ($invitation->expires_at->isPast()) {
            Log::warning('Intento de aceptar invitación expirada. Token: ' . $token . ' Email: ' . $invitation->email);
             return redirect()->route('login')->with('error', 'Lo sentimos, esta invitación ha expirado.');
        }

        // 3. Mostrar la vista del formulario de aceptación final
        return view('invitations.accept', [
            'token' => $invitation->token,
            'email' => $invitation->email,
        ]);
    }

    /**
     * Procesa el formulario de aceptación de invitación, crea el usuario y lo autentica.
     * Ruta pública (protegida por middleware 'guest').
     *
     * @param \App\Http\Requests\ProcessInvitationRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processRegistration(ProcessInvitationRequest $request)
    {
        // La validación de los campos (token existe y no usado, email, nombre, password) ya se hizo.
        
        // 1. Encontrar la invitación válida usando el token validado
        $invitation = Invitation::where('token', $request->validated('token'))->firstOrFail();

         // 2. Verificación de seguridad adicional (Email)
         if ($invitation->email !== $request->validated('email')) {
             Log::error('Discrepancia de email al aceptar invitación.', [
                 'invitation_id' => $invitation->id, 
                 'invitation_email' => $invitation->email,
                 'request_email' => $request->validated('email')
             ]);
             return redirect()->route('login')->with('error', 'Ha ocurrido un error inesperado.');
        }
        // Podríamos verificar expiración de nuevo si quisiéramos ser extra paranoicos

        // 3. Crear el nuevo usuario
        try {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $invitation->email, // Usar el email seguro de la invitación
                'password' => Hash::make($request->validated('password')),
                'email_verified_at' => now(), // El email se considera verificado
                'supplier_id' => $invitation->associated_supplier_id,
            ]);

            // 4. Asignar el rol
            $invitation->load('role'); // Cargar relación explícitamente
            if ($invitation->role) {
                $user->assignRole($invitation->role->name);
            } else {
                 Log::critical('Rol no encontrado al aceptar invitación.', ['invitation_id' => $invitation->id, 'role_id' => $invitation->role_id]);
                 throw new \Exception("No se pudo asignar el rol especificado.");
             }

            // 5. Marcar la invitación como usada
            $invitation->update(['registered_at' => now()]);

            // 6. Iniciar sesión
            Auth::login($user);

            // 7. Disparar evento Registered
            event(new Registered($user));

            // 8. Redirigir al dashboard
             return redirect()->intended(route('dashboard', absolute: false))
                              ->with('status', '¡Registro completado! Bienvenido/a a ' . config('app.name') . '.');

        } catch (\Exception $e) {
             // Registrar error crítico
             Log::error('Error crítico al procesar registro de invitación: ' . $e->getMessage(), [
                'invitation_id' => $invitation->id,
                'request_data' => $request->except(['_token', 'password', 'password_confirmation']),
                'exception' => $e
            ]);
             // Redirigir a login con error genérico
             return redirect()->route('login')
                              ->with('error', 'No se pudo completar tu registro debido a un error interno.');
        }
    }

} // Fin de la clase InvitationController