<x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Configuración General') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                 {{-- Mensajes Flash --}}
                 @if (session('success')) <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Éxito!</strong> <span class="block sm:inline">{{ session('success') }}</span></div> @endif
                 @if (session('error')) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error!</strong> <span class="block sm:inline">{{ session('error') }}</span></div> @endif
                 @if ($errors->any()) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error de Validación!</strong> <ul class="mt-3 list-disc list-inside text-sm text-red-600"> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif
                 {{-- Fin Mensajes Flash --}}

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            {{ __('Ajustes de la Aplicación') }}
                        </h3>

                        <form method="post" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                            @csrf
                            @method('put')

                            {{-- Nombre de la Aplicación --}}
                            <div>
                                <x-input-label for="app_name" :value="__('Nombre de la Aplicación')" />
                                {{-- Usar el valor de $settings si existe, sino el de config/app.php --}}
                                <x-text-input id="app_name" name="app_name" type="text" class="mt-1 block w-full" :value="old('app_name', $settings['app_name'] ?? config('app.name', 'Laravel'))" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('app_name')" />
                            </div>

                            {{-- Aquí puedes añadir más campos, por ejemplo: --}}
                            {{--
                            <div class="mt-4">
                                <x-input-label for="contact_email" :value="__('Email de Contacto')" />
                                <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email', $settings['contact_email'] ?? '')" />
                                <x-input-error class="mt-2" :messages="$errors->get('contact_email')" />
                            </div>
                            --}}

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Guardar Cambios') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </x-app-layout>
    