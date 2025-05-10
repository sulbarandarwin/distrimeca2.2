<x-app-layout>
    {{-- Slot para el encabezado de la página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crear Nueva Invitación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Mostrar Mensaje de Éxito (si existe en la sesión) --}}
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Éxito!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Mostrar Errores de Validación --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Error de Validación!</strong>
                            <ul class="mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Mostrar Mensaje de Error General (si existe en la sesión, ej. del try-catch) --}}
                     @if (session('error'))
                          <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                              <strong class="font-bold">¡Error!</strong>
                              <span class="block sm:inline">{{ session('error') }}</span>
                          </div>
                      @endif

                    <form method="POST" action="{{ route('invitations.store') }}">
                        @csrf {{-- Token CSRF obligatorio --}}

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email del Invitado') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Asignar Rol') }}</label>
                            <select name="role_id" id="role_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>{{ __('Seleccionar Rol...') }}</option>
                                {{-- $roles es pasada desde InvitationController@create --}}
                                @foreach($roles as $role)
                                    {{-- Añadimos data-role-name para usarlo en JS --}}
                                    <option value="{{ $role->id }}" data-role-name="{{ $role->name }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }} {{-- Asume que el modelo Role tiene un atributo 'name' --}}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                 <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Este div se mostrará/ocultará con JavaScript --}}
                        <div class="mb-4" id="supplier-field" style="display: none;"> {{-- Oculto por defecto --}}
                            <label for="associated_supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Asociar Proveedor (si el rol es Proveedor)') }}</label>
                            <select name="associated_supplier_id" id="associated_supplier_id" {{-- No es required globalmente, se ajusta con JS --}}
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                <option value="" selected>{{ __('Seleccionar Proveedor...') }}</option> {{-- Permitir no seleccionar si no es obligatorio --}}
                                {{-- $suppliers es pasada desde InvitationController@create --}}
                                @if(isset($suppliers)) {{-- Verifica si $suppliers existe --}}
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('associated_supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }} {{-- Asume que el modelo Supplier tiene un atributo 'name' --}}
                                        </option>
                                    @endforeach
                                @endif {{-- <--- CIERRE DEL @if CORREGIDO --}}
                            </select> {{-- Cierre del select --}}
                             @error('associated_supplier_id')
                                 <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                             @enderror
                        </div> {{-- Cierre del div supplier-field --}}

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Enviar Invitación') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Script para mostrar/ocultar campo Proveedor --}}
    {{-- Usamos @push para añadirlo al stack 'scripts' definido en el layout app.blade.php --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role_id');
            const supplierField = document.getElementById('supplier-field');
            const supplierSelect = document.getElementById('associated_supplier_id');
            // IMPORTANTE: Ajusta 'Proveedor' si el nombre exacto de tu rol es diferente
            const supplierRoleName = 'Proveedor';

            function toggleSupplierField() {
                const selectedOption = roleSelect.options[roleSelect.selectedIndex];
                // Comprueba si la opción seleccionada tiene el data attribute con el nombre esperado
                if (selectedOption && selectedOption.dataset.roleName === supplierRoleName) {
                    supplierField.style.display = 'block'; // Muestra el campo
                    supplierSelect.required = true; // Hazlo requerido
                } else {
                    supplierField.style.display = 'none'; // Oculta el campo
                    supplierSelect.required = false; // No es requerido
                    supplierSelect.value = ''; // Limpia cualquier valor previo al ocultar
                }
            }

            // Ejecuta la función al cargar la página (importante si hay errores de validación y 'old' values)
            toggleSupplierField();

            // Añade un listener para ejecutar la función cada vez que cambie el rol seleccionado
            roleSelect.addEventListener('change', toggleSupplierField);
        });
    </script>
    @endpush
</x-app-layout>