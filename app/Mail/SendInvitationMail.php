<?php

namespace App\Mail;

use App\Models\Invitation; // Importamos el modelo Invitation
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Interfaz para correos en cola (opcional)
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL; // Para generar URLs firmadas si se necesitaran

class SendInvitationMail extends Mailable // implements ShouldQueue // Descomentar si quieres que los emails se envíen en cola
{
    use Queueable, SerializesModels;

    /**
     * La instancia de la invitación.
     * Usamos promoción de propiedades del constructor de PHP 8 para simplicidad.
     * Esto hace que $invitation sea una propiedad pública y esté disponible automáticamente en la vista.
     */
    public function __construct(
        public Invitation $invitation
    ) {
        // El objeto $invitation se asigna automáticamente a $this->invitation
    }

    /**
     * Obtiene la "envoltura" del mensaje.
     * Define el Asunto, De (From), Responder A (ReplyTo), etc.
     */
    public function envelope(): Envelope
    {
        // El remitente (From) se tomará por defecto de la configuración en config/mail.php y .env
        return new Envelope(
            subject: '¡Has sido invitado a unirte a ' . config('app.name') . '!', // Asunto del correo
        );
    }

    /**
     * Obtiene la definición del contenido del mensaje.
     * Especifica qué vista de Markdown se usará.
     * La propiedad pública $invitation estará disponible automáticamente en esta vista.
     */
    public function content(): Content
    {
        // Aquí generamos la URL que el usuario usará para aceptar la invitación.
        // Es crucial que la ruta 'invitation.accept' exista (la definiremos más adelante).
        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept', // Nombre de la ruta para aceptar (¡la crearemos pronto!)
            $this->invitation->expires_at, // La URL firmada expira cuando expira la invitación
            ['token' => $this->invitation->token] // Parámetro que necesita la ruta
        );

        return new Content(
            markdown: 'emails.invitations.send', // La vista que contiene el cuerpo del email
            with: [ // Pasamos explícitamente la URL y otros datos útiles a la vista
                'acceptUrl' => $acceptUrl,
                'inviterName' => $this->invitation->inviter->name ?? 'El equipo', // Nombre de quien invita (con fallback)
                'invitedEmail' => $this->invitation->email,
                // Puedes pasar más datos si los necesitas en la plantilla del email
                // 'roleName' => $this->invitation->role->name, 
            ],
        );
    }

    /**
     * Obtiene los archivos adjuntos para el mensaje.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // No necesitamos adjuntos para este correo
        return [];
    }
}