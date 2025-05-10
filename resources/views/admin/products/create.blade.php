<x-app-layout>
    @section('title', __('Crear Nuevo Producto'))

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crear Nuevo Producto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.products.store') }}">
                    @csrf
                    <div class="p-6 space-y-6">

                        {{-- Código (Ahora Primero) --}}
                        <div>
                            <x-input-label for="code" :value="__('Código (Opcional)')" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code')" />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        {{-- Nombre (Ahora Segundo) --}}
                        <div>
                            <x-input-label for="name" :value="__('Nombre del Producto')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        {{-- Descripción --}}
                         <div>
                            <x-input-label for="description" :value="__('Descripción (Opcional)')" />
                            <textarea id="description" name="description" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                         {{-- Precio --}}
                        <div>
                            <x-input-label for="price" :value="__('Precio')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price')" />
                            <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </div>

                        {{-- Proveedor (con Choices.js) --}}
                        <div>
                            <x-input-label for="supplier_id" :value="__('Proveedor')" />
                            <select name="supplier_id" id="supplier-select" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                                <option value="">{{ __('Seleccione un proveedor...') }}</option>
                                @foreach($suppliers as $id => $name)
                                    <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('supplier_id')" />
                        </div>

                        {{-- Categoría --}}
                        <div>
                            <x-input-label for="category_id" :value="__('Categoría (Opcional)')" />
                            <select name="category_id" id="category_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                                <option value="">{{ __('Sin categoría') }}</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-4 bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t dark:border-gray-700 sm:rounded-b-lg">
                         <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                        <x-primary-button>
                            {{ __('Guardar Producto') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script para inicializar Choices.js en el select de proveedor --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const supplierElement = document.getElementById('supplier-select');
            if (supplierElement) {
                const choices = new Choices(supplierElement, { /* ... tus opciones ... */ });
            }
        });
    </script>
    @endpush

</x-app-layout>
