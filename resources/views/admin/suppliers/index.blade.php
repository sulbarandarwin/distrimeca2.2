<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Proveedores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensajes Flash (Usando @if directamente) --}}
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
                     <ul class="mt-1 list-disc list-inside text-sm text-red-600"> 
                         @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach 
                     </ul> 
                 </div> 
             @endif
            {{-- Fin Mensajes Flash --}}

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Listado de Proveedores') }}
                        </h3>
                        {{-- Botón Crear (solo si tiene permiso 'manage suppliers') --}}
                        @can('manage suppliers')
                        <a href="{{ route('admin.suppliers.create') }}">
                            <x-primary-button>
                                {{ __('Crear Nuevo Proveedor') }}
                            </x-primary-button>
                        </a>
                        @endcan
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    {{-- Añadir padding a los encabezados --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">RIF</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teléfono 1</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">País</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipos</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($suppliers as $supplier)
                                    {{-- Añadir clases a la fila --}}
                                    <tr class="odd:bg-white dark:odd:bg-gray-800 even:bg-gray-50 dark:even:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                                        {{-- Añadir padding a las celdas --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $supplier->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $supplier->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $supplier->rif ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $supplier->email ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $supplier->phone1 ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $supplier->country->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $supplier->state->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{-- Mostrar tipos como badges/pills --}}
                                            @foreach($supplier->types as $type)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 mr-1 dark:bg-blue-900 dark:text-blue-100">
                                                    {{ $type->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            {{-- Botones según permiso 'manage suppliers' --}}
                                            @can('manage suppliers')
                                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Editar</a>
                                                <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este proveedor? Esto podría afectar productos asociados.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Borrar</button>
                                                </form>
                                            @else
                                                {{-- Vendedor solo puede ver, no mostrar acciones --}}
                                                <span class="text-gray-400 dark:text-gray-500 italic">N/A</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"> {{-- Ajustar colspan --}}
                                            No hay proveedores registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                             {{-- Paginación (si la tienes en el controlador) --}}
                             {{-- Asegúrate que la variable $suppliers sea un objeto Paginator --}}
                             @if ($suppliers instanceof \Illuminate\Pagination\LengthAwarePaginator && $suppliers->hasPages())
                             <tfoot>
                                 <tr>
                                     <td colspan="9" class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                          {{ $suppliers->links() }}
                                     </td>
                                 </tr>
                             </tfoot>
                             @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
