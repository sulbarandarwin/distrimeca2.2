<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buscar y Seleccionar Productos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Formulario de Búsqueda --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6"> {{-- Sin overflow-hidden --}}
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" action="{{ route('search.results') }}">
                         <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="relative">
                                <x-input-label for="product_name" :value="__('Nombre del Producto')" />
                                <x-text-input id="product_name" class="block mt-1 w-full" type="text" name="product_name" :value="request('product_name')" placeholder="Ej: Pala, Tornillo..." autocomplete="off" />
                                <div id="product_suggestions" class="absolute z-10 w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md mt-1 shadow-lg hidden max-h-60 overflow-y-auto"></div>
                            </div>
                            <div>
                                <x-input-label for="country_id_select" :value="__('País')" />
                                <select id="country_id_select" name="country_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">{{ __('Todos') }}</option>
                                    {{-- Asegurarse que $countries exista --}}
                                    @isset($countries)
                                        @foreach ($countries as $id => $name)
                                            <option value="{{ $id }}" {{ request('country_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                             <div>
                                <x-input-label for="state_id_select" :value="__('Estado')" />
                                <select id="state_id_select" name="state_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">{{ __('Seleccione un país primero') }}</option>
                                    @php $selectedStateId = request('state_id'); @endphp
                                </select>
                            </div>
                             <div class="flex items-end">
                                <x-primary-button>
                                    {{ __('Buscar') }}
                                </x-primary-button>
                             </div>
                        </div>
                    </form>
                </div>
            </div>

             {{-- Lista de Selección y Botón Exportar --}}
             <div id="selection-section" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Productos Seleccionados (<span id="selected-count">0</span>)</h3>
                        <form id="export-form" action="{{ route('search.export') }}" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="selected_ids" id="selected_ids_input">
                            <x-primary-button type="submit" id="export-button" disabled class="bg-green-600 hover:bg-green-500 active:bg-green-700 focus:ring-green-500">
                                Exportar Selección (.xlsx)
                            </x-primary-button>
                        </form>
                    </div>
                    <ul id="selected-products-list" class="list-disc pl-5 space-y-1 max-h-40 overflow-y-auto"></ul>
                </div>
            </div>

            {{-- SECCIÓN DE RESULTADOS --}}
            @isset($products)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">Resultados de la Búsqueda</h3>
                        @if($products->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                     <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Código</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                            @unless (Auth::user()->hasRole('Cliente'))
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio</th>
                                            @endunless
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                                            @unless (Auth::user()->hasRole('Cliente'))
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proveedor</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ubicación</th>
                                            @endunless
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agregar</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($products as $product)
                                            <tr id="product-row-{{ $product->id }}">
                                                 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
                                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->code ?? 'N/A' }}</td>
                                                 <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $product->description }}">{{ $product->description ?? 'N/A' }}</td>
                                                 @unless (Auth::user()->hasRole('Cliente'))
                                                     <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->price ? '$' . number_format($product->price, 2) : 'N/A' }}</td>
                                                 @endunless
                                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->category->name ?? 'N/A' }}</td>
                                                 @unless (Auth::user()->hasRole('Cliente'))
                                                     <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->supplier->name ?? 'N/A' }}</td>
                                                     <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                         {{ $product->supplier->state->name ?? '' }}{{ $product->supplier->state && $product->supplier->country ? ', ' : '' }}{{ $product->supplier->country->name ?? '' }}
                                                     </td>
                                                 @endunless
                                                 <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                     <button type="button" class="add-to-selection-btn text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                             data-product-id="{{ $product->id }}"
                                                             data-product-name="{{ $product->name }}">
                                                         Agregar
                                                     </button>
                                                 </td>
                                                 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                     <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                         Ver Detalles
                                                     </a>
                                                 </td>
                                             </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $products->appends(request()->query())->links() }}
                            </div>
                        @else
                             <table class="min-w-full"><tbody><tr>
                                @php
                                    $colspan = 6;
                                    if (Auth::check() && !Auth::user()->hasRole('Cliente')) { $colspan += 3; }
                                @endphp
                                <td colspan="{{ $colspan }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    No se encontraron productos que coincidan con los criterios de búsqueda.
                                </td>
                            </tr></tbody></table>
                        @endif
                    </div>
                </div>
            @else
                 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                         <p class="text-gray-500 dark:text-gray-400">Utilice el formulario de arriba para buscar productos.</p>
                    </div>
                 </div>
            @endisset
            {{-- FIN SECCIÓN DE RESULTADOS --}}

        </div>
    </div>

    {{-- Scripts --}}
    @push('scripts')
    <script>
        // Asegurarse que el script se ejecute después de que el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[DEBUG] DOMContentLoaded Event Fired');
            // Pequeña espera para asegurar que otros scripts (como Alpine/jQuery de Vite) se inicialicen
            setTimeout(function() {
                if (typeof jQuery === 'undefined') {
                    console.error('[DEBUG] jQuery sigue SIN estar definido después de la espera!');
                    return;
                }
                console.log('[DEBUG] jQuery detectado después de espera. Inicializando scripts...');
                initializePageScripts(jQuery); // Ejecutar la lógica principal
            }, 100); // Esperar 100ms
        });

        function initializePageScripts($) {
            console.log('[DEBUG] InitializePageScripts ejecutado.');

            // --- Autocompletado ---
            try {
                const productNameInput = $('#product_name');
                const suggestionsDiv = $('#product_suggestions');
                let debounceTimerAutocomplete;
                if(productNameInput.length === 0) console.error("[DEBUG] Autocomplete: Input #product_name NO encontrado");
                if(suggestionsDiv.length === 0) console.error("[DEBUG] Autocomplete: Div #product_suggestions NO encontrado");

                if(productNameInput.length > 0) {
                    productNameInput.off('keyup').on('keyup', function() { // Usar .off().on() para evitar listeners duplicados
                        console.log('[DEBUG] Autocomplete: Keyup en product_name');
                        clearTimeout(debounceTimerAutocomplete); let query = $(this).val();
                        if (query.length >= 2) {
                            debounceTimerAutocomplete = setTimeout(function() {
                                console.log('[DEBUG] Autocomplete: Enviando AJAX para:', query);
                                $.ajax({
                                    url: "{{ route('search.autocomplete') }}", type: "GET", data: { 'term': query },
                                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    success: function(data) {
                                        console.log('[DEBUG] Autocomplete: Respuesta:', data);
                                        suggestionsDiv.html('');
                                        if (data && data.length > 0) {
                                            suggestionsDiv.removeClass('hidden');
                                            $.each(data, function(key, value) { const escapedValue = $('<div>').text(value).html(); suggestionsDiv.append('<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer suggestion-item">' + escapedValue + '</div>'); });
                                        } else { suggestionsDiv.addClass('hidden'); }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) { console.error("[DEBUG] Autocomplete: Error AJAX:", textStatus, errorThrown, jqXHR.responseText); suggestionsDiv.addClass('hidden'); }
                                });
                            }, 300);
                        } else { suggestionsDiv.html(''); suggestionsDiv.addClass('hidden'); }
                    });
                     console.log('[DEBUG] Autocomplete: Listener keyup añadido.');
                }
                // Asegurarse que los listeners de clic no se dupliquen
                $(document).off('click', '.suggestion-item').on('click', '.suggestion-item', function() { productNameInput.val($(this).text()); suggestionsDiv.html(''); suggestionsDiv.addClass('hidden'); });
                $(document).off('click.hideSuggestions').on('click.hideSuggestions', function(e) { if (!$(e.target).closest('#product_name, #product_suggestions').length) { suggestionsDiv.addClass('hidden'); } });
                 console.log('[DEBUG] Autocomplete: Listeners de clic añadidos/revisados.');

            } catch(e) { console.error("[DEBUG] Error inicializando Autocomplete:", e); }

            // --- Selección Persistente ---
             try {
                const selectionKey = 'selectedProductsDistrimeca';
                let selectedProducts = JSON.parse(sessionStorage.getItem(selectionKey)) || {};
                const selectionSection = $('#selection-section');
                const selectedList = $('#selected-products-list');
                const selectedCountSpan = $('#selected-count');
                const exportButton = $('#export-button');
                const exportForm = $('#export-form');
                const selectedIdsInput = $('#selected_ids_input');
                if(selectionSection.length === 0 || selectedList.length === 0 || exportButton.length === 0 || exportForm.length === 0 || selectedIdsInput.length === 0) {
                     console.warn("[DEBUG] Selección: Uno o más elementos no encontrados.");
                } else {
                    console.log('[DEBUG] Selección: Elementos encontrados. Añadiendo listeners.');
                    function updateSelectionUI() {
                         console.log('[DEBUG] Selección: updateSelectionUI ejecutado.');
                         selectedList.html(''); let count = 0; const selectedIds = [];
                         for (const id in selectedProducts) { if (selectedProducts.hasOwnProperty(id)) { const name = selectedProducts[id]; const escapedName = $('<div>').text(name).html(); selectedList.append('<li id="selected-item-'+ id +'" class="text-sm"> • ' + escapedName + ' <button type="button" class="remove-from-selection-btn text-red-500 hover:text-red-700 ml-2 text-xs" data-product-id="'+ id +'">(Quitar)</button></li>'); count++; selectedIds.push(id); } }
                         selectedCountSpan.text(count);
                         $('.add-to-selection-btn').each(function() { const button = $(this); const productId = button.data('product-id'); if (selectedProducts[productId]) { button.text('Agregado').prop('disabled', true).removeClass('text-green-600 dark:text-green-400').addClass('text-gray-400 dark:text-gray-500 cursor-not-allowed'); $('#product-row-' + productId).addClass('opacity-50'); } else { button.text('Agregar').prop('disabled', false).addClass('text-green-600 dark:text-green-400').removeClass('text-gray-400 dark:text-gray-500 cursor-not-allowed'); $('#product-row-' + productId).removeClass('opacity-50'); } });
                         if (count > 0) { selectionSection.removeClass('hidden'); exportButton.prop('disabled', false); exportForm.removeClass('hidden'); selectedIdsInput.val(selectedIds.join(',')); } else { selectionSection.addClass('hidden'); exportButton.prop('disabled', true); exportForm.addClass('hidden'); selectedIdsInput.val(''); }
                         console.log('[DEBUG] Selección: UI actualizada. Count:', count);
                    }
                    // Usar delegación y asegurar que no se dupliquen listeners
                    $(document).off('click', '.add-to-selection-btn:not(:disabled)').on('click', '.add-to-selection-btn:not(:disabled)', function() { console.log('[DEBUG] Selección: Click en Agregar'); const button = $(this); const productId = button.data('product-id'); const productName = button.data('product-name'); if (!selectedProducts[productId]) { selectedProducts[productId] = productName; sessionStorage.setItem(selectionKey, JSON.stringify(selectedProducts)); updateSelectionUI(); } });
                    $(document).off('click', '.remove-from-selection-btn').on('click', '.remove-from-selection-btn', function() { console.log('[DEBUG] Selección: Click en Quitar'); const productId = $(this).data('product-id'); if (selectedProducts[productId]) { delete selectedProducts[productId]; sessionStorage.setItem(selectionKey, JSON.stringify(selectedProducts)); updateSelectionUI(); } });
                    updateSelectionUI(); // Llamada inicial
                     console.log('[DEBUG] Selección: Listeners añadidos y UI actualizada.');
                }
            } catch(e) { console.error("[DEBUG] Error inicializando Selección:", e); }


            // --- Estados Dinámicos ---
             try {
                console.log('[DEBUG] Estados: Inicializando...');
                const countrySelect = $('#country_id_select');
                const stateSelect = $('#state_id_select');
                const selectedStateId = '{{ $selectedStateId ?? '' }}';
                if(countrySelect.length === 0 || stateSelect.length === 0) {
                     console.warn("[DEBUG] Estados: Selects no encontrados.");
                } else {
                    console.log('[DEBUG] Estados: Selects encontrados.');
                    function loadStates(countryId, stateToSelect) {
                        console.log('[DEBUG] Estados: loadStates llamado con countryId:', countryId, 'stateToSelect:', stateToSelect);
                        stateSelect.prop('disabled', true).html('<option value="">Cargando...</option>');
                        if (!countryId) { console.log('[DEBUG] Estados: No hay countryId.'); stateSelect.prop('disabled', false).html('<option value="">Seleccione país</option>'); return; }
                        const apiUrl = '/api/states-by-country/' + countryId;
                        console.log('[DEBUG] Estados: Haciendo AJAX a:', apiUrl);
                        $.ajax({
                            url: apiUrl, type: 'GET', dataType: 'json',
                            success: function(data) {
                                console.log('[DEBUG] Estados: Respuesta AJAX:', data);
                                stateSelect.html('<option value="">Todos</option>'); // Cambiado a "Todos"
                                if (data && Object.keys(data).length > 0) {
                                    $.each(data, function(id, name) { stateSelect.append($('<option>', { value: id, text: name })); });
                                    if (stateToSelect) { console.log('[DEBUG] Estados: Intentando preseleccionar:', stateToSelect); stateSelect.val(stateToSelect); if(stateSelect.val() != stateToSelect) { console.warn('[DEBUG] Estados: No se pudo preseleccionar:', stateToSelect); } } // Usar != porque val() devuelve string
                                } else { stateSelect.append('<option value="" disabled>No hay estados</option>'); }
                                stateSelect.prop('disabled', false);
                            },
                            error: function(jqXHR, textStatus, errorThrown) { console.error("[DEBUG] Estados: Error AJAX:", textStatus, errorThrown, jqXHR.responseText); stateSelect.prop('disabled', false).html('<option value="">Error</option>'); }
                        });
                    }
                    // Usar .off().on() para evitar listeners duplicados
                    countrySelect.off('change').on('change', function() { console.log('[DEBUG] Estados: País cambiado a:', $(this).val()); loadStates($(this).val(), null); });
                    const initialCountryId = countrySelect.val();
                    console.log('[DEBUG] Estados: Carga inicial - País ID:', initialCountryId);
                    if (initialCountryId) { loadStates(initialCountryId, selectedStateId); }
                    else { stateSelect.html('<option value="">Seleccione país</option>'); }
                     console.log('[DEBUG] Estados: Listeners añadidos y estado inicial cargado.');
                }
            } catch(e) { console.error("[DEBUG] Error inicializando Estados Dinámicos:", e); }

            console.log('[DEBUG] Scripts inicializados completamente.');

        } // Fin initializePageScripts
    </script>
    @endpush

</x-app-layout>
