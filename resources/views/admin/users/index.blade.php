<x-app-layout>
    @section('title', __('Gestión de Usuarios'))

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Usuarios') }}
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

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Listado de Usuarios') }}
                        </h3>
                        @can('manage users')
                        <a href="{{ route('admin.users.create') }}">
                            <x-primary-button> {{ __('Crear Nuevo Usuario') }} </x-primary-button>
                        </a>
                        @endcan
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Roles</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proveedores Asociados</th> {{-- Cambiado el título --}}
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr class="odd:bg-white dark:odd:bg-gray-800 even:bg-gray-50 dark:even:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @foreach($user->getRoleNames() as $roleName)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 mr-1 dark:bg-green-900 dark:text-green-100">
                                                    {{ $roleName }}
                                                </span>
                                            @endforeach
                                        </td>
                                        {{-- --- INICIO CAMBIO: Mostrar Múltiples Proveedores --- --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{-- Verificar si la relación 'suppliers' (plural) está cargada y no está vacía --}}
                                            @if($user->relationLoaded('suppliers') && $user->suppliers->isNotEmpty())
                                                {{-- Mapear los nombres y unirlos con coma --}}
                                                {{ $user->suppliers->pluck('name')->implode(', ') }}
                                            @else
                                                N/A 
                                            @endif
                                        </td>
                                        {{-- --- FIN CAMBIO --- --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            @can('manage users')
                                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Editar</a>
                                                @if($user->id !== 1 && Auth::user()->id !== $user->id) 
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro...?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Borrar</button>
                                                </form>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"> 
                                            No hay usuarios registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                             @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
                             <tfoot>
                                 <tr>
                                     <td colspan="6" class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700"> 
                                          {{ $users->links() }}
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
