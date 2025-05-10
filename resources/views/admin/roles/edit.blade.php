<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Permisos para Rol:') }} <span class="font-bold">{{ $role->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
             {{-- Mensajes Flash --}}
             @if (session('success')) <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Éxito!</strong> <span class="block sm:inline">{{ session('success') }}</span></div> @endif
             @if (session('error')) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error!</strong> <span class="block sm:inline">{{ session('error') }}</span></div> @endif
             @if ($errors->any()) <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong class="font-bold">¡Error de Validación!</strong> <ul class="mt-1 list-disc list-inside text-sm text-red-600"> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif
             {{-- Fin Mensajes Flash --}}

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.roles.update.permissions', $role) }}">
                    @csrf
                    @method('PUT')

                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Selecciona los permisos que deseas asignar al rol <strong class="font-semibold">{{ $role->name }}</strong>. Los permisos no seleccionados serán revocados.
                        </p>

                        {{-- Array asociativo con descripciones para cada permiso (CLAVES EN ESPAÑOL - VERIFICADAS) --}}
                        @php
                            $permissionDescriptions = [
                                // Usuarios 
                                'ver usuarios' => 'Permite ver la lista de usuarios en el panel de administración.', 
                                'gestionar usuarios' => 'Permite crear, editar y eliminar usuarios.', 
                                // Proveedores 
                                'ver proveedores' => 'Permite ver la lista de proveedores.', 
                                'gestionar proveedores' => 'Permite crear, editar y eliminar proveedores.', 
                                 // Productos 
                                'ver productos' => 'Permite ver la lista de productos (filtrada por proveedor si aplica).', 
                                'crear productos' => 'Permite añadir nuevos productos al sistema.', 
                                'editar productos propios' => 'Permite editar únicamente los productos asociados al proveedor del usuario.', 
                                'editar todos los productos' => 'Permite editar cualquier producto de cualquier proveedor.', 
                                'eliminar productos' => 'Permite eliminar productos.', 
                                // Invitaciones 
                                'enviar invitaciones' => 'Permite acceder al formulario y enviar invitaciones a nuevos usuarios.', 
                                 // Import/Export 
                                'importar suppliers' => 'Permite subir archivos Excel para importar proveedores.', 
                                'importar products' => 'Permite subir archivos Excel para importar productos.', 
                                'exportar suppliers' => 'Permite descargar el listado de proveedores en Excel.', 
                                'exportar products' => 'Permite descargar el listado de productos en Excel.', 
                                 // Auditoría 
                                'ver auditoria' => 'Permite ver el registro de actividad del sistema.', 
                                'limpiar auditoria' => 'Permite usar las funciones para borrar registros de auditoría.', 
                                 // Configuración 
                                'gestionar configuracion' => 'Permite acceder y modificar la configuración general de la aplicación.', 
                                 // Roles y Permisos 
                                'manage roles' => 'Permite acceder a esta pantalla para gestionar roles y asignar permisos.', 
                            ];
                        @endphp

                        {{-- Iterar sobre los grupos de permisos --}}
                        @foreach ($permissions->sortKeys() as $group => $permissionList)
                            <fieldset class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <legend class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2 capitalize">{{ ucfirst(str_replace('_', ' ', $group)) }}</legend>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2">
                                    {{-- Iterar sobre los permisos dentro del grupo --}}
                                    @foreach ($permissionList as $permission)
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="permission-{{ $permission->id }}" 
                                                       name="permissions[]" 
                                                       type="checkbox" 
                                                       value="{{ $permission->name }}"
                                                       @if(in_array($permission->name, $rolePermissions)) checked @endif
                                                       class="focus:ring-indigo-500 dark:focus:ring-indigo-600 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded">
                                            </div>
                                            <div class="ms-3 text-sm">
                                                {{-- Añadir el atributo title con la descripción --}}
                                                <label for="permission-{{ $permission->id }}" 
                                                       class="font-medium text-gray-700 dark:text-gray-300 cursor-help" 
                                                       {{-- Usar la descripción del array, con fallback --}}
                                                       title="{{ $permissionDescriptions[$permission->name] ?? 'Descripción no disponible para: ' . $permission->name }}"> 
                                                    {{ ucfirst($permission->name) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach

                    </div>

                    <div class="flex items-center justify-end gap-4 bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t dark:border-gray-700 sm:rounded-b-lg">
                         <a href="{{ route('admin.roles.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                        <x-primary-button>
                            {{ __('Guardar Permisos') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
