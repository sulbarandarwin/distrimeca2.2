<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ Auth::user()->hasRole('Admin') ? route('admin.dashboard') : route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- 1. Admin Dashboard (SOLO Admin) --}}
                    @role('Admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')"> {{ __('Admin Dashboard') }} </x-nav-link>
                    @endrole
                    {{-- 2. Dashboard General (Todos EXCEPTO Admin) --}}
                    @unlessrole('Admin')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"> {{ __('Dashboard') }} </x-nav-link>
                    @endunlessrole

                    {{-- ENLACE BUSCADOR IA (ESCRITORIO) --}}
                    {{-- Puedes protegerlo con un permiso si quieres más adelante: @can('usar buscador ia') --}}
                    <x-nav-link :href="route('ai.search.index')" {{-- La ruta se llamará así --}}
                                :active="request()->routeIs('ai.search.*')">
                        {{-- Icono Lupa SVG --}}
                        <svg class="h-5 w-5 mr-1 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        {{ __('IA') }}
                    </x-nav-link>

                    {{-- 3. Buscar Productos (Todos) - Enlace Directo --}}
                    <x-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')">
                        {{ __('Buscar Productos') }}
                    </x-nav-link>

                    {{-- 4. Productos --}}
                    @can('view products')
                        <x-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')"> {{ __('Productos') }} </x-nav-link>
                    @endcan

                    @can('view product_failures') {{-- Asegúrate de crear este permiso --}}
                        <x-nav-link :href="route('admin.failures.index')" :active="request()->routeIs('admin.failures.*')">
                            {{ __('Fallas Productos') }}
                        </x-nav-link>
                    @endcan

                    {{-- 5. Gestionar Proveedores --}}
                    @can('view suppliers')
                        <x-nav-link :href="route('admin.suppliers.index')" :active="request()->routeIs('admin.suppliers.*')"> {{ __('Gestionar Proveedores') }} </x-nav-link>
                    @endcan

                    {{-- 6. Dropdown de Configuración --}}
                    @canany(['manage settings', 'manage roles', 'send invitations', 'view audit log', 'view users', 'import suppliers', 'import products', 'export suppliers', 'export products'])
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 border-b-2 {{ (request()->routeIs('admin.settings.*') || request()->routeIs('admin.roles.*') || request()->routeIs('invitations.*') || request()->routeIs('admin.audit-log.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.import_export.*') ) ? 'border-indigo-400 dark:border-indigo-600 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                        <div>{{ __('Configuración') }}</div>
                                        <div class="ms-1"> <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg> </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    {{-- Asegúrate que esta ruta sea correcta --}}
                                    @can('manage settings') <x-dropdown-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')"> {{ __('Ajustes Generales') }} </x-dropdown-link> @endcan
                                    @can('manage roles') <x-dropdown-link :href="route('admin.roles.index')"> {{ __('Roles y Permisos') }} </x-dropdown-link> @endcan
                                    @can('view audit log') <x-dropdown-link :href="route('admin.audit-log.index')"> {{ __('Auditoría') }} </x-dropdown-link> @endcan
                                    @can('send invitations') <x-dropdown-link :href="route('invitations.create')"> {{ __('Invitar Usuarios') }} </x-dropdown-link> @endcan
                                    @can('view users') <x-dropdown-link :href="route('admin.users.index')"> {{ __('Gestionar Usuarios') }} </x-dropdown-link> @endcan
                                    @canany(['import products', 'import suppliers', 'export products', 'export suppliers']) <x-dropdown-link :href="route('admin.import_export.index')"> {{ __('Importar/Exportar') }} </x-dropdown-link> @endcanany
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany

                </div>
            </div>

            {{-- Settings Dropdown --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Responsive Navigation Menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @role('Admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')"> {{ __('Admin Dashboard') }} </x-responsive-nav-link>
            @endrole
            @unlessrole('Admin')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"> {{ __('Dashboard') }} </x-responsive-nav-link>
            @endunlessrole

            {{-- ENLACE RESPONSIVE BUSCADOR IA --}}
            {{-- @can('usar buscador ia') --}}
            <x-responsive-nav-link :href="route('ai.search.index')" :active="request()->routeIs('ai.search.*')">
                {{ __('IA Buscador') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')"> {{ __('Buscar Productos') }} </x-responsive-nav-link>

            @can('view products')
                <x-responsive-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')"> {{ __('Productos') }} </x-responsive-nav-link>
            @endcan

            @can('view product_failures')
                <x-responsive-nav-link :href="route('admin.failures.index')" :active="request()->routeIs('admin.failures.*')">
                    {{ __('Fallas Productos') }}
                </x-responsive-nav-link>
            @endcan

            @can('view suppliers')
                <x-responsive-nav-link :href="route('admin.suppliers.index')" :active="request()->routeIs('admin.suppliers.*')"> {{ __('Gestionar Proveedores') }} </x-responsive-nav-link>
            @endcan

            @canany(['manage settings', 'manage roles', 'send invitations', 'view audit log', 'view users', 'import suppliers', 'import products', 'export suppliers', 'export products'])
                <div class="border-t border-gray-200 dark:border-gray-600"></div>
                @can('manage settings') <x-responsive-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')"> {{ __('Ajustes Generales') }} </x-responsive-nav-link> @endcan
                @can('manage roles') <x-responsive-nav-link :href="route('admin.roles.index')"> {{ __('Roles y Permisos') }} </x-responsive-nav-link> @endcan
                @can('view audit log') <x-responsive-nav-link :href="route('admin.audit-log.index')"> {{ __('Auditoría') }} </x-responsive-nav-link> @endcan
                @can('send invitations') <x-responsive-nav-link :href="route('invitations.create')"> {{ __('Invitar Usuarios') }} </x-responsive-nav-link> @endcan
                @can('view users') <x-responsive-nav-link :href="route('admin.users.index')"> {{ __('Gestionar Usuarios') }} </x-responsive-nav-link> @endcan
                @canany(['import products', 'import suppliers', 'export products', 'export suppliers']) <x-responsive-nav-link :href="route('admin.import_export.index')"> {{ __('Importar/Exportar') }} </x-responsive-nav-link> @endcanany
            @endcanany
        </div>

        {{-- Responsive Settings Options --}}
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>