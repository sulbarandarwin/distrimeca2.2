<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crear Nuevo Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Mostar errores de validación --}}
                    <x-auth-session-status class="mb-4" :status="session('status')" />
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

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nombre')" /> <span class="text-red-500">*</span>
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" /> <span class="text-red-500">*</span>
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Contraseña')" /> <span class="text-red-500">*</span>
                            <x-text-input id="password" class="block mt-1 w-full"
                                            type="password"
                                            name="password"
                                            required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" /> <span class="text-red-500">*</span>
                            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                            type="password"
                                            name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label :value="__('Roles')" /> <span class="text-red-500">*</span>
                            <div class="mt-2 space-y-2">
                                @forelse ($roles as $role)
                                    <label for="role_{{ $role->id }}" class="flex items-center">
                                        <input id="role_{{ $role->id }}" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                               name="roles[]" value="{{ $role->id }}"
                                               {{-- Marcamos el checkbox si estaba seleccionado en caso de error de validación --}}
                                               {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>

                                        {{-- Aplicamos el estilo de píldora al nombre del rol --}}
                                        <span class="ms-2 text-sm px-2 inline-flex leading-5 font-semibold rounded-full
                                            @if($role->name == 'Admin') bg-red-100 text-red-800 @elseif($role->name == 'Vendedor' || $role->name == 'Supervisor') bg-yellow-100 text-yellow-800 @elseif($role->name == 'Proveedor') bg-purple-100 text-purple-800 @elseif($role->name == 'Cliente') bg-gray-100 text-gray-800 @else bg-green-100 text-green-800 @endif">
                                            {{ $role->name }}
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay roles definidos.</p>
                                @endforelse
                            </div>
                            <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                            <x-input-error :messages="$errors->get('roles.*')" class="mt-2" />
                         </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button>
                                {{ __('Guardar Usuario') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
