<x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Editar Producto: ') }} {{ $product->name }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        {{-- Mostrar errores de validación --}}
                        <x-auth-session-status class="mb-4" :status="session('status')" />
                        @if ($errors->any())
                            <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400">
                                {{ __('¡Ups! Algo salió mal.') }}
                            </div>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <form method="POST" action="{{ route('admin.products.update', $product) }}"> {{-- Apunta a la ruta update --}}
                            @csrf
                            @method('PUT') {{-- Método HTTP para actualizar --}}

                            <div class="mt-4">
                                <x-input-label for="name" :value="__('Nombre del Producto')" /> <span class="text-red-500">*</span>
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $product->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label for="code" :value="__('Código (SKU)')" />
                                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $product->code)" />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label for="description" :value="__('Descripción')" />
                                <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $product->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label for="price" :value="__('Precio ($)')" />
                                <x-text-input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" :value="old('price', $product->price)" />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="supplier_id" :value="__('Proveedor')" /> <span class="text-red-500">*</span>
                                <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">{{ __('Seleccione un proveedor...') }}</option>
                                    @foreach ($suppliers as $id => $name)
                                        {{-- Usamos old() para mantener la selección si falla la validación, si no, usamos el ID actual del producto --}}
                                        <option value="{{ $id }}" {{ old('supplier_id', $product->supplier_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                            </div>

                             <div class="mt-4">
                                <x-input-label for="category_id" :value="__('Categoría (Opcional)')" />
                                <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">{{ __('Sin categoría') }}</option>
                                    @foreach ($categories as $id => $name)
                                        <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>


                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.products.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                    {{ __('Cancelar') }}
                                </a>

                                <x-primary-button>
                                    {{ __('Actualizar Producto') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
    