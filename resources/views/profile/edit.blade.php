<x-app-layout>
@section('title', __('Editar Perfil')) {{-- <--- AÑADIR ESTO --}}

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Formulario Información de Perfil --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Formulario Actualizar Contraseña --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- SECCIÓN PREFERENCIAS --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <script>
                    window.initialDarkModePreference = {!! json_encode(Auth::user()->dark_mode_enabled) !!}; 
                </script>

                <div class="max-w-xl" 
                     x-data="{ 
                        initialPreference: window.initialDarkModePreference, 
                        darkModeEnabled: false, 
                        preferenceSet: false,   
                        feedbackMessage: '',
                        isSaving: false,
                        
                        init() {
                            if (this.initialPreference === null) {
                                this.darkModeEnabled = window.matchMedia('(prefers-color-scheme: dark)').matches;
                                this.preferenceSet = false;
                                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                                    if (!this.preferenceSet) { 
                                        this.darkModeEnabled = event.matches;
                                        this.applyDarkMode(this.darkModeEnabled);
                                    }
                                });
                            } else {
                                this.darkModeEnabled = this.initialPreference; 
                                this.preferenceSet = true;
                            }
                            this.applyDarkMode(this.darkModeEnabled); 
                        },

                        toggleDarkMode() {
                            // console.log('Toggle Clicked! Current state:', this.darkModeEnabled); 
                            this.darkModeEnabled = !this.darkModeEnabled;
                            this.preferenceSet = true; 
                            // console.log('New state:', this.darkModeEnabled); 
                            this.applyDarkMode(this.darkModeEnabled); // Volvemos a llamar a apply
                            this.savePreference(); 
                            // this.feedbackMessage = 'Estado cambiado (solo en memoria)'; 
                            // setTimeout(() => this.feedbackMessage = '', 3000); 
                        },

                        applyDarkMode(enabled) {
                            if (enabled) {
                                document.documentElement.classList.add('dark');
                                localStorage.theme = 'dark'; 
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.theme = 'light';
                            }
                        },

                        savePreference() {
                            this.isSaving = true;
                            this.feedbackMessage = ''; 
                            fetch('{{ route('profile.dark-mode.update') }}', { /* ... */ })
                            .then(response => { /* ... */ }).then(data => { /* ... */ })
                            .catch(error => { /* ... */ }).finally(() => { /* ... */ });
                        }
                     }"
                >
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Preferencias de Visualización') }}
                    </h2>
            
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Ajusta la apariencia visual de la aplicación.') }}
                    </p>

                    <div class="mt-6 space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="dark-mode-label">{{ __('Modo Oscuro') }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400" id="dark-mode-description">
                                    {{ __('Activa el tema oscuro para reducir el brillo.') }} 
                                    <span x-show="!preferenceSet" class="text-xs italic">({{ __('Usando preferencia del sistema') }})</span>
                                </span>
                            </span>
                             <button 
                                type="button" 
                                @click="toggleDarkMode()"
                                {{-- Sintaxis :class simplificada --}}
                                :class="darkModeEnabled ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600'" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" 
                                role="switch" 
                                :aria-checked="darkModeEnabled.toString()"
                                aria-labelledby="dark-mode-label"
                                aria-describedby="dark-mode-description">
                                <span aria-hidden="true"
                                    {{-- Sintaxis :class simplificada para el span interior --}}
                                    :class="darkModeEnabled ? 'translate-x-5' : 'translate-x-0'" 
                                    {{-- Añadir estilo inline temporal para forzar transform --}}
                                    {{-- :style="darkModeEnabled ? 'transform: translateX(1.25rem);' : 'transform: translateX(0);'" --}}
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                                </span>
                            </button>
                        </div>
                         {{-- Mensaje de feedback --}}
                         <div class="text-sm text-gray-600 dark:text-gray-400 h-4" x-show="feedbackMessage" x-text="feedbackMessage" x-transition.opacity.duration.500ms></div>
                         {{-- Párrafo de depuración (opcional) --}}
                         {{-- <p class="mt-2 text-white">Estado Actual: <span x-text="darkModeEnabled ? 'Activado (true)' : 'Desactivado (false)'"></span></p> --}}
                    </div>
                </div> 
            </div>

            {{-- Formulario Eliminar Usuario --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
