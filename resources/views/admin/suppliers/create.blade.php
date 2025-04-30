<x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Crear Nuevo Proveedor') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        {{-- Mostrar errores de validación --}}
                        <x-auth-session-status class="mb-4" :status="session('status')" /> {{-- Para mensajes generales --}}
                        @if ($errors->any())
                            <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400">
                                {{ __('¡Ups! Algo salió mal.') }}
                            </div>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif


                        <form method="POST" action="{{ route('admin.suppliers.store') }}">
                            @csrf

                            <div class="mt-4">
                                <x-input-label for="name" :value="__('Nombre')" /> <span class="text-red-500">*</span>
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="rif" :value="__('RIF')" />
                                <x-text-input id="rif" class="block mt-1 w-full" type="text" name="rif" :value="old('rif')" />
                                <x-input-error :messages="$errors->get('rif')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label for="phone1" :value="__('Teléfono 1')" />
                                <x-text-input id="phone1" class="block mt-1 w-full" type="text" name="phone1" :value="old('phone1')" />
                                <x-input-error :messages="$errors->get('phone1')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label for="phone2" :value="__('Teléfono 2')" />
                                <x-text-input id="phone2" class="block mt-1 w-full" type="text" name="phone2" :value="old('phone2')" />
                                <x-input-error :messages="$errors->get('phone2')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="country_id" :value="__('País')" /> <span class="text-red-500">*</span>
                                <select id="country_id" name="country_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">{{ __('Seleccione un país...') }}</option>
                                    @foreach ($countries as $id => $name)
                                        <option value="{{ $id }}" {{ old('country_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('country_id')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label for="state_id" :value="__('Estado')" /> <span class="text-red-500">*</span>
                                <select id="state_id" name="state_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">{{ __('Seleccione un estado...') }}</option>
                                    {{-- Nota: Idealmente, este select debería llenarse dinámicamente con JS basado en el país. Por ahora muestra todos. --}}
                                    @foreach ($states as $id => $name)
                                        <option value="{{ $id }}" {{ old('state_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('state_id')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label :value="__('Tipos de Proveedor')" /> <span class="text-red-500">*</span>
                                <div class="mt-2 space-y-2">
                                    @forelse ($supplierTypes as $type)
                                        <label for="type_{{ $type->id }}" class="flex items-center">
                                            <input id="type_{{ $type->id }}" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                                   name="supplier_types[]" value="{{ $type->id }}"
                                                   {{ in_array($type->id, old('supplier_types', [])) ? 'checked' : '' }}>
                                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $type->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay tipos de proveedor definidos.</p>
                                    @endforelse
                                </div>
                                <x-input-error :messages="$errors->get('supplier_types')" class="mt-2" />
                                <x-input-error :messages="$errors->get('supplier_types.*')" class="mt-2" />
                             </div>


                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.suppliers.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                    {{ __('Cancelar') }}
                                </a>

                                <x-primary-button>
                                    {{ __('Guardar Proveedor') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
    