<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configuración General') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

             {{-- Mensajes Flash --}}
             @if (session('success'))
                 <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                     <strong class="font-bold">¡Éxito!</strong>
                     <span class="block sm:inline">{{ session('success') }}</span>
                 </div>
             @endif
             @if (session('error'))
                  <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                      <strong class="font-bold">¡Error!</strong>
                      <span class="block sm:inline">{{ session('error') }}</span>
                  </div>
              @endif
              @if ($errors->any()) 
                 <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                     <strong class="font-bold">¡Error de Validación!</strong> 
                     <ul class="mt-1 list-disc list-inside text-sm"> 
                         @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach 
                     </ul> 
                 </div> 
             @endif
             {{-- Fin Mensajes Flash --}}

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl"> 
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Ajustes de la Aplicación') }}
                    </h3>

                    <form method="post" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('put')

                        {{-- Nombre de la Aplicación (Informativo) --}}
                        <div class="mb-4">
                           <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nombre de la Aplicación') }}</label>
                           <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ config('app.name', 'Laravel') }}</p>
                           <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gestionado desde el archivo `.env` (variable `APP_NAME`).</p>
                       </div>
                       <hr class="dark:border-gray-600 my-4">

                       {{-- Logo de la Aplicación --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Logo de la Aplicación') }}</label>
                            @php $logoPath = $settings['logo_path'] ?? null; @endphp
                            @if ($logoPath && Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath))
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo Actual" class="h-16 w-auto object-contain bg-gray-200 dark:bg-gray-700 p-1 rounded">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Logo actual. Sube uno nuevo para reemplazarlo.</p>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No hay logo cargado actualmente.</p>
                            @endif
                             <input type="file" name="logo" id="logo"
                                   class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800"/>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sube un archivo PNG, JPG, SVG, WEBP (máx. 2MB).</p>
                             @error('logo') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <hr class="dark:border-gray-600 my-4">

                        {{-- Símbolo de Moneda --}}
                       <div class="mb-4">
                           <x-input-label for="currency_symbol" :value="__('Símbolo de Moneda por Defecto')" />
                           <x-text-input id="currency_symbol" name="currency_symbol" type="text" class="mt-1 block w-full md:w-1/3" 
                                         :value="old('currency_symbol', $settings['currency_symbol'] ?? 'USD')" 
                                         required maxlength="10" />
                           <x-input-error class="mt-2" :messages="$errors->get('currency_symbol')" />
                           <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ej: USD, $, Bs., EUR, €</p>
                       </div>
                       <hr class="dark:border-gray-600 my-4">

                       {{-- Email de Notificación --}}
                       <div class="mb-4">
                           <x-input-label for="notification_email" :value="__('Email para Notificaciones')" />
                           <x-text-input id="notification_email" name="notification_email" type="email" class="mt-1 block w-full md:w-2/3" 
                                         :value="old('notification_email', $settings['notification_email'] ?? config('mail.from.address'))" 
                                         required />
                           <x-input-error class="mt-2" :messages="$errors->get('notification_email')" />
                           <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dirección usada para enviar emails del sistema (si aplica).</p>
                       </div>
                       <hr class="dark:border-gray-600 my-4">

                       {{-- NUEVO: Elementos por Página --}}
                        <div class="mb-4">
                            <x-input-label for="items_per_page" :value="__('Elementos por Página (Paginación)')" />
                            <x-text-input id="items_per_page" name="items_per_page" type="number" class="mt-1 block w-full md:w-1/3" 
                                          :value="old('items_per_page', $settings['items_per_page'] ?? 15)" 
                                          required min="5" max="100" step="1" />
                            <x-input-error class="mt-2" :messages="$errors->get('items_per_page')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cuántos ítems mostrar en los listados (ej. 5-100).</p>
                        </div>
                        <hr class="dark:border-gray-600 my-4">

                        {{-- NUEVO: País por Defecto --}}
                        <div class="mb-4">
                             <label for="default_country_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('País por Defecto') }}</label>
                             <select name="default_country_id" id="default_country_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                 <option value="">{{ __('Ninguno') }}</option>
                                 {{-- $countries viene del controlador --}}
                                 @foreach($countries as $country)
                                     <option value="{{ $country->id }}" {{ (old('default_country_id', $settings['default_country_id'] ?? null) == $country->id) ? 'selected' : '' }}>
                                         {{ $country->name }}
                                     </option>
                                 @endforeach
                             </select>
                             <x-input-error class="mt-2" :messages="$errors->get('default_country_id')" />
                             <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">País pre-seleccionado en filtros/formularios.</p>
                        </div>

                        {{-- NUEVO: Estado por Defecto --}}
                        <div class="mb-4">
                             <label for="default_state_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Estado por Defecto') }}</label>
                             <select name="default_state_id" id="default_state_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                  <option value="">{{ __('Seleccionar Estado (si aplica)...') }}</option>
                                  {{-- $statesForDefaultCountry viene del controlador --}}
                                  {{-- Se rellenará dinámicamente con JS --}}
                                  @foreach($statesForDefaultCountry as $state)
                                      <option value="{{ $state->id }}" {{ (old('default_state_id', $settings['default_state_id'] ?? null) == $state->id) ? 'selected' : '' }}>
                                          {{ $state->name }}
                                      </option>
                                  @endforeach
                             </select>
                             <x-input-error class="mt-2" :messages="$errors->get('default_state_id')" />
                              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Estado pre-seleccionado (depende del País).</p>
                        </div>

                        {{-- ... (después de los campos de país/estado por defecto) ... --}}

                        <hr class="dark:border-gray-600 my-6"> {{-- Separador opcional --}}

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                                {{ __('Búsqueda Inteligente (IA)') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Configura el proveedor de Inteligencia Artificial para la búsqueda de productos.
                            </p>
                        </div>

                        {{-- Selector de Proveedor IA --}}
                        <div>
                            <x-input-label for="ai_search_provider" :value="__('Proveedor IA para Búsqueda')" />
                            <select id="ai_search_provider" name="ai_search_provider" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="none" {{ (old('ai_search_provider', $settings['ai_search_provider'] ?? 'none') == 'none') ? 'selected' : '' }}>{{ __('Desactivado') }}</option>
                                <option value="google_gemini" {{ (old('ai_search_provider', $settings['ai_search_provider'] ?? 'none') == 'google_gemini') ? 'selected' : '' }}>{{ __('Google Gemini') }}</option>
                                <option value="openai_gpt" {{ (old('ai_search_provider', $settings['ai_search_provider'] ?? 'none') == 'openai_gpt') ? 'selected' : '' }}>{{ __('OpenAI GPT') }}</option>
                                <option value="deepseek" {{ (old('ai_search_provider', $settings['ai_search_provider'] ?? 'none') == 'deepseek') ? 'selected' : '' }}>{{ __('DeepSeek') }}</option> {{-- NUEVA --}}
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('ai_search_provider')" />
                        </div>

                        {{-- Información sobre API Key --}}
                        <div class="mt-4">
                            <x-input-label :value="__('Configuración de Clave API')" />
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                La clave API para el proveedor seleccionado debe configurarse de forma segura en el archivo <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs">.env</code> del servidor.
                                (Por ejemplo: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs">GEMINI_API_KEY</code> o <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs">OPENAI_API_KEY</code>).
                            </p>
                            @php
                            $selectedProvider = old('ai_search_provider', $settings['ai_search_provider'] ?? 'none');
                            $isKeySet = false;
                            $keyName = '';
                            if ($selectedProvider === 'google_gemini') {
                                $keyName = 'GEMINI_API_KEY';
                                if (config('services.google.gemini_api_key')) $isKeySet = true;
                            } elseif ($selectedProvider === 'openai_gpt') {
                                $keyName = 'OPENAI_API_KEY';
                                if (config('services.openai.api_key')) $isKeySet = true;
                            } elseif ($selectedProvider === 'deepseek') { // <-- NUEVA CONDICIÓN
                                $keyName = 'DEEPSEEK_API_KEY';
                                if (config('services.deepseek.api_key')) $isKeySet = true;
                            }
                            @endphp
{{-- El resto del @if para mostrar el mensaje sigue igual --}}
                            @if($selectedProvider !== 'none')
                                <p class="mt-1 text-xs {{ $isKeySet ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $isKeySet ? "✓ Variable {$keyName} parece estar configurada en .env." : "✗ Variable {$keyName} NO detectada en .env para el proveedor seleccionado." }}
                                </p>
                            @endif
                        </div>

                        {{-- ... (justo antes del div con el botón 'Guardar Cambios') ... --}}


                        {{-- Botón Guardar --}}
                        <div class="flex items-center gap-4 mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <x-primary-button>
                                {{ __('Guardar Cambios') }}
                            </x-primary-button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Añadimos script para el dropdown de estados dependiente --}}
     @push('scripts')
     <script>
         document.addEventListener('DOMContentLoaded', function () {
            const countrySelect = document.getElementById('default_country_id');
            const stateSelect = document.getElementById('default_state_id');
            // La URL de tu API - Asegúrate que 'api.states_by_country' es el nombre correcto de tu ruta API
            const apiUrl = "{{ route('api.states_by_country', ['countryId' => ':countryId']) }}"; 

            if(countrySelect && stateSelect) {
                 countrySelect.addEventListener('change', function() {
                    const countryId = this.value;
                    stateSelect.innerHTML = '<option value="">Cargando...</option>'; // Limpiar y mostrar carga

                    if (!countryId) {
                         stateSelect.innerHTML = '<option value="">Seleccionar Estado (si aplica)...</option>';
                         return; // Salir si no hay país seleccionado
                    }

                    // Construir la URL de la API reemplazando el placeholder
                    const finalApiUrl = apiUrl.replace(':countryId', countryId);

                     // Llamada AJAX (usando Fetch API)
                     fetch(finalApiUrl)
                         .then(response => {
                             if (!response.ok) {
                                 throw new Error('Error en la respuesta de la API');
                             }
                             return response.json();
                         })
                         .then(data => {
                             stateSelect.innerHTML = '<option value="">Ninguno</option>'; // Opción para no seleccionar estado
                             for (const id in data) {
                                 const option = document.createElement('option');
                                 option.value = id;
                                 option.textContent = data[id]; // Asume que la API devuelve {id: name}
                                 stateSelect.appendChild(option);
                             }
                         })
                         .catch(error => {
                              console.error('Error al cargar estados:', error);
                              stateSelect.innerHTML = '<option value="">Error al cargar</option>';
                         });
                });

                // Disparar 'change' al inicio si hay un país seleccionado por defecto (por 'old' o 'settings')
                // para cargar los estados iniciales correctos.
                if (countrySelect.value) {
                     countrySelect.dispatchEvent(new Event('change'));
                     // Pequeña espera para asegurar que el AJAX termine antes de intentar seleccionar el 'old' state
                     setTimeout(() => {
                        const oldStateId = "{{ old('default_state_id', $settings['default_state_id'] ?? '') }}";
                        if (oldStateId) {
                            stateSelect.value = oldStateId;
                        }
                    }, 500); // 500ms de espera, ajusta si es necesario
                }
            }
         });
     </script>
     @endpush

</x-app-layout>