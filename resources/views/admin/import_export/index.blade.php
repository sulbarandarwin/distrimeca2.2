<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Importar / Exportar Datos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Mensajes Flash --}}
            @if (session('success')) <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Éxito!</strong> <span class="block sm:inline">{{ session('success') }}</span></div> @endif
            @if (session('error')) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error!</strong> <span class="block sm:inline">{{ session('error') }}</span></div> @endif
            @if ($errors->any()) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error de Validación!</strong> <ul class="mt-1 list-disc list-inside text-sm text-red-600"> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif
            {{-- Fin Mensajes Flash --}}

            {{-- Tarjeta de Exportación (Solo visible para Admin) --}}
            @role('Admin')
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Exportar Datos') }}
                    </h3>
                    <div class="space-y-4">
                        {{-- Exportar Proveedores --}}
                        <div>
                            <x-input-label :value="__('Exportar Proveedores')" />
                            {{-- Usamos el nombre de ruta completo generado por el grupo con prefijo --}}
                            <a href="{{ route('admin.import_export.export.suppliers') }}" class="mt-1 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Descargar Excel (.xlsx)
                            </a>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Descarga todos los proveedores registrados.
                            </p>
                        </div>
                         {{-- Exportar Productos --}}
                         <div>
                            <x-input-label :value="__('Exportar Productos')" />
                             {{-- Usamos el nombre de ruta completo generado por el grupo con prefijo --}}
                            <a href="{{ route('admin.import_export.export.products') }}" class="mt-1 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Descargar Excel (.xlsx)
                            </a>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Descarga todos los productos registrados con sus proveedores y categorías.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endrole {{-- Cierre del @role('Admin') para Exportación --}}

            {{-- Tarjeta de Importación --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Importar Datos') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Sube un archivo Excel (.xlsx) para añadir o actualizar datos masivamente. Asegúrate de usar las plantillas correctas.
                    </p>

                    {{-- Formulario Importar Proveedores (Solo Admin) --}}
                    @role('Admin')
                    <form action="{{ route('admin.import_export.import.suppliers') }}" method="POST" enctype="multipart/form-data" class="space-y-4 border-t dark:border-gray-700 pt-4">
                        @csrf
                        <div x-data="{ fileName: null }">
                            <x-input-label for="import_suppliers_file" :value="__('Importar Proveedores (.xlsx)')" />
                            <div class="mt-1 flex items-center space-x-2">
                                <x-secondary-button type="button" x-on:click.prevent="$refs.supplierFile.click()">
                                    {{ __('Seleccionar Archivo') }}
                                </x-secondary-button>
                                <input type="file" name="import_file" class="hidden" x-ref="supplierFile" required accept=".xlsx"
                                       x-on:change="fileName = $refs.supplierFile.files[0] ? $refs.supplierFile.files[0].name : null">
                                <span x-text="fileName" class="text-sm text-gray-500 dark:text-gray-400"></span>
                            </div>
                            <x-primary-button type="submit" class="mt-2">
                                {{ __('Importar Proveedores') }}
                            </x-primary-button>
                            <x-input-error :messages="$errors->get('import_file_suppliers')" class="mt-2" /> {{-- Usar una clave de error específica --}}
                        </div>
                    </form>
                    @endrole

                    {{-- Formulario Importar Productos (Visible para Admin y Proveedor) --}}
                    @role('Admin|Proveedor')
                    <form action="{{ route('admin.import_export.import.products') }}" method="POST" enctype="multipart/form-data" class="space-y-4 border-t dark:border-gray-700 pt-4 @role('Admin') mt-6 @endrole"> {{-- Margen si Admin ve ambos forms --}}
                        @csrf
                        <div x-data="{ fileName: null }">
                            <x-input-label for="import_products_file" :value="__('Importar Productos (.xlsx)')" />
                            <div class="mt-1 flex items-center space-x-2">
                                <x-secondary-button type="button" x-on:click.prevent="$refs.productFile.click()">
                                    {{ __('Seleccionar Archivo') }}
                                </x-secondary-button>
                                 <input type="file" name="import_file" class="hidden" x-ref="productFile" required accept=".xlsx"
                                        x-on:change="fileName = $refs.productFile.files[0] ? $refs.productFile.files[0].name : null">
                                 <span x-text="fileName" class="text-sm text-gray-500 dark:text-gray-400"></span>
                            </div>
                            <x-primary-button type="submit" class="mt-2">
                                {{ __('Importar Productos') }}
                            </x-primary-button>
                             {{-- Usar una clave de error diferente para no solaparse --}}
                            <x-input-error :messages="$errors->get('import_file_products')" class="mt-2" />
                             @error('import_file') {{-- Error genérico si no se especifica --}}
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                             @enderror
                        </div>
                    </form>
                    @endrole {{-- Asegurarse que este @endrole exista y cierre correctamente --}}

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
