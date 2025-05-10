<x-app-layout>
    @section('title', __('Buscador IA'))

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Búsqueda Inteligente de Productos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form id="ai-search-form" action="{{ route('ai.search.products') }}" method="POST">
                        @csrf
                        <label for="ai_query" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Describe el producto que buscas:
                        </label>
                        <div class="flex items-center border-b border-indigo-500 dark:border-indigo-600 py-2 mb-6">
                            <input class="appearance-none bg-transparent border-none w-full text-gray-700 dark:text-gray-300 mr-3 py-1 px-2 leading-tight focus:outline-none focus:ring-0"
                                   type="text"
                                   name="query"
                                   id="ai_query"
                                   placeholder="Ej: 'herramienta para apretar tuercas cromada', 'pintura azul brillante para exteriores'..."
                                   aria-label="Consulta de búsqueda IA"
                                   required>
                            <button class="flex-shrink-0 bg-indigo-600 hover:bg-indigo-700 border-indigo-600 hover:border-indigo-700 text-sm border-4 text-white py-1 px-2 rounded transition duration-150 ease-in-out" type="submit">
                                <svg class="w-5 h-5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                Buscar
                            </button>
                        </div>
                    </form>

                    <div id="ai-search-spinner" class="hidden text-center mt-6">
                        <svg class="animate-spin mx-auto h-8 w-8 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Buscando productos...</p>
                    </div>

                    <div id="ai-search-error" class="hidden mt-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-gray-900 dark:text-red-400 border border-red-300 dark:border-red-600" role="alert">
                        <span class="font-bold">Error:</span> <span id="ai-error-message"></span>
                    </div>

                    <div id="ai-search-results" class="mt-4">
                        {{-- Los resultados se cargarán aquí --}}
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('ai-search-form');
        const resultsDiv = document.getElementById('ai-search-results');
        const spinner = document.getElementById('ai-search-spinner');
        const errorDiv = document.getElementById('ai-search-error');
        const errorMessageSpan = document.getElementById('ai-error-message');
        const initialMessage = '<p class="text-center text-gray-500 dark:text-gray-400 italic">Ingresa tu consulta para buscar productos...</p>';

        resultsDiv.innerHTML = initialMessage;

        if(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                resultsDiv.innerHTML = '';
                errorDiv.classList.add('hidden');
                spinner.classList.remove('hidden');

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => {
                            throw new Error(errData.error || `Error HTTP ${response.status}`);
                        }).catch(() => {
                            throw new Error(`Error HTTP ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    spinner.classList.add('hidden');
                    if (data.html) {
                        resultsDiv.innerHTML = data.html;
                        if (data.html.trim() === '' || data.html.includes('No se encontraron resultados')) { // Ajuste para verificar si hay resultados
                            resultsDiv.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400">No se encontraron resultados relevantes para tu búsqueda.</p>';
                        }
                    } else if (data.error){
                         errorMessageSpan.textContent = data.error;
                         errorDiv.classList.remove('hidden');
                         resultsDiv.innerHTML = initialMessage;
                    } else {
                         resultsDiv.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400">No se obtuvieron resultados válidos.</p>';
                    }
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    console.error('Error en la búsqueda IA:', error);
                    errorMessageSpan.textContent = error.message || 'Ocurrió un error inesperado.';
                    errorDiv.classList.remove('hidden');
                     resultsDiv.innerHTML = initialMessage;
                });
            });
        }
    });
    </script>
    @endpush
</x-app-layout>