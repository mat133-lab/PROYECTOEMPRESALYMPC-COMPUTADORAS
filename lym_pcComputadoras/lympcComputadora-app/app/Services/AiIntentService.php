<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class AiIntentService
{
    public function interpret(User $user, string $message): array
    {
        $prompt = "Eres el intérprete de acciones de L&M PC. Usuario con rol {$user->rol}. Devuelve SOLO JSON válido con accion y datos. Acciones: crear_cita(fecha YYYY-MM-DD,motivo), crear_contacto(mensaje,compania opcional), crear_producto(nombre,precio,unidades,categoria,serie opcional), actualizar_producto(id_producto,nombre,precio,unidades,categoria opcional), eliminar_producto(id_producto), respuesta(texto). No inventes valores ni ejecutes acciones. El rol cliente solo puede crear_cita o crear_contacto; tecnico/admin también productos. Mensaje: ".$message;
        $provider = config('services.assistant_ai.provider');
        $raw = match ($provider) {
            'qwen' => $this->qwen($prompt),
            'groq' => $this->groq($prompt),
            default => $this->gemini($prompt),
        };
        $json = json_decode(trim(preg_replace('/^```json\s*|\s*```$/i', '', $raw)), true);
        if (! is_array($json) || ! isset($json['accion'])) return ['accion' => 'respuesta', 'datos' => ['texto' => 'No pude entender la solicitud. Indica la acción y sus datos.']];
        return $json;
    }

    private function gemini(string $prompt): string
    {
        $key = config('services.assistant_ai.gemini_key'); if (! $key) throw new \RuntimeException('Falta configurar GEMINI_API_KEY.');
        $model = config('services.assistant_ai.gemini_model');
        $response = Http::timeout(20)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['responseMimeType' => 'application/json', 'temperature' => 0],
        ])->throw()->json();
        return (string) data_get($response, 'candidates.0.content.parts.0.text', '');
    }

    private function qwen(string $prompt): string
    {
        $key = config('services.assistant_ai.qwen_key'); if (! $key) throw new \RuntimeException('Falta configurar QWEN_API_KEY.');
        $base = rtrim((string) config('services.assistant_ai.qwen_base_url'), '/');
        $response = Http::timeout(20)->withToken($key)->post("{$base}/chat/completions", [
            'model' => config('services.assistant_ai.qwen_model'),
            'messages' => [['role' => 'system', 'content' => 'Responde únicamente JSON válido.'], ['role' => 'user', 'content' => $prompt]],
            'temperature' => 0, 'response_format' => ['type' => 'json_object'],
        ])->throw()->json();
        return (string) data_get($response, 'choices.0.message.content', '');
    }

    private function groq(string $prompt): string
    {
        $key = config('services.assistant_ai.groq_key'); if (! $key) throw new \RuntimeException('Falta configurar GROQ_API_KEY.');
        $base = rtrim((string) config('services.assistant_ai.groq_base_url'), '/');
        $response = Http::timeout(20)->withToken($key)->post("{$base}/chat/completions", [
            'model' => config('services.assistant_ai.groq_model'),
            'messages' => [['role' => 'system', 'content' => 'Responde únicamente JSON válido.'], ['role' => 'user', 'content' => $prompt]],
            'temperature' => 0, 'response_format' => ['type' => 'json_object'],
        ])->throw()->json();
        return (string) data_get($response, 'choices.0.message.content', '');
    }
}
