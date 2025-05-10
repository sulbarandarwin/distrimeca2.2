<x-app-layout>
    @section('title', __('Buscar Productos'))

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Buscar y Seleccionar Productos') }}
            </h2>
            {{-- BOTÓN AGREGAR NUEVO PRODUCTO (Visible según permiso) --}}
            @can('create products')
                <div class="mt-3 md:mt-0">
                    <a href="{{ route('admin.products.create') }}">
                        <x-primary-button>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            {{ __('Agregar Producto') }}
                        </x-primary-button>
                    </a>
                </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes Flash y Errores --}}
            @if (session('success')) <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Éxito!</strong> <span class="block sm:inline">{{ session('success') }}</span></div> @endif
            @if (session('error')) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error!</strong> <span class="block sm:inline">{{ session('error') }}</span></div> @endif
            @if ($errors->any()) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error de Validación!</strong> <ul class="mt-1 list-disc list-inside text-sm text-red-600"> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif
            {{-- Fin Mensajes --}}

            {{-- Formulario de Búsqueda --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php $filters = $validated ?? []; @endphp
                    <form method="GET" action="{{ route('search.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

                            {{-- Nombre Producto --}}
                            <div class="relative">
                                <x-input-label for="product_name" :value="__('Nombre Producto')" />
                                <x-text-input id="product_name" class="block mt-1 w-full" type="text" name="product_name"
                                              :value="old('product_name', $filters['product_name'] ?? '')"
                                              placeholder="Ej: Martillo..." autocomplete="off" />
                                <div id="product_suggestions" class="absolute z-50 w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md mt-1 shadow-lg hidden max-h-60 overflow-y-auto"></div>
                            </div>

                             {{-- Proveedor --}}
                             @unlessrole('Proveedor') {{-- Rol Proveedor no ve este filtro, ya que busca en los suyos por defecto --}}
                             <div class="relative">
                                 <x-input-label for="supplier_name_input" :value="__('Proveedor')" /> {{-- Cambiado ID para evitar conflicto si hubiera un select con mismo ID --}}
                                 <input type="text" id="supplier_name_input" name="supplier_name" {{-- Campo para buscar por nombre --}}
                                        class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                        value="{{ old('supplier_name', $filters['supplier_name'] ?? '') }}"
                                        placeholder="Escribe para buscar proveedor..." autocomplete="off">
                                 <input type="hidden" id="supplier_id_hidden" name="supplier_id" value="{{ old('supplier_id', $filters['supplier_id'] ?? '') }}"> {{-- Campo oculto para el ID del proveedor seleccionado --}}
                                 <div id="supplier_suggestions" class="absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md mt-1 shadow-lg hidden max-h-60 overflow-y-auto"></div>
                             </div>
                             @endunlessrole

                            {{-- País --}}
                            <div>
                                <x-input-label for="country_id_select" :value="__('País (Proveedor)')" />
                                <select id="country_id_select" name="country_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                     <option value="">{{ __('Todos') }}</option>
                                     @foreach ($countries as $id => $name) <option value="{{ $id }}" {{ (old('country_id', $filters['country_id'] ?? '') == $id) ? 'selected' : '' }}>{{ $name }}</option> @endforeach
                                </select>
                            </div>
                             {{-- Estado --}}
                            <div>
                                <x-input-label for="state_id_select" :value="__('Estado (Proveedor)')" />
                                <select id="state_id_select" name="state_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" @if($states->isEmpty() && !(old('country_id', $filters['country_id'] ?? false))) disabled @endif>
                                    <option value="">{{ __('Todos') }}</option>
                                     @foreach($states as $id => $name) <option value="{{ $id }}" {{ (old('state_id', $filters['state_id'] ?? '') == $id) ? 'selected' : '' }}>{{ $name }}</option> @endforeach
                                </select>
                            </div>

                             {{-- Botón Buscar --}}
                             <div class="flex items-end {{ Auth::user()->hasRole('Proveedor') ? 'md:col-start-3 lg:col-start-4' : 'lg:col-start-4 xl:col-start-4' }}">
                                <x-primary-button class="w-full justify-center">
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
                     <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium">Productos Seleccionados (<span id="selected-count">0</span>)</h3>
                            <ul id="selected-products-list" class="list-disc pl-5 space-y-1 max-h-40 overflow-y-auto mt-2"></ul>
                        </div>
                         @can('exportar products') {{-- O el permiso que hayas definido para exportar --}}
                         <form id="export-form" action="{{ route('search.export') }}" method="POST" class="mt-4 md:mt-0">
                             @csrf
                             {{-- Contenedor para los inputs hidden de los productos seleccionados --}}
                             <div id="selected-products-input-container-for-export">
                                 {{-- Aquí se añadirán los <input type="hidden" name="selected_products[]" value="ID"> --}}
                             </div>
                             <x-secondary-button type="submit" id="export-button" disabled class="bg-green-600 hover:bg-green-500 active:bg-green-700 focus:ring-green-500 text-white dark:text-white">
                                 Exportar Selección (.xlsx)
                             </x-secondary-button>
                         </form>
                         @endcan
                     </div>
                 </div>
             </div>

            {{-- SECCIÓN DE RESULTADOS --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Resultados de la Búsqueda</h3>
                    <div id="search-results-table-container" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    {{-- TUS CABECERAS --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Código</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                    @unless (Auth::user()->hasRole('Cliente')) <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio</th> @endunless
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
                                @forelse ($products as $product)
                                    <tr id="product-row-{{ $product->id }}" class="odd:bg-white dark:odd:bg-gray-800 even:bg-gray-50 dark:even:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        {{-- TUS DATOS DE PRODUCTO --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $product->code ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $product->description }}">{{ Str::limit($product->description, 50) }}</td>
                                        @unless (Auth::user()->hasRole('Cliente'))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ ($settings['currency_symbol'] ?? '$') . number_format($product->price ?? 0, 2, ',', '.') }}
                                            </td>
                                        @endunless
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $product->category->name ?? 'Sin Categoría' }}</td>
                                        @unless (Auth::user()->hasRole('Cliente'))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $product->supplier->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $product->supplier->state->name ?? '' }}{{ (isset($product->supplier->state) && isset($product->supplier->country)) ? ', ' : '' }}{{ $product->supplier->country->name ?? '' }}
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
                                            {{-- Asumiendo que tienes permiso 'view products' para ver detalles, ProductPolicy lo manejará --}}
                                            <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                Ver Detalles
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->hasRole('Cliente') ? 5 : 8 }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                            @if(!empty($validated))
                                                No se encontraron productos que coincidan con los criterios de búsqueda.
                                            @else
                                                Utilice el formulario de arriba para buscar productos.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                             @if ($products->hasPages())
                             <tfoot>
                                 <tr>
                                     <td colspan="{{ Auth::user()->hasRole('Cliente') ? 5 : 8 }}" class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                          {{ $products->links() }}
                                     </td>
                                 </tr>
                             </tfoot>
                             @endif
                        </table>
                    </div>
                </div>
            </div>
            {{-- FIN SECCIÓN DE RESULTADOS --}}
        </div>
    </div>

    @push('scripts')
    <script>
    // Mantener el IIFE y la espera de dependencias (jQuery) como en tu archivo original
    (function() {
        function initializePageScripts($) {
            console.log('[DEBUG] jQuery listo. Inicializando scripts de search.index...');

            // --- Autocompletado Nombre Producto ---
            const productNameInput = $('#product_name');
            const suggestionsDiv = $('#product_suggestions');
            let debounceTimerAutocomplete;

            if (productNameInput.length > 0 && suggestionsDiv.length > 0) {
                productNameInput.on('keyup', function() {
                    clearTimeout(debounceTimerAutocomplete);
                    let query = $(this).val();
                    if (query.length >= 2) {
                        debounceTimerAutocomplete = setTimeout(function() {
                            $.ajax({
                                url: "{{ route('search.autocomplete') }}",
                                type: "GET",
                                data: { 'term': query },
                                dataType: 'json',
                                success: function(data) {
                                    suggestionsDiv.html('');
                                    if (data && Array.isArray(data) && data.length > 0) {
                                        suggestionsDiv.removeClass('hidden');
                                        $.each(data, function(key, value) {
                                            const escapedValue = $('<div>').text(value.name).html();
                                            suggestionsDiv.append('<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer suggestion-item">' + escapedValue + '</div>');
                                        });
                                    } else {
                                        suggestionsDiv.addClass('hidden');
                                    }
                                },
                                error: function() {
                                    suggestionsDiv.addClass('hidden');
                                }
                            });
                        }, 300);
                    } else {
                        suggestionsDiv.html('').addClass('hidden');
                    }
                });
                $(document).on('click', '.suggestion-item', function() {
                    productNameInput.val($(this).text());
                    suggestionsDiv.html('').addClass('hidden');
                });
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#product_name, #product_suggestions').length) {
                        suggestionsDiv.addClass('hidden');
                    }
                });
            }

            // --- Autocompletado Proveedor ---
            const supplierNameField = $('#supplier_name_input'); // ID del campo de texto para nombre de proveedor
            const supplierIdHiddenField = $('#supplier_id_hidden'); // ID del campo oculto para el ID del proveedor
            const supplierSuggestionsDiv = $('#supplier_suggestions');
            let debounceTimerSupplier;

            if (supplierNameField.length > 0 && supplierSuggestionsDiv.length > 0) {
                supplierNameField.on('keyup', function() {
                    clearTimeout(debounceTimerSupplier);
                    let query = $(this).val();
                    supplierIdHiddenField.val(''); // Limpiar ID oculto si se escribe manualmente
                    if (query.length >= 2) {
                        debounceTimerSupplier = setTimeout(function() {
                            $.ajax({
                                url: "{{ route('search.autocomplete-supplier') }}",
                                type: "GET",
                                data: { 'term': query },
                                dataType: 'json',
                                success: function(data) {
                                    supplierSuggestionsDiv.html('');
                                    if (data && Array.isArray(data) && data.length > 0) {
                                        supplierSuggestionsDiv.removeClass('hidden');
                                        $.each(data, function(key, value) {
                                            const escapedValue = $('<div>').text(value.name).html();
                                            supplierSuggestionsDiv.append(`<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer supplier-suggestion-item" data-supplier-id="${value.id}" data-supplier-name="${value.name}">${escapedValue}</div>`);
                                        });
                                    } else {
                                        supplierSuggestionsDiv.addClass('hidden');
                                    }
                                },
                                error: function() {
                                    supplierSuggestionsDiv.addClass('hidden');
                                }
                            });
                        }, 300);
                    } else {
                        supplierSuggestionsDiv.html('').addClass('hidden');
                    }
                });
                $(document).on('click', '.supplier-suggestion-item', function() {
                    supplierNameField.val($(this).data('supplier-name'));
                    supplierIdHiddenField.val($(this).data('supplier-id')); // Establecer el ID en el campo oculto
                    supplierSuggestionsDiv.html('').addClass('hidden');
                });
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#supplier_name_input, #supplier_suggestions').length) {
                        supplierSuggestionsDiv.addClass('hidden');
                    }
                });
            }

            // --- Selección de Productos para Exportar ---
            const selectionKey = 'selectedProductsDistrimeca';
            let selectedProducts = JSON.parse(sessionStorage.getItem(selectionKey)) || {};
            const selectionSection = $('#selection-section');
            const selectedListUL = $('#selected-products-list');
            const selectedCountSpan = $('#selected-count');
            const exportButton = $('#export-button');
            const exportForm = $('#export-form');
            // const selectedIdsInputForExport = $('#selected_ids_input'); // Ya no se usa un solo input
            const inputContainerForExport = $('#selected-products-input-container-for-export'); // Contenedor para múltiples inputs
            const resultsTableContainer = $('#search-results-table-container');

            function updateSelectionUIAndExportInputs() {
                selectedListUL.html('');
                inputContainerForExport.html(''); // Limpiar inputs anteriores
                let count = 0;

                for (const id in selectedProducts) {
                    if (selectedProducts.hasOwnProperty(id)) {
                        const name = selectedProducts[id];
                        const escapedName = $('<div>').text(name).html();
                        selectedListUL.append(`<li id="selected-item-${id}" class="text-sm"> • ${escapedName} <button type="button" class="remove-from-selection-btn text-red-500 hover:text-red-700 ml-2 text-xs" data-product-id="${id}">(Quitar)</button></li>`);
                        count++;
                        // Añadir un input hidden por cada producto seleccionado
                        inputContainerForExport.append(`<input type="hidden" name="selected_products[]" value="${id}">`);
                    }
                }
                selectedCountSpan.text(count);

                resultsTableContainer.find('.add-to-selection-btn').each(function() {
                    const button = $(this);
                    const productId = String(button.data('product-id'));
                    if (selectedProducts[productId]) {
                        button.text('Agregado').prop('disabled', true).removeClass('text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300').addClass('text-gray-400 dark:text-gray-500 cursor-not-allowed');
                        $('#product-row-' + productId).addClass('opacity-50');
                    } else {
                        button.text('Agregar').prop('disabled', false).addClass('text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300').removeClass('text-gray-400 dark:text-gray-500 cursor-not-allowed');
                        $('#product-row-' + productId).removeClass('opacity-50');
                    }
                });

                if (count > 0) {
                    selectionSection.removeClass('hidden');
                    exportButton.prop('disabled', false);
                    exportForm.removeClass('hidden'); // Asegurarse que el form sea visible
                } else {
                    selectionSection.addClass('hidden');
                    exportButton.prop('disabled', true);
                    exportForm.addClass('hidden');
                }
            }

            resultsTableContainer.on('click', '.add-to-selection-btn:not(:disabled)', function() {
                const button = $(this);
                const productId = String(button.data('product-id'));
                const productName = button.data('product-name');
                if (!selectedProducts[productId] && productId && productName) {
                    selectedProducts[productId] = productName;
                    sessionStorage.setItem(selectionKey, JSON.stringify(selectedProducts));
                    updateSelectionUIAndExportInputs();
                }
            });

            selectionSection.on('click', '.remove-from-selection-btn', function() {
                const productId = String($(this).data('product-id'));
                if (selectedProducts[productId]) {
                    delete selectedProducts[productId];
                    sessionStorage.setItem(selectionKey, JSON.stringify(selectedProducts));
                    updateSelectionUIAndExportInputs();
                }
            });
            updateSelectionUIAndExportInputs(); // Llamada inicial

            // --- Estados Dinámicos ---
            const countrySelect = $('#country_id_select');
            const stateSelect = $('#state_id_select');
            const initialSelectedStateId = '{{ old('state_id', $filters['state_id'] ?? '') }}';

            function loadStates(countryId, stateToSelect) {
                stateSelect.prop('disabled', true).html('<option value="">Cargando...</option>');
                if (!countryId) {
                    stateSelect.prop('disabled', false).html('<option value="">Todos los Estados</option>');
                    return;
                }
                fetch(`/api/states-by-country/${countryId}`)
                    .then(response => response.json())
                    .then(data => {
                        stateSelect.html('<option value="">Todos los Estados</option>');
                        for (const id in data) {
                            const option = $('<option></option>').val(id).text(data[id]);
                            if (String(id) === String(stateToSelect)) {
                                option.prop('selected', true);
                            }
                            stateSelect.append(option);
                        }
                        stateSelect.prop('disabled', false);
                    }).catch(error => {
                        console.error('Error loading states:', error);
                        stateSelect.prop('disabled', false).html('<option value="">Error al cargar</option>');
                    });
            }
            if (countrySelect.length > 0) {
                countrySelect.on('change', function() { loadStates($(this).val(), null); });
                if(countrySelect.val()){ // Cargar estados si ya hay un país seleccionado
                    loadStates(countrySelect.val(), initialSelectedStateId);
                } else { // Si no hay país, asegurar que el select de estados esté vacío y habilitado
                    stateSelect.prop('disabled', true).html('<option value="">Todos los Estados</option>');
                }
            }
        } // Fin initializePageScripts

        function waitForJqueryAndMaybeChoices() {
            if (window.jQuery) { // Solo esperar jQuery, Choices.js no se está usando aquí
                initializePageScripts(window.jQuery);
            } else {
                setTimeout(waitForJqueryAndMaybeChoices, 100);
            }
        }
        document.addEventListener('DOMContentLoaded', waitForJqueryAndMaybeChoices);
    })(); // Fin del IIFE
    </script>
    @endpush
</x-app-layout>