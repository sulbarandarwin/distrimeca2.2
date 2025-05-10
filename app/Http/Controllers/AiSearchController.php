<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;
// Usaremos el Facade de OpenAI que es compatible con la API de DeepSeek para CHAT
use OpenAI\Laravel\Facades\OpenAI as OpenAIFacade;

class AiSearchController extends Controller
{
    public function index()
    {
        return view('ai.search'); // La vista del formulario de búsqueda
    }

    public function searchProducts(Request $request)
    {
        $userQuery = $request->input('query');
        if (empty($userQuery)) {
            return response()->json(['error' => 'La consulta no puede estar vacía.'], 400);
        }

        $settings = Setting::pluck('value', 'key')->all();
        // Forzaremos DeepSeek para esta implementación, o puedes leerlo de $settings
        $provider = $settings['ai_search_provider'] ?? 'deepseek'; // O directamente $provider = 'deepseek';

        $finalProducts = collect(); // Colección para los productos finales a mostrar
        $debugInfo = ["Proveedor de IA seleccionado: {$provider}"];

        try {
            $debugInfo[] = "Procesando búsqueda para: \"{$userQuery}\"";

            // 1. Búsqueda inicial por palabras clave en tu base de datos
            $keywords = preg_split('/\s+/', $userQuery, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($keywords) && !empty($userQuery)) { $keywords = [$userQuery]; }

            $initialResults = collect();
            if (!empty($keywords)) {
                $initialResults = Product::with(['category', 'supplier'])
                    ->where(function ($q) use ($keywords) {
                        foreach ($keywords as $keyword) {
                            if(!empty($keyword)) {
                                $q->orWhere('name', 'LIKE', "%{$keyword}%")
                                  ->orWhere('description', 'LIKE', "%{$keyword}%");
                            }
                        }
                    })
                    ->limit(15) // Obtener un máximo de 15 productos para enviar a la IA
                    ->get();
                $debugInfo[] = "Productos encontrados por palabras clave (para IA): " . $initialResults->count();
            } else {
                $debugInfo[] = "No se generaron palabras clave para la búsqueda inicial.";
            }

            // 2. Si hay resultados iniciales y el proveedor es DeepSeek, re-clasificar/seleccionar con IA
            if ($initialResults->isNotEmpty() && $provider === 'deepseek') {
                $apiKey = config('services.deepseek.api_key');
                $baseUri = config('services.deepseek.base_uri'); // Ej: 'https://api.deepseek.com/v1'

                if (!$apiKey || !$baseUri) {
                    throw new Exception("DEEPSEEK_API_KEY o base_uri no configuradas.");
                }
                $debugInfo[] = "Usando DeepSeek para re-clasificación. Base URI: {$baseUri}";

                // Formatear los productos para enviarlos a la IA
                $productContextForAI = "Contexto de Productos Encontrados:\n";
                $initialResultsArray = []; // Para mapear IDs de respuesta de IA a productos reales
                foreach ($initialResults as $index => $product) {
                    $initialResultsArray[$index] = $product; // Guardar el producto completo
                    $productContextForAI .= "ID_INTERNO: {$index}\n";
                    $productContextForAI .= "Nombre: " . ($product->name ?? 'N/A') . "\n";
                    $productContextForAI .= "Descripción: " . strip_tags($product->description ?? 'N/A') . "\n";
                    $productContextForAI .= "Categoría: " . ($product->category->name ?? 'N/A') . "\n";
                    $productContextForAI .= "---\n";
                }

                // Prompt para DeepSeek (modelo de CHAT)
                // ¡¡¡IMPORTANTE: AJUSTA EL NOMBRE DEL MODELO DE CHAT DE DEEPSEEK!!!
                $chatModel = 'deepseek-chat'; // O 'deepseek-coder' si es el que recomiendan para esto
                $prompt = "Consulta del Usuario: \"{$userQuery}\"\n\n" .
                          "He encontrado los siguientes productos en mi base de datos que podrían coincidir. Por favor, revisa la lista y devuelve SOLO los ID_INTERNO de los 3 productos que consideres MÁS RELEVANTES para la consulta del usuario, separados por comas (ej: 0,2,5). Si ninguno es muy relevante, devuelve 'NINGUNO'. No añadas explicaciones, solo los IDs o 'NINGUNO'.\n\n" .
                          $productContextForAI;

                $debugInfo[] = "Enviando a DeepSeek (Chat). Modelo: {$chatModel}. Prompt (primeros 200 chars): " . substr($prompt, 0, 200) . "...";

                // Dentro del if ($provider === 'deepseek')
                $deepSeekClient = \OpenAI::factory()
                ->withApiKey($apiKey)
                ->withBaseUri($baseUri)
                ->make();

                $chatResponse = $deepSeekClient->chat()->create([
                    'model' => $chatModel,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3, // Un poco de creatividad, pero no demasiada para re-ranking
                ]);

                $aiResponseText = trim($chatResponse->choices[0]->message->content ?? '');
                $debugInfo[] = "Respuesta cruda de DeepSeek Chat: '{$aiResponseText}'";

                if (!empty($aiResponseText) && strtoupper($aiResponseText) !== 'NINGUNO') {
                    $selectedInternalIds = array_map('trim', explode(',', $aiResponseText));
                    foreach ($selectedInternalIds as $internalId) {
                        if (is_numeric($internalId) && isset($initialResultsArray[(int)$internalId])) {
                            $finalProducts->push($initialResultsArray[(int)$internalId]);
                        }
                    }
                    if ($finalProducts->isNotEmpty()) {
                        $debugInfo[] = "Productos seleccionados y reordenados por DeepSeek: " . $finalProducts->count();
                    } else {
                         $debugInfo[] = "DeepSeek no devolvió IDs válidos. Mostrando resultados de palabras clave.";
                         $finalProducts = $initialResults->take(5); // Fallback si la IA no devuelve IDs válidos
                    }
                } else {
                    $debugInfo[] = "DeepSeek respondió 'NINGUNO' o respuesta vacía. Mostrando primeros resultados de palabras clave.";
                    $finalProducts = $initialResults->take(5); // Fallback a los primeros 5 si IA dice NINGUNO
                }
            } else { // Si no se usa IA o no hubo resultados iniciales
                 $debugInfo[] = $initialResults->isEmpty() ? "No hubo resultados iniciales por palabras clave." : "IA no activada o no aplicable. Mostrando resultados de palabras clave.";
                 $finalProducts = $initialResults; // Mostrar todos los resultados de palabras clave (hasta el límite de 15)
            }


            $html = view('ai.partials._search_results', ['products' => $finalProducts, 'debugInfo' => $debugInfo])->render();
            return response()->json(['html' => $html]);

        } catch (Throwable $e) {
            $errorMessage = "Error en búsqueda IA ({$provider}): " . $e->getMessage();
            Log::error($errorMessage, ['query' => $userQuery, 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => substr($e->getTraceAsString(), 0, 1000)]);
            $debugInfo[] = "ERROR CRÍTICO: " . $errorMessage;
            $html = view('ai.partials._search_results', ['products' => collect(), 'debugInfo' => $debugInfo, 'criticalError' => "Error: " . $e->getMessage()])->render();
            return response()->json(['html' => $html, 'error_message_for_user' => 'Ocurrió un error al procesar búsqueda. Intenta más tarde.'], 500);
        }
    }
}