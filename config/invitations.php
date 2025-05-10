<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duración de la Validez de la Invitación
    |--------------------------------------------------------------------------
    |
    | Define cuántos días será válida una invitación antes de expirar.
    | Este valor puede ser sobreescrito por la variable de entorno INVITATION_EXPIRE_DAYS.
    |
    */

    'expire_days' => env('INVITATION_EXPIRE_DAYS', 2), // Valor por defecto: 2 días

];