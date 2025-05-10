{{-- Usamos el layout para invitados de Breeze --}}
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Estás a un paso de unirte a :app_name. Por favor, completa tu registro estableciendo tu nombre y contraseña.', ['app_name' => config('app.name')]) }}
    </div>

    {{-- Mostrar Errores Generales (si vienen de la sesión) --}}
    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

     {{-- Mostrar Errores de Validación Estándar --}}
     @if ($errors->any())
        <div class="mb-4">
            <div class="font-medium text-red-600 dark:text-red-400">{{ __('¡Ups! Algo salió mal.') }}</div>

            <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario de Registro Final --}}
    <form method="POST" action="{{ route('invitation.register') }}">
        @csrf

        {{-- Este campo envía el token de vuelta para verificar la invitación al procesar --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Mostramos el email de la invitación, pero no dejamos que lo cambie --}}
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700"
                          type="email" name="email" :value="$email" readonly />
             {{-- No se necesita @error aquí porque es de solo lectura --}}
        </div>

        <div class="mt-4">
            <x-input-label for="name" :value="__('Nombre Completo')" />
            <x-text-input id="name" class="block mt-1 w-full"
                          type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Completar Registro') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>