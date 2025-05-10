<?php

// Cargar el autoloader de Composer y el bootstrap de Laravel
echo "Cargando entorno de Laravel...\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
try {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "Entorno de Laravel cargado.\n";
} catch (Throwable $e) {
    echo "Error cargando el Kernel de Laravel: " . $e->getMessage() . "\n";
    echo "Asegúrate de que tu bootstrap/app.php no tenga errores de sintaxis.\n";
    exit(1);
}

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient; // <--- USAREMOS GUZZLE
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Throwable;

// --- CONFIGURACIÓN ---
$apiKey = config('services.deepseek.api_key');

// ¡¡IMPORTANTE!! Esta URL debe ser el ENDPOINT COMPLETO para embeddings de DeepSeek
// La documentación de DeepSeek que me mostraste (image_5cd841.png) decía "Base URL: https://api.deepseek.com"
// El endpoint para embeddings suele ser algo como /v1/embeddings o similar. ¡DEBES CONFIRMARLO!
// Si es compatible con OpenAI, es probable que sea '/v1/embeddings' añadido a la base URI.
$deepSeekEmbeddingsEndpoint = (config('services.deepseek.base_uri') ?? 'https://api.deepseek.com/v1') . '/embeddings';
// $deepSeekEmbeddingsEndpoint = 'https://api.deepseek.com/embeddings'; // O la ruta que sea correcta

// ¡¡¡MUY IMPORTANTE!!! Reemplaza con el nombre REAL del modelo de embedding de DeepSeek
$embeddingModel = 'PON_AQUI_TU_MODELO_DE_EMBEDDING_DEEPSEEK'; // Ejemplo: 'deepseek-embed-vX', 'text-embedding-v2'

$chunkSize = 5;  // Procesar de 5 en 5
$sleepTime = 3;  // Esperar 3 segundos entre llamadas a la API
$regenerateAll = false; // Poner en true si quieres regenerar todos
// --------------------

if (empty($embeddingModel) || $embeddingModel === 'PON_AQUI_TU_MODELO_DE_EMBEDDING_DEEPSEEK') {
    echo "❌ Error: Debes especificar el nombre correcto del modelo de embedding de DeepSeek en la variable \$embeddingModel de este script.\n";
    exit(1);
}
if (!$apiKey || !$deepSeekEmbeddingsEndpoint) {
    echo "❌ Error: DEEPSEEK_API_KEY o el Endpoint de Embeddings no están configurados.\n";
    exit(1);
}

echo "🚀 Iniciando generación de embeddings (Guzzle Directo) para DeepSeek...\n";
echo "ℹ️  Usando modelo: {$embeddingModel}\n";
echo "ℹ️  Endpoint: {$deepSeekEmbeddingsEndpoint}\n";

$guzzleClient = new GuzzleClient([
    'timeout'  => 30.0, // Timeout de 30 segundos
    'connect_timeout' => 10.0, // Timeout de conexión
]);

$processed = 0; $failed = 0; $skipped = 0;

$query = Product::query();
if (!$regenerateAll) { $query->whereNull('embedding'); }

$totalProductsToProcess = $query->count();
if ($totalProductsToProcess === 0) {
    echo "👍 No hay productos para procesar con los criterios actuales.\n";
    exit(0);
}
echo "🔍 Productos a procesar en total: {$totalProductsToProcess}\n";

$query->orderBy('id')->chunkById($chunkSize, function ($products) use (&$processed, &$failed, &$skipped, $sleepTime, $guzzleClient, $embeddingModel, $apiKey, $deepSeekEmbeddingsEndpoint, $regenerateAll) {
    echo "📦 Procesando lote de " . $products->count() . " productos...\n";

    foreach ($products as $product) {
        if (!$regenerateAll && $product->embedding && json_decode($product->embedding)) {
            $skipped++;
            echo "  -> Omitido (ya tiene embedding): Producto ID {$product->id} ('{$product->name}')\n";
            continue;
        }

        $textToEmbed = "Producto: " . ($product->name ?? '') . "\nDescripción: " . ($product->description ?? '') . "\nCategoría: " . ($product->category->name ?? 'N/A');
        if (empty(trim($textToEmbed)) || trim($textToEmbed) === "Producto: \nDescripción: \nCategoría: N/A") {
            echo "⚠️ Omitiendo Producto ID: {$product->id} ('{$product->name}') por falta de texto.\n";
            $skipped++;
            continue;
        }

        echo "  Enviando texto para ID {$product->id} ('" . substr($product->name ?? '', 0, 30) . "...'): '" . substr($textToEmbed, 0, 70) . "...'\n";

        try {
            // --- INICIO: Llamada directa con Guzzle ---
            $response = $guzzleClient->post($deepSeekEmbeddingsEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => [ // Guzzle convierte esto a JSON automáticamente
                    'model' => $embeddingModel,
                    'input' => $textToEmbed, // La API de OpenAI/DeepSeek para embeddings suele esperar un string aquí (o un array de strings)
                                             // Si DeepSeek espera un array con un solo string: 'input' => [$textToEmbed],
                ]
            ]);
            // --- FIN: Llamada directa con Guzzle ---

            $responseBody = $response->getBody()->getContents();
            echo "  Respuesta cruda DeepSeek (Guzzle) para ID {$product->id}: " . $responseBody . "\n";
            $responseData = json_decode($responseBody, true);

            $embeddingVector = null;

            // --- INICIO: Lógica para extraer el embedding del JSON de respuesta de DeepSeek ---
            // ESTA ES LA PARTE QUE MÁS PROBABLEMENTE NECESITES AJUSTAR BASÁNDOTE EN EL JSON CRUDO QUE VEAS
            if (isset($responseData['data'][0]['embedding']) && is_array($responseData['data'][0]['embedding'])) {
                // Estructura común de OpenAI
                $embeddingVector = $responseData['data'][0]['embedding'];
            } elseif (isset($responseData['embedding']) && is_array($responseData['embedding'])) {
                // Si DeepSeek devuelve el embedding directamente bajo una clave 'embedding' y es el vector
                $embeddingVector = $responseData['embedding'];
            } elseif (isset($responseData['embeddings'][0]) && is_array($responseData['embeddings'][0])) {
                // Si devuelve una lista de vectores directamente
                $embeddingVector = $responseData['embeddings'][0];
            }
            // Añade más `elseif` si DeepSeek tiene otra estructura. Ejemplo:
            // elseif (isset($responseData['result']['vector']) && is_array($responseData['result']['vector'])) {
            //     $embeddingVector = $responseData['result']['vector'];
            // }
            // --- FIN: Lógica para extraer el embedding ---


            if ($embeddingVector && count($embeddingVector) > 0) {
                DB::table('products')->where('id', $product->id)->update(['embedding' => json_encode($embeddingVector)]);
                $processed++;
                echo "  ✅ Embedding generado y guardado para ID: {$product->id}\n";
            } else {
                echo "  ⚠️ Embedding inválido o estructura inesperada para ID: {$product->id}. Respuesta decodificada arriba.\n";
                Log::warning("[Guzzle Embed Script] Embedding inválido o estructura inesperada DeepSeek", ['product_id' => $product->id, 'decoded_response' => $responseData]);
                $failed++;
            }

        } catch (GuzzleRequestException $e) { // Errores de red o HTTP de Guzzle
            echo "  ❌ Error de Red/API Guzzle para ID: {$product->id}: " . $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                echo "     Respuesta API (Guzzle error): " . substr($errorBody, 0, 500) . "\n"; // Mostrar solo una parte
                Log::error("[Guzzle Embed Script] Error API Guzzle (con cuerpo)", ['product_id' => $product->id, 'status_code' => $e->getResponse()->getStatusCode(), 'message' => $e->getMessage(), 'response_body' => $errorBody]);
            } else {
                Log::error("[Guzzle Embed Script] Error API Guzzle (sin cuerpo de respuesta)", ['product_id' => $product->id, 'message' => $e->getMessage()]);
            }
            $failed++;
        } catch (Throwable $generalError) { // Otros errores (ej. JSON mal formado en respuesta)
            echo "  ❌ Error General para ID: {$product->id}: " . $generalError->getMessage() . "\n";
            Log::error("[Guzzle Embed Script] Error General", ['product_id' => $product->id, 'message' => $generalError->getMessage(), 'trace' => substr($generalError->getTraceAsString(), 0, 500)]);
            $failed++;
        }
        if ($sleepTime > 0) sleep($sleepTime); // Pausa después de cada producto
    } // Fin foreach
    echo "--- Fin del Lote ---\n";
}); // Fin chunkById

echo "\n✅ Proceso de generación de embeddings (Script Guzzle) completado.\n";
echo "   - Productos procesados/actualizados: {$processed}\n";
echo "   - Productos omitidos: {$skipped}\n";
if ($failed > 0) {
    echo "   - Productos con errores: {$failed}\n";
}
echo "Revisa storage/logs/laravel.log para más detalles si hubo errores.\n";

?>