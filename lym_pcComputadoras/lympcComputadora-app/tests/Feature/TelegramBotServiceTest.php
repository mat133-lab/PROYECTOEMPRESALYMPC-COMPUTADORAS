<?php

namespace Tests\Feature;

use App\Mail\TelegramAdvisorNotificationMail;
use App\Models\Producto;
use App\Models\TelegramAccount;
use App\Models\TelegramActionRequest;
use App\Models\TelegramLinkCode;
use App\Models\TelegramSession;
use App\Models\User;
use App\Services\TelegramActionService;
use App\Services\TelegramBotService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TelegramBotServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->increments('id_usuario');
            $table->string('usuario');
            $table->string('correo');
            $table->string('rol');
        });
        Schema::create('telegram_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('id_usuario')->unique();
            $table->string('telegram_chat_id', 32)->unique();
            $table->string('telegram_user_id', 32)->unique();
            $table->string('username')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->timestamp('telefono_verificado_en')->nullable();
            $table->timestamp('ultima_actividad_en')->nullable();
            $table->timestamps();
        });
        Schema::create('telegram_link_codes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('id_usuario');
            $table->string('codigo_hash', 64)->unique();
            $table->timestamp('expira_en');
            $table->timestamp('usado_en')->nullable();
            $table->string('ip_solicitud', 45)->nullable();
            $table->timestamps();
        });
        Schema::create('telegram_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('telegram_chat_id', 32)->unique();
            $table->string('telegram_user_id', 32)->nullable();
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('state')->nullable();
            $table->string('categoria')->nullable();
            $table->date('cita_fecha')->nullable();
            $table->string('contacto_tipo')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('ultima_actividad_en')->nullable();
            $table->timestamps();
        });
        Schema::create('productos', function (Blueprint $table): void {
            $table->increments('id_producto');
            $table->string('nombre');
            $table->string('serie');
            $table->date('fecha');
            $table->integer('unidades');
            $table->decimal('precio', 10, 2);
            $table->string('imagen');
            $table->string('categoria');
        });
        Schema::create('contacto', function (Blueprint $table): void {
            $table->increments('id_mensaje');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('nombre');
            $table->string('correo');
            $table->string('compania');
            $table->text('mensaje');
            $table->timestamp('fecha')->useCurrent();
        });
        Schema::create('pedidos', function (Blueprint $table): void {
            $table->increments('id_pedido');
            $table->unsignedInteger('id_usuario');
            $table->dateTime('fecha_pedido')->useCurrent();
            $table->decimal('total', 10, 2);
        });
        Schema::create('telegram_action_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('id_usuario');
            $table->string('telegram_chat_id', 32);
            $table->string('accion', 80);
            $table->json('datos');
            $table->string('resumen', 1000);
            $table->string('confirmacion_hash', 64)->unique();
            $table->string('estado', 24)->default('pendiente');
            $table->timestamp('expira_en');
            $table->timestamp('confirmada_en')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
        Schema::create('telegram_action_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('telegram_chat_id', 32)->nullable();
            $table->string('accion', 80);
            $table->string('estado', 30);
            $table->json('detalle')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('notificaciones', function (Blueprint $table): void {
            $table->increments('id_notificacion');
            $table->unsignedInteger('id_remitente')->nullable();
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('titulo', 180);
            $table->text('mensaje');
            $table->string('correo_destino');
            $table->string('estado_correo', 30)->default('pendiente');
            $table->timestamp('fecha_creacion')->useCurrent();
        });
    }

    public function test_confirming_a_telegram_cart_creates_an_advisor_request_not_a_paid_order(): void
    {
        config(['services.telegram.bot_token' => 'test-telegram-token']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true])]);
        Mail::fake();
        $user = $this->linkedUser();
        User::create([
            'usuario' => 'Asesor',
            'correo' => 'advisor@example.com',
            'rol' => 'asistente',
        ]);
        User::create([
            'usuario' => 'Segundo asesor',
            'correo' => 'advisor2@example.com',
            'rol' => 'asistente',
        ]);
        $this->assertSame(2, User::whereRaw('LOWER(rol) = ?', ['asistente'])->count());
        $product = Producto::create([
            'nombre' => 'Laptop de prueba',
            'serie' => 'TEST-001',
            'fecha' => today(),
            'unidades' => 3,
            'precio' => 500,
            'imagen' => 'test.webp',
            'categoria' => 'ASUS',
        ]);
        TelegramSession::create([
            'telegram_chat_id' => '123',
            'state' => 'MENU',
            'payload' => ['cart' => [$product->id_producto => 2]],
        ]);

        app(TelegramBotService::class)->handleCallback([
            'id' => 'callback-1',
            'from' => ['id' => 456],
            'message' => ['chat' => ['id' => 123], 'message_id' => 99],
            'data' => 'cart_checkout|confirm',
        ]);

        $this->assertDatabaseHas('contacto', [
            'id_usuario' => $user->id_usuario,
            'compania' => 'Solicitud de pedido por Telegram',
        ]);
        $this->assertDatabaseMissing('pedidos', ['id_usuario' => $user->id_usuario]);
        $this->assertSame([], TelegramSession::where('telegram_chat_id', '123')->firstOrFail()->payload['cart']);
        Mail::assertSent(TelegramAdvisorNotificationMail::class);
        $advisorMail = Mail::sent(TelegramAdvisorNotificationMail::class)->first();
        $advisorMail->build();
        $this->assertTrue($advisorMail->hasTo('advisor@example.com'));
        $this->assertTrue($advisorMail->hasBcc('advisor2@example.com'));
        $this->assertTrue($advisorMail->hasReplyTo($user->correo));
    }

    public function test_free_text_ai_actions_are_pending_until_the_user_confirms_them(): void
    {
        config([
            'services.telegram.bot_token' => 'test-telegram-token',
            'services.assistant_ai.provider' => 'nvidia',
            'services.assistant_ai.nvidia_key' => 'test-nvidia-key',
            'services.assistant_ai.nvidia_model' => 'openai/gpt-oss-20b',
            'services.assistant_ai.nvidia_base_url' => 'https://integrate.api.nvidia.com/v1',
        ]);
        $intent = json_encode([
            'accion' => 'crear_cita',
            'datos' => ['fecha' => now()->addDays(5)->toDateString(), 'motivo' => 'Mantenimiento'],
        ]);
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => $intent]]],
            ]),
            'https://api.telegram.org/*' => Http::response(['ok' => true]),
        ]);
        $this->linkedUser();

        app(TelegramBotService::class)->handle([
            'chat' => ['id' => 123, 'type' => 'private'],
            'from' => ['id' => 456, 'username' => 'test_user'],
            'text' => 'Quiero agendar una cita de mantenimiento.',
        ]);

        $request = TelegramActionRequest::firstOrFail();
        $this->assertSame('pendiente', $request->estado);
        $this->assertDatabaseHas('telegram_action_audits', [
            'accion' => 'crear_cita',
            'estado' => 'pendiente',
        ]);
        $confirmationToken = null;
        Http::assertSent(function ($httpRequest) use (&$confirmationToken): bool {
            if (! str_contains($httpRequest->url(), '/sendMessage')) {
                return false;
            }
            $markup = json_decode((string) $httpRequest['reply_markup'], true);
            $callbackData = $markup['inline_keyboard'][0][0]['callback_data'] ?? '';
            if (! preg_match('/^accion_confirmar\|([A-Z0-9]+)$/', $callbackData, $matches)) {
                return false;
            }
            $confirmationToken = $matches[1];
            return true;
        });
        $this->assertNotNull($confirmationToken);
        Http::assertSent(fn ($httpRequest): bool =>
            str_contains($httpRequest->url(), '/chat/completions')
            && $httpRequest['model'] === 'openai/gpt-oss-20b'
        );

        app(TelegramBotService::class)->handleCallback([
            'id' => 'callback-cancel',
            'from' => ['id' => 456],
            'message' => ['chat' => ['id' => 123], 'message_id' => 100],
            'data' => "accion_cancelar|{$confirmationToken}",
        ]);

        $this->assertSame('cancelada', $request->fresh()->estado);
    }

    public function test_start_clears_an_orphaned_telegram_link_and_explains_how_to_relink(): void
    {
        config(['services.telegram.bot_token' => 'test-telegram-token']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true])]);
        TelegramAccount::create([
            'id_usuario' => 999,
            'telegram_chat_id' => '123',
            'telegram_user_id' => '456',
        ]);

        app(TelegramBotService::class)->handle([
            'chat' => ['id' => 123, 'type' => 'private'],
            'from' => ['id' => 456],
            'text' => '/start',
        ]);

        $this->assertDatabaseMissing('telegram_accounts', ['telegram_user_id' => '456']);
        Http::assertSent(fn ($httpRequest): bool =>
            str_contains($httpRequest->url(), '/sendMessage')
            && str_contains($httpRequest['text'], 'vínculo obsoleto')
        );
    }

    public function test_successful_linking_sends_a_valid_contact_keyboard(): void
    {
        config(['services.telegram.bot_token' => 'test-telegram-token']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true])]);
        $user = User::create([
            'usuario' => 'Usuario de prueba',
            'correo' => 'test@example.com',
            'rol' => 'cliente',
        ]);
        $code = 'TESTLINKCODE123';
        TelegramLinkCode::create([
            'id_usuario' => $user->id_usuario,
            'codigo_hash' => hash('sha256', $code),
            'expira_en' => now()->addMinutes(10),
        ]);

        app(TelegramBotService::class)->handle([
            'chat' => ['id' => 123, 'type' => 'private'],
            'from' => ['id' => 456, 'username' => 'test_user'],
            'text' => "/vincular {$code}",
        ]);

        Http::assertSent(fn ($httpRequest): bool =>
            str_contains($httpRequest->url(), '/sendMessage')
            && data_get(json_decode((string) $httpRequest['reply_markup'], true), 'keyboard.0.0.request_contact') === true
        );

        app(TelegramBotService::class)->handle([
            'chat' => ['id' => 123, 'type' => 'private'],
            'from' => ['id' => 456],
            'text' => '/start',
        ]);
        Http::assertSent(fn ($httpRequest): bool =>
            str_contains($httpRequest->url(), '/sendMessage')
            && data_get(json_decode((string) $httpRequest['reply_markup'], true), 'keyboard.0.0.request_contact') === true
        );

        app(TelegramBotService::class)->handle([
            'chat' => ['id' => 123, 'type' => 'private'],
            'from' => ['id' => 456],
            'contact' => ['user_id' => 456, 'phone_number' => '+593999999999'],
        ]);

        $this->assertNotNull(TelegramAccount::where('telegram_user_id', '456')->firstOrFail()->telefono_verificado_en);
        Http::assertSent(fn ($httpRequest): bool =>
            str_contains($httpRequest->url(), '/sendMessage')
            && data_get(json_decode((string) $httpRequest['reply_markup'], true), 'inline_keyboard.0.0.callback_data') === 'cat|laptops'
        );
    }

    public function test_confirmed_ai_contact_request_notifies_assistant_accounts(): void
    {
        Mail::fake();
        $customer = $this->linkedUser();
        User::create([
            'usuario' => 'Asesor',
            'correo' => 'advisor@example.com',
            'rol' => 'asistente',
        ]);
        $this->assertSame(1, User::whereRaw('LOWER(rol) = ?', ['asistente'])->count());

        $service = app(TelegramActionService::class);
        $pending = $service->createPending($customer, '123', [
            'accion' => 'crear_contacto',
            'datos' => ['mensaje' => 'Quiero consultar disponibilidad.'],
        ]);
        $result = $service->confirm($customer, '123', (string) $pending->getAttribute('plain_confirmation_token'));

        $this->assertDatabaseHas('contacto', [
            'id_usuario' => $customer->id_usuario,
            'mensaje' => 'Quiero consultar disponibilidad.',
        ]);
        $this->assertStringContainsString('Se avisó a los asesores', $result);
        Mail::assertSent(TelegramAdvisorNotificationMail::class);
        $advisorMail = Mail::sent(TelegramAdvisorNotificationMail::class)->first();
        $advisorMail->build();
        $this->assertTrue($advisorMail->hasTo('advisor@example.com'));
        $this->assertTrue($advisorMail->hasReplyTo($customer->correo));
    }

    private function linkedUser(): User
    {
        $user = User::create([
            'usuario' => 'Usuario de prueba',
            'correo' => 'test@example.com',
            'rol' => 'cliente',
        ]);
        TelegramAccount::create([
            'id_usuario' => $user->id_usuario,
            'telegram_chat_id' => '123',
            'telegram_user_id' => '456',
        ]);

        return $user;
    }
}
