<x-app-layout>
    {{-- Título específico para esta página --}}
    @section('title', __('Dashboard'))

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensaje de Bienvenida General --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __('¡Bienvenido/a de nuevo,') }} {{ Auth::user()->name }}!
                </div>
            </div>

            {{-- Contenido Específico por Rol (Excluyendo Admin aquí) --}}

            {{-- === Contenido para Proveedor === --}}
            @role('Proveedor')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tus Últimos Productos</h3>
                        {{-- Asegúrate que $supplierProducts se pasa desde DashboardController para el rol Proveedor --}}
                        @if(isset($supplierProducts) && $supplierProducts->count() > 0)
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                 @foreach($supplierProducts as $product)
                                     <li class="py-3 flex justify-between items-center">
                                         <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $product->name }} ({{ $product->code ?? 'Sin código' }})</span>
                                         <span class="text-xs text-gray-500 dark:text-gray-400">{{ $product->created_at->diffForHumans() }}</span>
                                     </li>
                                 @endforeach
                            </ul>
                            <a href="{{ route('admin.products.index') }}" class="mt-4 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos tus productos &rarr;</a>
                         @else
                             <p class="text-sm text-gray-500 dark:text-gray-400">Aún no tienes productos registrados o no se pudieron cargar.</p>
                         @endif
                    </div>
                </div>
                 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                     <a href="{{ route('admin.import_export.index') }}">
                         <x-secondary-button>Importar/Exportar Productos</x-secondary-button>
                     </a>
                 </div>
            @endrole

            {{-- === Contenido para Vendedor === --}}
             @role('Vendedor')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                         <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Acciones Rápidas</h3>
                        <div class="space-y-3">
                            @can('create products')
                            <a href="{{ route('admin.products.create') }}" class="block">
                                <x-primary-button class="w-full justify-center">Crear Nuevo Producto</x-primary-button>
                            </a>
                            @endcan
                            @can('send invitations')
                             <a href="{{ route('invitations.create') }}" class="block">
                                 <x-secondary-button class="w-full justify-center">Invitar Usuario</x-secondary-button>
                             </a>
                            @endcan
                        </div>
                    </div>
                     <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                         <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Últimos Proveedores</h3>
                          {{-- Asegúrate que $latestSuppliers se pasa desde DashboardController para el rol Vendedor --}}
                          @if(isset($latestSuppliers) && $latestSuppliers->count() > 0)
                             <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                 @foreach($latestSuppliers as $supplier)
                                     <li class="py-2 flex justify-between items-center">
                                         <span class="text-sm text-gray-800 dark:text-gray-200">{{ $supplier->name }}</span>
                                         <span class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->created_at->diffForHumans() }}</span>
                                     </li>
                                 @endforeach
                             </ul>
                             <a href="{{ route('admin.suppliers.index') }}" class="mt-3 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos &rarr;</a>
                         @else
                             <p class="text-sm text-gray-500 dark:text-gray-400">No hay proveedores recientes o no se pudieron cargar.</p>
                         @endif
                     </div>
                </div>
            @endrole

             {{-- === Contenido para Cliente === --}}
             @role('Cliente')
                 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                     <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Encuentra lo que necesitas</h3>
                     <a href="{{ route('search.index') }}">
                         <x-primary-button>
                             Ir a la Búsqueda de Productos
                         </x-primary-button>
                     </a>
                 </div>
             @endrole

             {{-- Mensaje por defecto si no es ninguno de los roles anteriores y no es Admin --}}
             @if(!Auth::user()->hasAnyRole(['Proveedor', 'Vendedor', 'Cliente', 'Admin']))
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-900 dark:text-gray-100">No hay información específica del dashboard para tu rol.</p>
                </div>
             @endif

        </div>
    </div>
</x-app-layout>