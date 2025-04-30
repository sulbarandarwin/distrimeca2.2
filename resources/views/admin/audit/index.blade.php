<x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Registro de Auditoría') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acción</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modelo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID Modelo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Detalles</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha y Hora</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($activities as $activity)
                                        <tr>
                                            {{-- Usuario que causó el evento (si existe) --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $activity->causer?->name ?? ($activity->causer_id ? 'Usuario ID:'.$activity->causer_id : 'Sistema') }}
                                            </td>
                                            {{-- Descripción del evento (created, updated, deleted) --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($activity->description == 'created') bg-green-100 text-green-800
                                                    @elseif($activity->description == 'updated') bg-yellow-100 text-yellow-800
                                                    @elseif($activity->description == 'deleted') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $activity->description }}
                                                </span>
                                            </td>
                                            {{-- Tipo de modelo afectado (ej: Product, User) --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{-- Mostrar solo el nombre corto del modelo --}}
                                                {{ $activity->subject_type ? class_basename($activity->subject_type) : 'N/A' }}
                                            </td>
                                             {{-- ID del modelo afectado --}}
                                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $activity->subject_id ?? 'N/A' }}
                                            </td>
                                            {{-- Detalles (cambios realizados) --}}
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                @if($activity->properties->has('attributes') || $activity->properties->has('old'))
                                                    <ul class="list-disc list-inside text-xs max-w-md">
                                                    {{-- Mostrar cambios para 'updated' --}}
                                                    @if($activity->description == 'updated' && $activity->properties->has('old'))
                                                        @foreach($activity->properties['attributes'] as $key => $newValue)
                                                            @php $oldValue = $activity->properties['old'][$key] ?? null; @endphp
                                                            {{-- Mostrar solo si el valor realmente cambió --}}
                                                            @if($oldValue != $newValue)
                                                                <li class="truncate" title="{{ $key }}: {{ $oldValue }} -> {{ $newValue }}">
                                                                    <strong>{{ $key }}:</strong> <span class="text-red-500">{{ Str::limit($oldValue, 20) }}</span> -> <span class="text-green-500">{{ Str::limit($newValue, 20) }}</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    {{-- Mostrar atributos para 'created' --}}
                                                    @elseif($activity->description == 'created')
                                                         @foreach($activity->properties->get('attributes', []) as $key => $value)
                                                             <li class="truncate" title="{{ $key }}: {{ $value }}"><strong>{{ $key }}:</strong> {{ Str::limit($value, 30) }}</li>
                                                         @endforeach
                                                    @endif
                                                    </ul>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            {{-- Fecha y Hora --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $activity->created_at->format('d/m/Y H:i:s') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                                No hay actividades registradas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Links de paginación --}}
                        <div class="mt-4">
                            {{ $activities->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
    