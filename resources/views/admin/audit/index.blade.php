<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registro de Auditoría') }}
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

            {{-- Sección Exportar --}}
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 shadow sm:rounded-lg border dark:border-gray-700">
                 <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Exportar Registros</h3>
                 <form method="GET" action="{{ route('admin.audit-log.export') }}" class="space-y-4">
                     {{-- ... tus campos select, date, etc. para exportar ... --}}
                     {{-- Asegúrate de que la variable $years se pase desde el controlador si usas el select de año --}}
                     @isset($years)
                     <div id="export_year_div" style="display: {{ request('export_range') == 'year' ? 'block' : 'none' }};">
                          <label for="export_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seleccionar Año:</label>
                          <select name="export_year" id="export_year" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                              <option value="">Seleccione...</option>
                              @foreach($years as $year)
                                  <option value="{{ $year }}" {{ request('export_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                              @endforeach
                          </select>
                     </div>
                     @endisset
                     {{-- ... otros campos ... --}}
                      <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                         Descargar Excel (.xlsx)
                     </button>
                 </form>
            </div>

            {{-- Sección Limpiar --}}
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-red-300 dark:border-red-700">
                  <h3 class="text-lg font-medium text-red-600 dark:text-red-400 mb-4">Limpiar Registros</h3>
                  <div class="space-y-4">
                      {{-- Form Limpiar > 3 meses --}}
                      <form method="POST" action="{{ route('admin.audit-log.clean.older-3-months') }}" onsubmit="return confirm('¿Estás MUY seguro...?');"> @csrf <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">Limpiar > 3 meses</button></form>
                      {{-- Form Limpiar por Año --}}
                      <form method="POST" action="{{ route('admin.audit-log.clean.by-year') }}" onsubmit="return confirm('¿Estás MUY seguro...?');"> @csrf <select name="clean_year" required class="mt-1 block w-auto rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"> <option value="">Año...</option> @isset($years) @foreach($years as $year) <option value="{{ $year }}">{{ $year }}</option> @endforeach @endisset </select> <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 ml-2">Limpiar Año</button></form>
                      {{-- Form Limpiar TODO --}}
                      <form method="POST" action="{{ route('admin.audit-log.clean.all') }}" onsubmit="return confirm('¡¡ADVERTENCIA EXTREMA!!...');"> @csrf <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-900">Limpiar TODO</button></form>
                  </div>
            </div>


            {{-- Tabla de Auditoría --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Historial de Actividad') }}
                    </h3>
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
                                    <tr class="odd:bg-white dark:odd:bg-gray-800 even:bg-gray-50 dark:even:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $activity->causer?->name ?? ($activity->causer_id ? 'Usuario ID:'.$activity->causer_id : 'Sistema') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                             <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                 @if($activity->description == 'created') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100
                                                 @elseif($activity->description == 'updated') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100
                                                 @elseif($activity->description == 'deleted') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100
                                                 @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100 @endif">
                                                 {{ $activity->description }}
                                             </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $activity->subject_type ? class_basename($activity->subject_type) : 'N/A' }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $activity->subject_id ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            @if($activity->properties->count() > 0)
                                                <pre class="text-xs overflow-x-auto max-w-xs bg-gray-100 dark:bg-gray-700 p-1 rounded"><code>{{ $activity->properties->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"> {{-- Ajustar colspan --}}
                                            No hay actividades registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                             {{-- Paginación --}}
                             @if ($activities->hasPages())
                             <tfoot>
                                 <tr>
                                     <td colspan="6" class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700"> {{-- Ajustar colspan --}}
                                          {{ $activities->links() }}
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

    {{-- Script para exportar/limpiar (si lo tenías) --}}
    @push('scripts')
        {{-- ... tu script existente para los dropdowns de exportar/limpiar ... --}}
        {{-- Ejemplo básico para el dropdown de exportar (si no lo tienes) --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const exportRangeSelect = document.getElementById('export_range');
                const exportYearDiv = document.getElementById('export_year_div');
                const exportCustomRangeDiv = document.getElementById('export_custom_range_div');
                // ... (resto del script para mostrar/ocultar campos) ...
                 function toggleExportFields() {
                     if (!exportRangeSelect || !exportYearDiv || !exportCustomRangeDiv) return;
                     const selectedRange = exportRangeSelect.value;
                     exportYearDiv.style.display = 'none';
                     exportCustomRangeDiv.style.display = 'none';
                     if (selectedRange === 'year') { exportYearDiv.style.display = 'block'; } 
                     else if (selectedRange === 'custom') { exportCustomRangeDiv.style.display = 'grid'; }
                 }
                 if(exportRangeSelect) {
                     toggleExportFields(); 
                     exportRangeSelect.addEventListener('change', toggleExportFields);
                 }
            });
        </script>
    @endpush

</x-app-layout>
