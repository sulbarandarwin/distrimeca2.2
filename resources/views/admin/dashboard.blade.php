<x-app-layout>
    @section('title', __('Admin Dashboard'))

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel de Administración de') }} <span class="text-indigo-600 dark:text-indigo-400">{{ $appName ?? 'Distrimeca' }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Saludo --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-lg">
                        {{ __('¡Bienvenido de nuevo,') }} <span class="font-semibold">{{ Auth::user()->name }}</span>!
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Aquí tienes un resumen del estado actual del sistema.</p>
                </div>
            </div>

            {{-- Sección de Estadísticas Principales --}}
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Estadísticas del Sistema</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Tarjeta Usuarios Totales --}}
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow-lg rounded-lg p-5 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 dark:bg-indigo-600 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Usuarios Totales</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-white">{{ $userCount ?? '0' }}</dd>
                            </div>
                        </div>
                    </div>
                    {{-- Tarjeta Proveedores Totales --}}
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow-lg rounded-lg p-5 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 dark:bg-green-600 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Proveedores</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-white">{{ $supplierCount ?? '0' }}</dd>
                            </div>
                        </div>
                    </div>
                    {{-- Tarjeta Productos Totales --}}
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow-lg rounded-lg p-5 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 dark:bg-blue-600 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Productos</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-white">{{ $productCount ?? '0' }}</dd>
                            </div>
                        </div>
                    </div>
                    {{-- Tarjeta Roles Activos --}}
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow-lg rounded-lg p-5 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 dark:bg-yellow-600 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.588-3.766z" /></svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Roles Definidos</dt>
                                <dd class="text-3xl font-bold text-gray-900 dark:text-white">{{ $activeRoles ?? '0' }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Columna Izquierda: Accesos Directos y Distribución de Roles --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Sección de Accesos Directos --}}
                    @if(!empty($quickAccessLinks))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Accesos Directos</h3>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($quickAccessLinks as $key => $link)
                                <a href="{{ $link['route'] }}" class="group flex flex-col items-center justify-center p-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-colors duration-150">
                                    <div class="flex-shrink-0 bg-gray-200 dark:bg-gray-500 rounded-full p-2 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500 transition-colors duration-150">
                                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-white transition-colors duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                                        </svg>
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-150 text-center">{{ $link['label'] }}</p>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Distribución de Usuarios por Rol --}}
                    @isset($usersByRole)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Distribución de Usuarios por Rol</h3>
                            <ul class="space-y-2">
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300">Administradores:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $usersByRole->admin_count ?? 0 }}</span>
                                </li>
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300">Proveedores (usuarios):</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $usersByRole->proveedor_count ?? 0 }}</span>
                                </li>
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300">Vendedores:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $usersByRole->vendedor_count ?? 0 }}</span>
                                </li>
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300">Clientes:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $usersByRole->cliente_count ?? 0 }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endisset
                </div>

                {{-- Columna Derecha: Última Actividad --}}
                <div class="lg:col-span-2">
                    @if(isset($latestActivities) && $latestActivities->count() > 0)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Última Actividad del Sistema</h3>
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    @foreach($latestActivities as $index => $activity)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 dark:bg-gray-600 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                        @if($activity->causer)
                                                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($activity->causer->name) }}&color=7F9CF5&background=EBF4FF" alt="">
                                                        @else
                                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                                            {{ $activity->description }}
                                                            @if($activity->subject_type && $activity->subject_id)
                                                                <span class="font-medium text-gray-800 dark:text-gray-100">({{ class_basename($activity->subject_type) }} ID: {{ $activity->subject_id }})</span>
                                                            @endif
                                                            por <span class="font-medium text-gray-800 dark:text-gray-100">{{ $activity->causer?->name ?? 'Sistema' }}</span>
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                        <time datetime="{{ $activity->created_at->toIso8601String() }}" title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">{{ $activity->created_at->diffForHumans() }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('admin.audit-log.index') }}" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    Ver todo el registro de auditoría
                                </a>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-lg">
                        <div class="p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin actividad reciente</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No hay registros de actividad en el sistema por el momento.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>