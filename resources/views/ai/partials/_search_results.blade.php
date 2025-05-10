@if($products && $products->count() > 0)
    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-md">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Código</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                    @unless(Auth::user()->hasRole('Cliente'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proveedor</th>
                    @endunless
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{-- Puedes hacer esto un enlace si tienes una vista de detalle del producto --}}
                            {{ $product->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->code ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $product->description }}">
                            {{ Str::limit($product->description, 60) }}
                        </td>
                         @unless(Auth::user()->hasRole('Cliente'))
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->supplier->name ?? 'N/A' }}</td>
                         @endunless
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->category->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    {{-- Este mensaje puede que no se vea si el JS ya pone uno, pero es un buen fallback --}}
    {{-- <p class="text-center text-gray-500 dark:text-gray-400 italic mt-4">No se encontraron resultados relevantes.</p> --}}
@endif