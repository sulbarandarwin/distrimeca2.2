<x-app-layout>
    @section('title', __('Editar Usuario') . ' - ' . $user->name)

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Usuario:') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
             {{-- Mensajes Flash y Errores --}}
             @if (session('success')) <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Éxito!</strong> <span class="block sm:inline">{{ session('success') }}</span></div> @endif
             @if (session('error')) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error!</strong> <span class="block sm:inline">{{ session('error') }}</span></div> @endif
             @if ($errors->any()) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error de Validación!</strong> <ul class="mt-1 list-disc list-inside text-sm text-red-600"> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif
             {{-- Fin Mensajes --}}

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT') 

                    {{-- Obtener ID del rol Proveedor --}}
                    @php
                        $proveedorRoleId = \Spatie\Permission\Models\Role::where('name', 'Proveedor')->first()?->id;
                    @endphp

                    {{-- Pasamos el ID del rol Proveedor a Alpine --}}
                    <div class="p-6 space-y-6" 
                         x-data="{ 
                             selectedRole: {{ old('role_id', $currentUserRoleId ?? 'null') ?? 'null' }}, 
                             proveedorRoleId: {{ $proveedorRoleId ?? 'null' }} 
                         }">

                        {{-- Campo Nombre --}}
                        <div>
                            <x-input-label for="name" :value="__('Nombre')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        {{-- Campo Email --}}
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        {{-- Campo Rol --}}
                        <div class="mt-4">
                             <x-input-label for="role_id" :value="__('Rol')" />
                             <select name="role_id" id="role_id" required x-model="selectedRole"
                                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                                 <option value="">Seleccionar Rol...</option>
                                 @foreach($roles as $id => $name)
                                     <option value="{{ $id }}" 
                                             @if( $id == old('role_id', $currentUserRoleId ?? null) ) selected @endif> 
                                         {{ $name }}
                                     </option>
                                 @endforeach
                             </select>
                             <x-input-error class="mt-2" :messages="$errors->get('role_id')" />
                        </div>

                        {{-- CAMPO PROVEEDORES (MULTI-SELECT) --}}
                        {{-- Usamos x-show para mostrar/ocultar --}}
                        <div class="mt-4" x-show="parseInt(selectedRole) === proveedorRoleId" x-transition> 
                            <x-input-label for="suppliers" :value="__('Proveedores Asociados (si Rol es Proveedor)')" />
                            {{-- Usamos select multiple y nombre de array suppliers[] --}}
                            <select name="suppliers[]" id="supplier-select" multiple 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                                {{-- No necesita opción vacía --}}
                                @foreach($suppliers as $id => $name)
                                    <option value="{{ $id }}" 
                                        {{-- Marcar si está en old() o en los asociados actuales ($userSupplierIds) --}}
                                        @if( in_array($id, old('suppliers', $userSupplierIds)) ) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('suppliers')" /> 
                            <x-input-error class="mt-2" :messages="$errors->get('suppliers.*')" /> 
                             <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecciona uno o más proveedores. Solo aplica si el rol es 'Proveedor'.</p>
                        </div>
                        {{-- FIN CAMPO PROVEEDORES --}}


                        {{-- Campos Contraseña (Opcional en edición) --}}
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Nueva Contraseña (Opcional)')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-4 bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t dark:border-gray-700 sm:rounded-b-lg">
                         <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                        <x-primary-button>
                            {{ __('Actualizar Usuario') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script para inicializar Choices.js en el select de proveedor --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const supplierElement = document.getElementById('supplier-select');
            if (supplierElement) {
                // Inicializar Choices.js para el multi-select
                const choices = new Choices(supplierElement, {
                    searchEnabled: true,        
                    removeItemButton: true, // Permitir quitar selecciones
                    placeholderValue: 'Escribe o selecciona proveedores...', 
                    searchPlaceholderValue: 'Buscar proveedor...', 
                    noResultsText: 'No se encontraron proveedores',
                    noChoicesText: 'No hay más opciones para seleccionar',
                    allowHTML: false, 
                    // Puedes añadir más opciones si necesitas
                });
            }
        });
    </script>
    @endpush

</x-app-layout>
