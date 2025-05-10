<x-app-layout>
    @section('title', __('Gestión de Productos')) {{-- Definir título --}}

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Productos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensajes Flash --}}
            @if (session('success')) <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Éxito!</strong> <span class="block sm:inline">{{ session('success') }}</span></div> @endif
            @if (session('error')) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error!</strong> <span class="block sm:inline">{{ session('error') }}</span></div> @endif
            {{-- Fin Mensajes Flash --}}

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Cabecera con Título y Botones --}}
                    <div class="flex flex-wrap justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Listado de Productos') }}
                        </h3>
                        {{-- Contenedor para botones --}}
                        <div class="flex items-center space-x-2">
                            {{-- NUEVO: Botón Importar/Exportar (Visible si tiene permiso) --}}
                            @canany(['import products', 'import suppliers', 'export products', 'export suppliers'])
                                <a href="{{ route('admin.import_export.index') }}">
                                    <x-secondary-button> 
                                        {{ __('Importar / Exportar') }}
                                    </x-secondary-button>
                                </a>
                            @endcanany
                             {{-- Botón Crear (Visible si tiene permiso) --}}
                            @can('crear productos') {{-- Usar permiso en español --}}
                            <a href="{{ route('admin.products.create') }}">
                                <x-primary-button>
                                    {{ __('Crear Nuevo Producto') }}
                                </x-primary-button>
                            </a>
                            @endcan
                        </div>
                    </div>

                    {{-- Contenedor para la tabla y paginación AJAX --}}
                    <div id="product-table-container" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Código</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proveedor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            {{-- Incluir el contenido inicial de la tabla desde el parcial --}}
                            @include('admin.products.partials._table_data', ['products' => $products]) 
                        </table>
                    </div> 
                    {{-- Fin del contenedor AJAX --}}

                </div>
            </div>
        </div>
    </div>

    {{-- Script para manejar la paginación AJAX --}}
    @push('scripts')
        {{-- ... (Tu script AJAX existente) ... --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tableContainer = document.getElementById('product-table-container');
                if (tableContainer) {
                    tableContainer.addEventListener('click', function (event) { /* ... tu lógica fetch ... */ });
                    window.addEventListener('popstate', function (event) { window.location.reload(); });
                }
            });
        </script>
    @endpush

</x-app-layout>
