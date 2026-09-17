<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Contacto;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\TelegramAccount;
use App\Models\TelegramLinkCode;
use App\Models\TelegramSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class TelegramBotService
{
    private const PAGE_SIZE = 5;

    private const CATEGORIES = [
        'laptops' => ['label' => '💻 Laptops', 'cat' => ['ASUS', 'DELL', 'lenovo', 'MSI', 'ominibook']],
        'pc' => ['label' => '🖥️ PCs de Escritorio', 'cat' => ['PC', 'PCHP']],
        'tablets' => ['label' => '📱 Tablets', 'cat' => ['Tablets']],
        'impresoras' => ['label' => '🖨️ Impresoras', 'cat' => ['CANON', 'EPSON']],
        'insumos' => ['label' => '🧪 Insumos', 'cat' => ['TINTA100', 'TINTA1000']],
        'duplicadoras' => ['label' => '💿 Duplicadores', 'cat' => ['DUPLICADORACD', 'DUPLICADORADVD', 'DUPLICADORABLU']],
    ];

    private const CITA_MOTIVOS = [
        'Mantenimiento preventivo',
        'Reparación de equipo',
        'Diagnóstico de fallas',
        'Instalación de software',
        'Limpieza interna',
        'Cambio de componentes',
        'Asesoría técnica',
        'Otro motivo',
    ];

    private const CONSULTA_ASUNTOS = [
        'Consulta sobre producto',
        'Disponibilidad y stock',
        'Precios y formas de pago',
        'Garantía y devoluciones',
        'Estado de mi pedido',
        'Reparación o soporte',
        'Facturación / RUC',
        'Otra consulta',
    ];

    public function handle(array $message): void
    {
        $chatId = data_get($message, 'chat.id');
        $fromId = data_get($message, 'from.id');
        if (! $chatId || ! $fromId || data_get($message, 'chat.type') !== 'private') {
            return;
        }
        $chatId = (string) $chatId;
        $fromId = (string) $fromId;

        $contact = data_get($message, 'contact');
        if ($contact) {
            $account = TelegramAccount::where('telegram_user_id', $fromId)->first();
            $this->saveContact($account, $contact, $fromId, $chatId);
            return;
        }

        $text = trim((string) data_get($message, 'text'));
        if (preg_match('/^\/vincular(?:\s+([A-Z0-9]+))?$/i', $text, $m)) {
            $this->start($m[1] ?? '', $chatId, $fromId, (string) data_get($message, 'from.username'));
            return;
        }
        if (preg_match('/^\/(start|menu|inicio)$/i', $text)) {
            $this->resetSession($chatId);
            $account = TelegramAccount::where('telegram_user_id', $fromId)->first();
            if (! $account) {
                $this->send($chatId, $this->unlinkedHelp(), null);
                return;
            }
            $user = User::find($account->id_usuario);
            if (! $user) {
                return;
            }
            $this->sendWelcome($chatId, $user);
            return;
        }

        $account = TelegramAccount::where('telegram_user_id', $fromId)->first();
        if (! $account) {
            $this->send($chatId, $this->unlinkedHelp(), null);
            return;
        }

        $account->update(['ultima_actividad_en' => now(), 'telegram_chat_id' => $chatId]);
        $user = User::find($account->id_usuario);
        if (! $user) {
            return;
        }

        // Cualquier texto fuera de comando se trata como "volver al menú"
        // porque el flujo normal se controla 100% con botones inline.
        $this->sendWelcome($chatId, $user, "No entendí ese mensaje. Usa los botones para continuar. 👇");
    }

    public function handleCallback(array $callback): void
    {
        $chatId = (string) data_get($callback, 'message.chat.id');
        $messageId = data_get($callback, 'message.message_id');
        if (! $chatId) {
            return;
        }
        $data = (string) data_get($callback, 'data');
        $this->answerCallback($callback, 'Procesando…');

        $fromId = (string) data_get($callback, 'from.id');
        $account = TelegramAccount::where('telegram_user_id', $fromId)->first();
        if (! $account) {
            $this->answerCallback($callback, '🔒 Vincula tu cuenta primero');
            $this->send($chatId, $this->unlinkedHelp());
            return;
        }
        $user = User::find($account->id_usuario);
        if (! $user) {
            return;
        }
        $account->update(['ultima_actividad_en' => now(), 'telegram_chat_id' => $chatId]);

        $session = $this->getSession($chatId);
        $this->dispatch($user, $chatId, $data, $session, $messageId);
        $this->setSession($chatId, $session);
    }

    private function dispatch(User $user, string $chatId, string $data, array &$session, ?int $messageId): void
    {
        // El prefijo identifica el handler; el resto es el payload.
        $parts = explode('|', $data, 2);
        $action = $parts[0];
        $payload = $parts[1] ?? '';

        match ($action) {
            'menu' => $this->screenWelcome($user, $chatId, $payload),
            'cat' => $this->screenCategory($chatId, $payload, $session, $messageId),
            'cat_page' => $this->screenCategory($chatId, $payload, $session, $messageId, true),
            'prod' => $this->screenProduct($user, $chatId, $payload, $session, $messageId),
            'cart_add' => $this->actionCartAdd($user, $chatId, $payload, $session, $messageId),
            'cart_view' => $this->screenCart($user, $chatId, $session, $messageId),
            'cart_clear' => $this->actionCartClear($user, $chatId, $session, $messageId),
            'cart_checkout' => $this->actionCartCheckout($user, $chatId, $session, $messageId),
            'svc' => $this->screenService($user, $chatId, $payload, $session, $messageId),
            'cita_mes' => $this->screenCitaDias($chatId, $payload, $session, $messageId),
            'cita_dia' => $this->screenCitaMotivos($chatId, $payload, $session, $messageId),
            'cita_mot' => $this->actionCitaCrear($user, $chatId, $payload, $session, $messageId),
            'cuenta' => $this->screenCuenta($user, $chatId, $payload, $session, $messageId),
            'cuenta_cita' => $this->screenCuentaCitaDetalle($user, $chatId, $payload, $messageId),
            'cuenta_ped' => $this->screenCuentaPedidoDetalle($user, $chatId, $payload, $messageId),
            'asesor' => $this->screenAsesor($user, $chatId, $payload, $session, $messageId),
            'asesor_asunto' => $this->actionAsesorCrear($user, $chatId, $payload, $session, $messageId),
            'cancel' => $this->actionCancelar($user, $chatId, $session, $messageId),
            'noop' => null,
            default => $this->screenWelcome($user, $chatId, 'Opción no reconocida. Vuelve al menú principal 👇'),
        };
    }

    // ────────────────────────── Pantallas (call center) ──────────────────────────

    private function screenWelcome(User $user, string $chatId, string $note = ''): void
    {
        $this->resetSession($chatId);
        $this->sendWelcome($chatId, $user, $note);
    }

    private function sendWelcome(string $chatId, User $user, string $note = ''): void
    {
        $name = (string) $user->usuario;
        $rol = ucfirst((string) $user->rol);
        $text = "👋 Hola <b>{$name}</b> ({$rol})\n\nSoy el asistente de <b>L&amp;M PC Computadoras</b>. Selecciona una opción para continuar:";
        if ($note !== '') {
            $text = $note . "\n\n" . $text;
        }
        $this->send($chatId, $text, $this->welcomeKeyboard($user));
        $this->setSession($chatId, ['state' => 'MENU', 'payload' => []]);
    }

    private function welcomeKeyboard(User $user): array
    {
        $isStaff = in_array(strtolower((string) $user->rol), ['admin', 'tecnico'], true);
        $rows = [
            [['text' => '🛒 Ver Productos', 'callback_data' => 'cat|laptops']],
            [['text' => '🔧 Servicio Técnico', 'callback_data' => 'svc|menu']],
            [['text' => '📱 Mi Cuenta', 'callback_data' => 'cuenta|menu']],
            [['text' => '💬 Hablar con Asesor', 'callback_data' => 'asesor|tipos']],
        ];
        if ($isStaff) {
            $rows[] = [['text' => '🛍 Carrito de cliente', 'callback_data' => 'cart_view|']];
        }
        return $this->inlineKeyboard($rows);
    }

    // ────────────────────────── Productos ──────────────────────────

    private function screenCategory(string $chatId, string $key, array &$session, ?int $messageId, bool $paginated = false): void
    {
        if (! isset(self::CATEGORIES[$key])) {
            $this->editOrSend($chatId, $messageId, '❓ Categoría no válida.', $this->backToMenu());
            return;
        }
        if (! $paginated) {
            $session['categoria'] = $key;
            $session['pagina'] = 0;
        }
        $page = (int) ($session['pagina'] ?? 0);
        $cat = self::CATEGORIES[$key];
        $total = Producto::whereIn('categoria', $cat['cat'])->count();
        $items = Producto::whereIn('categoria', $cat['cat'])
            ->orderBy('nombre')->skip($page * self::PAGE_SIZE)->take(self::PAGE_SIZE)->get();

        if ($total === 0) {
            $this->editOrSend($chatId, $messageId, "📭 No hay productos disponibles en <b>{$cat['label']}</b>.", $this->categoryKeyboard($key, 0, 0));
            return;
        }

        $lines = ["<b>{$cat['label']}</b> — {$total} producto(s)\n"];
        foreach ($items as $p) {
            $stock = (int) $p->unidades > 0 ? "{$p->unidades} en stock" : 'Agotado';
            $lines[] = "• <b>{$p->nombre}</b>\n  💲 {$p->precio} · 📦 {$stock}";
        }
        $this->editOrSend($chatId, $messageId, implode("\n", $lines), $this->categoryKeyboard($key, $page, $total));
    }

    private function categoryKeyboard(string $key, int $page, int $total): array
    {
        $cat = self::CATEGORIES[$key];
        $rows = [];
        $offset = $page * self::PAGE_SIZE;
        $pageSize = self::PAGE_SIZE;
        $items = Producto::whereIn('categoria', $cat['cat'])
            ->orderBy('nombre')->skip($offset)->take($pageSize)->get();
        foreach ($items as $p) {
            $label = mb_substr((string) $p->nombre, 0, 32) . ' · $' . $p->precio;
            $rows[] = [['text' => $label, 'callback_data' => "prod|{$p->id_producto}"]];
        }
        // Paginación
        $maxPage = (int) max(0, ceil($total / $pageSize) - 1);
        if ($maxPage > 0) {
            $nav = [];
            if ($page > 0) $nav[] = ['text' => '◀️ Anterior', 'callback_data' => "cat_page|{$key}:".($page - 1)];
            $nav[] = ['text' => '📄 '.($page + 1).'/'.($maxPage + 1), 'callback_data' => 'noop|'];
            if ($page < $maxPage) $nav[] = ['text' => 'Siguiente ▶️', 'callback_data' => "cat_page|{$key}:".($page + 1)];
            $rows[] = $nav;
        }
        // Cambio rápido de categoría
        $switcher = [];
        foreach (self::CATEGORIES as $k => $c) {
            $switcher[] = ['text' => $c['label'], 'callback_data' => "cat|{$k}"];
        }
        foreach (array_chunk($switcher, 2) as $chunk) {
            $rows[] = $chunk;
        }
        $rows[] = [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']];
        return $this->inlineKeyboard($rows);
    }

    private function screenProduct(User $user, string $chatId, string $id, array &$session, ?int $messageId): void
    {
        $producto = Producto::find((int) $id);
        if (! $producto) {
            $this->editOrSend($chatId, $messageId, '❌ Producto no disponible.', $this->backToMenu());
            return;
        }
        $stock = (int) $producto->unidades > 0
            ? "✅ {$producto->unidades} unidades disponibles"
            : '⛔ Agotado';
        $cart = $this->cartGet($session);
        $enCarrito = isset($cart[(int) $producto->id_producto]) ? (int) $cart[(int) $producto->id_producto] : 0;
        $text = "<b>{$producto->nombre}</b>\n\n"
            . "🔖 Serie: <code>{$producto->serie}</code>\n"
            . "💲 Precio: <b>\${$producto->precio}</b>\n"
            . "📦 {$stock}\n"
            . "🏷 Categoría: " . self::CATEGORIES[self::reverseCategory($producto->categoria)]['label'] . "\n"
            . ($enCarrito > 0 ? "\n🛒 En tu carrito: <b>{$enCarrito}</b>" : '');

        $rows = [];
        if ((int) $producto->unidades > 0) {
            $rows[] = [
                ['text' => '➕ Agregar 1', 'callback_data' => "cart_add|{$producto->id_producto}:1"],
                ['text' => '➕ Agregar 3', 'callback_data' => "cart_add|{$producto->id_producto}:3"],
            ];
        }
        $rows[] = [['text' => '💬 Consultar este producto', 'callback_data' => "asesor|producto:{$producto->id_producto}"]];
        $rows[] = [
            ['text' => '↩️ Volver a la categoría', 'callback_data' => "cat|" . self::reverseCategory($producto->categoria)],
            ['text' => '🏠 Menú', 'callback_data' => 'menu|'],
        ];
        $this->editOrSend($chatId, $messageId, $text, $this->inlineKeyboard($rows));
    }

    // ────────────────────────── Carrito (call-center style) ──────────────────────────

    private function actionCartAdd(User $user, string $chatId, string $payload, array &$session, ?int $messageId): void
    {
        [$id, $qty] = array_pad(explode(':', $payload, 2), 2, '1');
        $producto = Producto::find((int) $id);
        if (! $producto || (int) $producto->unidades <= 0) {
            $this->answerCallbackText($chatId, $messageId, '⛔ Producto agotado', $this->backToMenu());
            return;
        }
        $cart = $this->cartGet($session);
        $current = (int) ($cart[(int) $id] ?? 0);
        $add = max(1, (int) $qty);
        $new = min((int) $producto->unidades, $current + $add);
        $cart[(int) $id] = $new;
        $this->cartSet($session, $cart);

        $rows = [
            [['text' => '➕ Agregar otro', 'callback_data' => "prod|{$id}"]],
            [['text' => '🛒 Ver carrito', 'callback_data' => 'cart_view|']],
            [['text' => '🛍 Seguir comprando', 'callback_data' => 'cat|laptops']],
            [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
        ];
        $text = "✅ Agregado <b>{$producto->nombre}</b> al carrito.\n\nLlevas <b>{$new}</b> unidad(es) de este producto.";
        $this->editOrSend($chatId, $messageId, $text, $this->inlineKeyboard($rows));
    }

    private function screenCart(User $user, string $chatId, array &$session, ?int $messageId): void
    {
        $cart = $this->cartGet($session);
        if (empty($cart)) {
            $this->editOrSend($chatId, $messageId, "🛒 Tu carrito está vacío.\n\nEmpieza agregando productos desde el menú principal.",
                $this->inlineKeyboard([
                    [['text' => '🛒 Ver productos', 'callback_data' => 'cat|laptops']],
                    [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
                ]));
            return;
        }
        $productos = Producto::whereIn('id_producto', array_keys($cart))->get()->keyBy('id_producto');
        $total = 0;
        $lines = ["<b>🛒 Tu carrito</b>\n"];
        foreach ($cart as $id => $qty) {
            $p = $productos->get((int) $id);
            if (! $p) {
                unset($cart[$id]);
                continue;
            }
            $sub = (float) $p->precio * (int) $qty;
            $total += $sub;
            $lines[] = "• {$p->nombre}\n   {$qty} × \${$p->precio} = <b>\$" . number_format($sub, 2) . "</b>";
        }
        $this->cartSet($session, $cart);
        $lines[] = "\n💲 <b>Total: \$" . number_format($total, 2) . "</b>";

        $rows = [
            [['text' => '✅ Confirmar pedido', 'callback_data' => 'cart_checkout|']],
            [['text' => '➕ Seguir comprando', 'callback_data' => 'cat|laptops']],
            [['text' => '🗑 Vaciar carrito', 'callback_data' => 'cart_clear|']],
            [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
        ];
        $this->editOrSend($chatId, $messageId, implode("\n", $lines), $this->inlineKeyboard($rows));
    }

    private function actionCartClear(User $user, string $chatId, array &$session, ?int $messageId): void
    {
        $this->cartSet($session, []);
        $this->editOrSend($chatId, $messageId, "🗑 Carrito vaciado.", $this->backToMenu());
    }

    private function actionCartCheckout(User $user, string $chatId, array &$session, ?int $messageId): void
    {
        $cart = $this->cartGet($session);
        if (empty($cart)) {
            $this->editOrSend($chatId, $messageId, "🛒 Tu carrito está vacío.", $this->backToMenu());
            return;
        }
        $rows = [
            [
                ['text' => '✅ Sí, enviar pedido', 'callback_data' => 'cart_checkout|confirm'],
            ],
            [['text' => '❌ Cancelar', 'callback_data' => 'cart_view|']],
        ];
        $this->editOrSend($chatId, $messageId, "¿Confirmas que quieres enviar este pedido?\nUn asesor revisará stock y te contactará por correo.",
            $this->inlineKeyboard($rows));
    }

    // ────────────────────────── Servicio Técnico ──────────────────────────

    private function screenService(User $user, string $chatId, string $payload, array &$session, ?int $messageId): void
    {
        if ($payload === 'horarios') {
            $this->editOrSend($chatId, $messageId,
                "🕐 <b>Horarios de atención</b>\n\n• Lunes a Viernes: 9:00 — 19:00\n• Sábado: 9:00 — 17:00\n• Domingo: cerrado",
                $this->serviceKeyboard());
            return;
        }
        if ($payload === 'ubicacion') {
            $this->editOrSend($chatId, $messageId,
                "📍 <b>Ubicación</b>\n\nLa Ecuatoriana — Las Orquídeas / Oe9 Martha Bucaram / S37-49 / S37a, Quito.\n\n🗺 <a href=\"https://maps.app.goo.gl/V8ghM2XY9M4H8v6Z9\">Abrir en Google Maps</a>",
                $this->serviceKeyboard());
            return;
        }
        $this->editOrSend($chatId, $messageId, "🔧 <b>Servicio Técnico</b>\n\nSelecciona una opción:", $this->serviceKeyboard());
    }

    private function serviceKeyboard(): array
    {
        return $this->inlineKeyboard([
            [['text' => '🕐 Ver horarios', 'callback_data' => 'svc|horarios']],
            [['text' => '📍 Ver ubicación', 'callback_data' => 'svc|ubicacion']],
            [['text' => '📅 Agendar cita', 'callback_data' => 'cita_mes|0']],
            [['text' => '🆘 Hablar con soporte', 'callback_data' => 'asesor|tipos']],
            [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
        ]);
    }

    private function screenCitaDias(string $chatId, string $payload, array &$session, ?int $messageId): void
    {
        $offset = max(0, (int) $payload);
        $start = Carbon::today()->addDays($offset);
        $rows = [];
        $rows[] = [['text' => '📅 Elige un día disponible:', 'callback_data' => 'noop|']];
        for ($i = 0; $i < 14; $i++) {
            $d = $start->copy()->addDays($i);
            $label = $d->locale('es')->isoFormat('ddd D MMM');
            $rows[] = [['text' => $label, 'callback_data' => "cita_dia|{$d->toDateString()}"]];
        }
        $nav = [];
        if ($offset > 0) $nav[] = ['text' => '◀️ Sem. anterior', 'callback_data' => "cita_mes|" . max(0, $offset - 14)];
        $nav[] = ['text' => '▶️ Sem. siguiente', 'callback_data' => "cita_mes|" . ($offset + 14)];
        $rows[] = $nav;
        $rows[] = [['text' => '↩️ Volver', 'callback_data' => 'svc|menu'], ['text' => '🏠 Menú', 'callback_data' => 'menu|']];
        $this->editOrSend($chatId, $messageId, "📅 <b>Agendar cita</b>\n\nSelecciona el día:", $this->inlineKeyboard($rows));
    }

    private function screenCitaMotivos(string $chatId, string $fecha, array &$session, ?int $messageId): void
    {
        try {
            $d = Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Throwable) {
            $this->editOrSend($chatId, $messageId, '❌ Fecha inválida.', $this->serviceKeyboard());
            return;
        }
        if ($d->isPast()) {
            $this->editOrSend($chatId, $messageId, '⛔ La fecha debe ser futura.', $this->serviceKeyboard());
            return;
        }
        $session['cita_fecha'] = $fecha;
        $rows = [];
        $label = $d->locale('es')->isoFormat('dddd D [de] MMMM');
        $rows[] = [['text' => "📆 {$label}", 'callback_data' => 'noop|']];
        foreach (self::CITA_MOTIVOS as $i => $motivo) {
            $rows[] = [['text' => "🛠 {$motivo}", 'callback_data' => "cita_mot|{$i}"]];
        }
        $rows[] = [['text' => '↩️ Elegir otro día', 'callback_data' => 'cita_mes|0'], ['text' => '🏠 Menú', 'callback_data' => 'menu|']];
        $this->editOrSend($chatId, $messageId, "📅 <b>Motivo de la cita</b>\n\nDía elegido: <b>{$label}</b>\nSelecciona el motivo:", $this->inlineKeyboard($rows));
    }

    private function actionCitaCrear(User $user, string $chatId, string $payload, array &$session, ?int $messageId): void
    {
        $idx = (int) $payload;
        $motivo = self::CITA_MOTIVOS[$idx] ?? null;
        $fecha = $session['cita_fecha'] ?? '';
        if (! $motivo || $fecha === '') {
            $this->editOrSend($chatId, $messageId, '❌ Falta información para crear la cita.', $this->serviceKeyboard());
            return;
        }
        try {
            Cita::create([
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->usuario,
                'apellido' => '-',
                'correo' => $user->correo,
                'fecha' => $fecha,
                'telefono' => optional($user->telegramAccounts()->first())->telefono ?? 'No registrado',
                'motivo' => $motivo,
            ]);
            $label = Carbon::createFromFormat('Y-m-d', $fecha)->locale('es')->isoFormat('dddd D [de] MMMM');
            $this->editOrSend($chatId, $messageId,
                "✅ <b>Cita registrada</b>\n\n📅 {$label}\n🛠 {$motivo}\n\nTe enviaremos confirmación al correo.",
                $this->inlineKeyboard([
                    [['text' => '📅 Agendar otra cita', 'callback_data' => 'cita_mes|0']],
                    [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
                ]));
            $this->resetSession($chatId);
        } catch (\Throwable $e) {
            report($e);
            $this->editOrSend($chatId, $messageId, '❌ No fue posible registrar la cita. Intenta más tarde.', $this->serviceKeyboard());
        }
    }

    // ────────────────────────── Mi Cuenta ──────────────────────────

    private function screenCuenta(User $user, string $chatId, string $payload, array &$session, ?int $messageId): void
    {
        if ($payload === 'citas') {
            $items = Cita::where('id_usuario', $user->id_usuario)->orderByDesc('fecha')->limit(self::PAGE_SIZE)->get();
            $rows = [];
            $rows[] = [['text' => '📋 Tus últimas citas', 'callback_data' => 'noop|']];
            if ($items->isEmpty()) {
                $rows[] = [['text' => '— Sin citas registradas —', 'callback_data' => 'noop|']];
            } else {
                foreach ($items as $c) {
                    $rows[] = [['text' => "📅 {$c->fecha->format('d/m/Y')} · {$c->motivo}", 'callback_data' => "cuenta_cita|{$c->id_cita}"]];
                }
            }
            $rows[] = [['text' => '📦 Ver mis pedidos', 'callback_data' => 'cuenta|pedidos']];
            $rows[] = [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']];
            $this->editOrSend($chatId, $messageId, "📋 <b>Mis citas</b>", $this->inlineKeyboard($rows));
            return;
        }
        if ($payload === 'pedidos') {
            $items = Pedido::where('id_usuario', $user->id_usuario)->orderByDesc('fecha_pedido')->limit(self::PAGE_SIZE)->get();
            $rows = [];
            $rows[] = [['text' => '📦 Tus últimos pedidos', 'callback_data' => 'noop|']];
            if ($items->isEmpty()) {
                $rows[] = [['text' => '— Sin pedidos registrados —', 'callback_data' => 'noop|']];
            } else {
                foreach ($items as $p) {
                    $rows[] = [['text' => "🧾 #{$p->id_pedido} · \$" . number_format((float) $p->total, 2), 'callback_data' => "cuenta_ped|{$p->id_pedido}"]];
                }
            }
            $rows[] = [['text' => '📋 Ver mis citas', 'callback_data' => 'cuenta|citas']];
            $rows[] = [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']];
            $this->editOrSend($chatId, $messageId, "📦 <b>Mis pedidos</b>", $this->inlineKeyboard($rows));
            return;
        }
        // Resumen de cuenta
        $cuenta = [
            "👤 <b>{$user->usuario}</b>",
            "📧 {$user->correo}",
            "🛡 Rol: " . ucfirst((string) $user->rol),
        ];
        $cuenta[] = "📅 Citas registradas: " . Cita::where('id_usuario', $user->id_usuario)->count();
        $cuenta[] = "📦 Pedidos realizados: " . Pedido::where('id_usuario', $user->id_usuario)->count();
        $this->editOrSend($chatId, $messageId, implode("\n", $cuenta), $this->inlineKeyboard([
            [['text' => '📋 Mis citas', 'callback_data' => 'cuenta|citas'], ['text' => '📦 Mis pedidos', 'callback_data' => 'cuenta|pedidos']],
            [['text' => '🛍 Mi carrito', 'callback_data' => 'cart_view|']],
            [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
        ]));
    }

    private function screenCuentaCitaDetalle(User $user, string $chatId, string $id, ?int $messageId): void
    {
        $cita = Cita::where('id_usuario', $user->id_usuario)->where('id_cita', (int) $id)->first();
        if (! $cita) {
            $this->editOrSend($chatId, $messageId, '❌ Cita no encontrada.', $this->backToMenu());
            return;
        }
        $text = "📅 <b>Cita #{$cita->id_cita}</b>\n\n"
            . "📆 {$cita->fecha->format('d/m/Y')}\n"
            . "🛠 {$cita->motivo}\n"
            . "📞 {$cita->telefono}\n"
            . "📧 {$cita->correo}";
        $this->editOrSend($chatId, $messageId, $text, $this->inlineKeyboard([
            [['text' => '📅 Agendar otra', 'callback_data' => 'cita_mes|0']],
            [['text' => '↩️ Volver a mis citas', 'callback_data' => 'cuenta|citas']],
            [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
        ]));
    }

    private function screenCuentaPedidoDetalle(User $user, string $chatId, string $id, ?int $messageId): void
    {
        $pedido = Pedido::where('id_usuario', $user->id_usuario)->where('id_pedido', (int) $id)->first();
        if (! $pedido) {
            $this->editOrSend($chatId, $messageId, '❌ Pedido no encontrado.', $this->backToMenu());
            return;
        }
        $text = "🧾 <b>Pedido #{$pedido->id_pedido}</b>\n\n"
            . "📆 {$pedido->fecha_pedido->format('d/m/Y H:i')}\n"
            . "💲 Total: <b>\$" . number_format((float) $pedido->total, 2) . "</b>\n"
            . "📦 Estado: " . ucfirst((string) $pedido->estado ?? 'registrado');
        $this->editOrSend($chatId, $messageId, $text, $this->inlineKeyboard([
            [['text' => '↩️ Volver a mis pedidos', 'callback_data' => 'cuenta|pedidos']],
            [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
        ]));
    }

    // ────────────────────────── Asesor ──────────────────────────

    private function screenAsesor(User $user, string $chatId, string $payload, array &$session, ?int $messageId): void
    {
        if (str_starts_with($payload, 'producto:')) {
            $producto = Producto::find((int) substr($payload, 9));
            $session['asesor_producto'] = $producto?->id_producto;
            $this->editOrSend($chatId, $messageId,
                "💬 Vas a consultar por <b>" . ($producto?->nombre ?? 'un producto') . "</b>.\n\nSelecciona el motivo de tu consulta:",
                $this->inlineKeyboard($this->asuntosRows()));
            return;
        }
        $this->editOrSend($chatId, $messageId, "💬 <b>Hablar con un asesor</b>\n\nSelecciona el motivo de tu consulta:", $this->inlineKeyboard($this->asuntosRows()));
    }

    private function asuntosRows(): array
    {
        $rows = [];
        foreach (self::CONSULTA_ASUNTOS as $i => $asunto) {
            $rows[] = [['text' => "📨 {$asunto}", 'callback_data' => "asesor_asunto|{$i}"]];
        }
        $rows[] = [['text' => '↩️ Cancelar', 'callback_data' => 'cancel|']];
        return $rows;
    }

    private function actionAsesorCrear(User $user, string $chatId, string $payload, array &$session, ?int $messageId): void
    {
        $idx = (int) $payload;
        $asunto = self::CONSULTA_ASUNTOS[$idx] ?? null;
        if (! $asunto) {
            $this->editOrSend($chatId, $messageId, '❌ Opción no válida.', $this->backToMenu());
            return;
        }
        $productoId = $session['asesor_producto'] ?? null;
        $mensaje = $asunto;
        if ($productoId) {
            $p = Producto::find($productoId);
            if ($p) {
                $mensaje = "[Producto: {$p->nombre} (serie {$p->serie})] " . $asunto;
            }
        }
        try {
            Contacto::create([
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->usuario,
                'correo' => $user->correo,
                'compania' => 'Telegram',
                'mensaje' => $mensaje,
            ]);
            $this->editOrSend($chatId, $messageId,
                "✅ <b>Mensaje enviado</b>\n\n📨 {$asunto}\n\nUn asesor revisará tu solicitud y te contactará al correo registrado.",
                $this->inlineKeyboard([
                    [['text' => '💬 Enviar otra consulta', 'callback_data' => 'asesor|tipos']],
                    [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
                ]));
            $this->resetSession($chatId);
        } catch (\Throwable $e) {
            report($e);
            $this->editOrSend($chatId, $messageId, '❌ No fue posible enviar el mensaje.', $this->backToMenu());
        }
    }

    private function actionCancelar(User $user, string $chatId, array &$session, ?int $messageId): void
    {
        $this->editOrSend($chatId, $messageId, "❌ Operación cancelada.", $this->backToMenu());
        $this->resetSession($chatId);
    }

    // ────────────────────────── Vinculación ──────────────────────────

    private function start(string $code, string $chatId, string $fromId, string $username): void
    {
        if ($code === '') {
            $this->send($chatId, "Para vincularte:\n\n1) Entra a tu perfil en L&amp;M PC\n2) Haz clic en <b>Configurar Telegram</b>\n3) Pulsa <b>Generar código seguro</b>\n4) Regresa aquí y escribe:\n\n<code>/vincular TU-CODIGO</code>");
            return;
        }
        $link = TelegramLinkCode::where('codigo_hash', hash('sha256', strtoupper($code)))->whereNull('usado_en')->where('expira_en', '>', now())->first();
        if (! $link) {
            $this->send($chatId, '❌ Código inválido, usado o vencido. Genera otro desde el portal.');
            return;
        }
        if (TelegramAccount::where('id_usuario', $link->id_usuario)->exists() || TelegramAccount::where('telegram_user_id', $fromId)->exists()) {
            $this->send($chatId, '⚠ Esta cuenta de usuario o de Telegram ya está vinculada. Desvincúlala primero desde el portal.');
            return;
        }
        TelegramAccount::create([
            'id_usuario' => $link->id_usuario,
            'telegram_chat_id' => $chatId,
            'telegram_user_id' => $fromId,
            'username' => $username ?: null,
            'ultima_actividad_en' => now(),
        ]);
        $link->update(['usado_en' => now()]);
        $this->send($chatId,
            "✅ <b>Cuenta vinculada con éxito</b>.\n\nAhora comparte tu número para verificar tu identidad. Toca el botón:",
            $this->buildReplyKeyboard([['text' => '📱 Compartir mi contacto', 'request_contact' => true]]));
    }

    private function saveContact(?TelegramAccount $account, array $contact, string $fromId, string $chatId): void
    {
        if (! $account || (string) data_get($contact, 'user_id') !== $fromId) {
            $this->send($chatId, '🔒 Por seguridad solo acepto el contacto de la misma cuenta de Telegram vinculada.');
            return;
        }
        $phone = preg_replace('/[^0-9+]/', '', (string) data_get($contact, 'phone_number'));
        if (strlen(str_replace('+', '', $phone)) < 8 || strlen($phone) > 20) {
            $this->send($chatId, '❌ El número recibido no es válido.');
            return;
        }
        $account->update(['telefono' => $phone, 'telefono_verificado_en' => now()]);
        $user = User::find($account->id_usuario);
        $this->send($chatId, "✅ Número verificado.\n\nToca <b>/menu</b> o escribe <code>menu</code> para ver las opciones.", null);
    }

    // ────────────────────────── Helpers de teclado ──────────────────────────

    private function inlineKeyboard(array $rows): array
    {
        return ['inline_keyboard' => $rows];
    }

    private function backToMenu(): array
    {
        return $this->inlineKeyboard([
            [['text' => '🏠 Menú principal', 'callback_data' => 'menu|']],
        ]);
    }

    private function buildReplyKeyboard(array $rows): array
    {
        return [
            'keyboard' => $rows,
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
            'selective' => false,
        ];
    }

    private function unlinkedHelp(): string
    {
        return "🔒 <b>Tu cuenta aún no está vinculada.</b>\n\n"
            . "Para usar el bot:\n"
            . "1) Inicia sesión en el portal de L&amp;M PC\n"
            . "2) Entra a <b>Configurar Telegram</b>\n"
            . "3) Genera un código y envíalo aquí:\n\n"
            . "<code>/vincular TU-CODIGO</code>";
    }

    private function reverseCategory(string $cat): string
    {
        foreach (self::CATEGORIES as $key => $def) {
            if (in_array($cat, $def['cat'], true)) {
                return $key;
            }
        }
        return 'laptops';
    }

    // ────────────────────────── Carrito (persistente en sesión) ──────────────────────────

    private function cartGet(array &$session): array
    {
        $payload = $session['payload'] ?? [];
        return $payload['cart'] ?? [];
    }

    private function cartSet(array &$session, array $cart): void
    {
        $payload = $session['payload'] ?? [];
        $payload['cart'] = $cart;
        $session['payload'] = $payload;
    }

    // ────────────────────────── Telegram API ──────────────────────────

    private function send(string $chatId, string $text, ?array $markup = null): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return;
        }
        $body = [
            'chat_id' => $chatId,
            'text' => mb_substr($text, 0, 4000),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        if ($markup) {
            $body['reply_markup'] = json_encode($markup);
        }
        Http::timeout(8)->post("https://api.telegram.org/bot{$token}/sendMessage", $body);
    }

    private function editOrSend(string $chatId, ?int $messageId, string $text, array $markup): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return;
        }
        $body = [
            'chat_id' => $chatId,
            'text' => mb_substr($text, 0, 4000),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($markup),
            'disable_web_page_preview' => true,
        ];
        if ($messageId) {
            $body['message_id'] = $messageId;
            $resp = Http::timeout(8)->post("https://api.telegram.org/bot{$token}/editMessageText", $body);
            // Si el mensaje no se puede editar (muy antiguo, sin cambios, etc.) enviamos uno nuevo.
            if ($resp->successful() || $resp->json('description') === 'Bad Request: message is not modified') {
                return;
            }
        }
        $body['reply_markup'] = $markup;
        Http::timeout(8)->post("https://api.telegram.org/bot{$token}/sendMessage", $body);
    }

    private function answerCallback(array $callback, string $text = ''): void
    {
        $token = config('services.telegram.bot_token');
        $id = data_get($callback, 'id');
        if (! $token || ! $id) {
            return;
        }
        Http::timeout(8)->post("https://api.telegram.org/bot{$token}/answerCallbackQuery", [
            'callback_query_id' => $id,
            'text' => $text,
            'show_alert' => false,
        ]);
    }

    private function answerCallbackText(string $chatId, ?int $messageId, string $note, array $markup): void
    {
        $this->editOrSend($chatId, $messageId, $note, $markup);
    }

    // ────────────────────────── Sesión (compatible con modelo existente) ──────────────────────────

    private function getSession(string $chatId): array
    {
        $session = TelegramSession::where('telegram_chat_id', $chatId)->first();
        if (! $session) {
            return ['state' => 'MENU', 'payload' => []];
        }
        $session->update(['ultima_actividad_en' => now()]);
        $payload = $session->payload ?? [];
        // Compatibilidad: si el payload antiguo no es un array, reiniciamos.
        if (! is_array($payload)) {
            $payload = [];
        }
        return [
            'state' => $session->state ?? 'MENU',
            'categoria' => $session->categoria,
            'pagina' => (int) ($payload['pagina'] ?? 0),
            'cita_fecha' => $session->cita_fecha,
            'asesor_producto' => $payload['asesor_producto'] ?? null,
            'payload' => $payload,
        ];
    }

    private function setSession(string $chatId, array $data): void
    {
        $session = TelegramSession::updateOrCreate(
            ['telegram_chat_id' => $chatId],
            ['ultima_actividad_en' => now()]
        );
        if (isset($data['state'])) {
            $session->state = $data['state'];
        }
        if (array_key_exists('categoria', $data)) {
            $session->categoria = $data['categoria'];
        }
        if (array_key_exists('cita_fecha', $data)) {
            $session->cita_fecha = $data['cita_fecha'];
        }
        if (isset($data['payload']) && is_array($data['payload'])) {
            $session->payload = $data['payload'];
        }
        $session->save();
    }

    private function resetSession(string $chatId): void
    {
        TelegramSession::where('telegram_chat_id', $chatId)->delete();
    }
}
