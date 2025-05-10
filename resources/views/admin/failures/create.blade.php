<x-app-layout>
    @section('title', __('Registrar Nueva Falla de Producto'))
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nueva Falla de Producto') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('¡Ups! Algo salió mal.') }}</strong>
                    <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.failures.store') }}">
                    @csrf
                    <div class="p-6 space-y-6">
                        <div>
                            <x-input-label for="product_id" value="Producto en Falla *" />
                            <select name="product_id" id="product_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700">
                                <option value="">{{ __('Seleccione un producto...') }}</option>
                                @foreach($products as $id => $name)
                                    <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="failure_date" value="Fecha y Hora de la Falla *" />
                            <x-text-input type="datetime-local" name="failure_date" id="failure_date" value="{{ old('failure_date', now()->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('failure_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="description" value="Descripción de la Falla (Opcional)" />
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-4 bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t dark:border-gray-700 sm:rounded-b-lg">
                        <a href="{{ route('admin.failures.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                        <x-primary-button>
                            {{ __('Guardar Registro de Falla') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>