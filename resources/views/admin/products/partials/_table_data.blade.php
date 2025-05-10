{{-- Contenido del cuerpo de la tabla y paginación --}}
<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    @forelse ($products as $product)
        {{-- Añadir clases para filas alternas --}}
        <tr class="odd:bg-white dark:odd:bg-gray-800 even:bg-gray-50 dark:even:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
            {{-- Asegurar padding en todas las celdas (px-6 py-4 es un buen valor de Tailwind) --}}
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->id }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->code ?? 'N/A' }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $product->description }}">{{ Str::limit($product->description, 50) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{-- Leer símbolo de moneda desde settings o usar default --}}
                {{ $settings['currency_symbol'] ?? '$' }}{{ number_format($product->price ?? 0, 2) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->supplier->name ?? 'N/A' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->category->name ?? 'N/A' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                {{-- Mostrar botones según permisos --}}
                @can('edit all products') 
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Editar</a>
                @elsecan('edit own products') 
                     @if(Auth::user()->supplier_id == $product->supplier_id)
                         <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Editar</a>
                     @endif
                @endcan

                @can('delete products')
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Borrar</button>
                    </form>
                @endcan
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                No hay productos registrados que coincidan con los criterios.
            </td>
        </tr>
    @endforelse
</tbody>
<tfoot>
    <tr>
        {{-- Asegurar padding también en el footer de la tabla si es necesario --}}
        <td colspan="8" class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
             {{ $products->links() }}
        </td>
    </tr>
</tfoot>
