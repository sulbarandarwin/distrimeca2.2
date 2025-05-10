@component('mail::message')
{{-- Saludo inicial --}}
# ¡Hola {{ $invitedEmail }}!

{{-- Mensaje principal de la invitación --}}
Has sido invitado por **{{ $inviterName }}** para unirte a la plataforma **{{ config('app.name') }}**.

Para completar tu registro y activar tu cuenta, por favor haz clic en el botón de abajo.

{{-- Botón de llamada a la acción --}}
@component('mail::button', ['url' => $acceptUrl, 'color' => 'success'])
Aceptar Invitación y Crear Cuenta
@endcomponent

{{-- Información de expiración --}}
Ten en cuenta que esta invitación y el enlace son válidos únicamente hasta el:
**{{ $invitation->expires_at->format('d/m/Y \a \l\a\s H:i') }}** (hora del servidor).

{{-- Nota sobre enlace (opcional pero útil) --}}
Si tienes problemas al hacer clic en el botón "Aceptar Invitación", copia y pega la siguiente URL en tu navegador web:
{{-- Nota: Mostrar la URL completa puede ser largo, a menudo se omite si el botón funciona bien --}}
{{-- <span style="word-break: break-all;">{{ $acceptUrl }}</span> --}}

{{-- Advertencia de seguridad y qué hacer si no se espera --}}
Este enlace de invitación es personal e intransferible. No lo compartas con nadie.

Si no esperabas recibir esta invitación, puedes ignorar este mensaje sin problemas. No se creará ninguna cuenta a menos que hagas clic en el enlace de arriba y completes el registro.

{{-- Despedida --}}
Saludos,
<br>
El equipo de {{ config('app.name') }}
@endcomponent