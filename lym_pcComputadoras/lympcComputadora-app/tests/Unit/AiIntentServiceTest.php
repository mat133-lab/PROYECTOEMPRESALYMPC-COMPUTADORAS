<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AiIntentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiIntentServiceTest extends TestCase
{
    public function test_it_uses_the_configured_nvidia_model_to_interpret_intents(): void
    {
        config([
            'services.assistant_ai.provider' => 'nvidia',
            'services.assistant_ai.nvidia_key' => 'test-nvidia-key',
            'services.assistant_ai.nvidia_model' => 'openai/gpt-oss-20b',
            'services.assistant_ai.nvidia_base_url' => 'https://integrate.api.nvidia.com/v1',
        ]);
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => '{"accion":"respuesta","datos":{"texto":"hola"}}']]],
            ]),
        ]);

        $intent = app(AiIntentService::class)->interpret(new User(['rol' => 'cliente']), 'hola');

        $this->assertSame('respuesta', $intent['accion']);
        $this->assertSame('hola', $intent['datos']['texto']);
        Http::assertSent(fn ($request): bool =>
            $request->url() === 'https://integrate.api.nvidia.com/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-nvidia-key')
            && $request['model'] === 'openai/gpt-oss-20b'
            && $request['response_format'] === ['type' => 'json_object']
        );
    }
}
