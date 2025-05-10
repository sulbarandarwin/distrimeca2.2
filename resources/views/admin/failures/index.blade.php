<x-app-layout>
    @section('title', __('Registro de Fallas de Productos'))

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Registro de Fallas de Productos') }}
                @if(isset($filterTitle)) <span class="text-base font-normal text-gray-600 dark:text-gray-400">({{ $filterTitle }})</span> @endif
            </h2>
            @can('manage product_failures')
            <div class="mt-3 md:mt-0">
                <a href="{{ route('admin.failures.create') }}">
                    <x-primary-button>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        {{ __('Registrar Nueva Falla') }}
                    </x-primary-button>
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success')) <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert"> <span class="font-medium">Éxito!</span> {{ session('success') }}</div> @endif
            @if (session('error')) <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert"> <span class="font-medium">Error!</span> {{ session('error') }}</div> @endif
            @if (session('info')) <div class="mb-4 p-4 text-sm text-blue-700 bg-blue-100 rounded-lg dark:bg-blue-200 dark:text-blue-800" role="alert"> <span class="font-medium">Info:</span> {{ session('info') }}</div> @endif

            <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('admin.failures.index') }}" id="filterFailuresForm" class="space-y-4 md:space-y-0 md:grid md:grid-cols-3 lg:grid-cols-4 md:gap-4 md:items-end">
                    <div>
                        <x-input-label for="filter_type" value="Filtrar por:" />
                        <select name="filter_type" id="filter_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700">
                            <option value="current_week" @if(request('filter_type', $filterType ?? 'current_week') == 'current_week') selected @endif>Semana Actual</option>
                            <option value="current_month" @if(request('filter_type', $filterType ?? '') == 'current_month') selected @endif>Mes Actual</option>
                            <option value="custom_range" @if(request('filter_type', $filterType ?? '') == 'custom_range') selected @endif>Rango de Fechas</option>
                            <option value="all" @if(request('filter_type', $filterType ?? '') == 'all') selected @endif>Mostrar Todos</option>
                        </select>
                    </div>
                    <div id="custom_range_fields" class="{{ request('filter_type', $filterType ?? '') == 'custom_range' ? '' : 'hidden' }} md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="date_start" value="Fecha Inicio:" />
                            <x-text-input type="date" name="date_start" id="date_start" value="{{ request('date_start', $viewDateStart ?? '') }}" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="date_end" value="Fecha Fin:" />
                            <x-text-input type="date" name="date_end" id="date_end" value="{{ request('date_end', $viewDateEnd ?? '') }}" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div class="md:col-start-3 lg:col-start-4 flex justify-end items-center space-x-3 mt-4 md:mt-0">
                        @can('export product_failures')
                        <x-secondary-button type="button" onclick="submitExportForm();" class="bg-green-500 hover:bg-green-600 text-white dark:text-white dark:bg-green-600 dark:hover:bg-green-700">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg>
                            Exportar
                        </x-secondary-button>
                        @endcan
                        <x-primary-button type="submit">Filtrar</x-primary-button>
                    </div>
                </form>
                <form method="GET" action="{{ route('admin.failures.export') }}" id="exportFailuresForm" class="hidden">
                    <input type="hidden" name="filter_type" id="export_filter_type" value="{{ request('filter_type', $filterType ?? 'current_week') }}">
                    <input type="hidden" name="date_start" id="export_date_start" value="{{ request('date_start', $viewDateStart ?? '') }}">
                    <input type="hidden" name="date_end" id="export_date_end" value="{{ request('date_end', $viewDateEnd ?? '') }}">
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-0 md:p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-750">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha Falla</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">Registrado por</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($failures as $failure)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $failure->failure_date->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $failure->product->name ?? 'Producto no encontrado' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="font-semibold text-red-600 dark:text-red-400">EN FALLA</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate hidden md:table-cell" title="{{ $failure->description }}">{{ Str::limit($failure->description, 60) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 hidden sm:table-cell">{{ $failure->user->name ?? 'Sistema' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @can('manage product_failures')
                                        <a href="{{ route('admin.failures.edit', $failure) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Editar</a>
                                        <form action="{{ route('admin.failures.destroy', $failure) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este registro de falla?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Eliminar</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                        No hay registros de fallas para el período seleccionado.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($failures->hasPages())
                        <div class="mt-4 px-2 py-2 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                            {{ $failures->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterTypeSelect = document.getElementById('filter_type');
            const customRangeFields = document.getElementById('custom_range_fields');
            const dateStartInput = document.getElementById('date_start'); // Para el formulario de filtro
            const dateEndInput = document.getElementById('date_end');     // Para el formulario de filtro

            // Para el formulario de exportación
            const exportFilterTypeInput = document.getElementById('export_filter_type');
            const exportDateStartInput = document.getElementById('export_date_start');
            const exportDateEndInput = document.getElementById('export_date_end');

            function toggleCustomRange() {
                if (filterTypeSelect.value === 'custom_range') {
                    if(customRangeFields) customRangeFields.classList.remove('hidden');
                } else {
                    if(customRangeFields) customRangeFields.classList.add('hidden');
                }
            }
            if (filterTypeSelect) {
                filterTypeSelect.addEventListener('change', toggleCustomRange);
                toggleCustomRange();
            }

            // Función global para enviar el formulario de exportación
            window.submitExportForm = function() {
                // Actualizar los valores del formulario de exportación con los filtros actuales del formulario visible
                if(exportFilterTypeInput && filterTypeSelect) exportFilterTypeInput.value = filterTypeSelect.value;
                if(exportDateStartInput && dateStartInput) exportDateStartInput.value = dateStartInput.value;
                if(exportDateEndInput && dateEndInput) exportDateEndInput.value = dateEndInput.value;
                
                document.getElementById('exportFailuresForm').submit();
            }
        });
    </script>
    @endpush
</x-app-layout>