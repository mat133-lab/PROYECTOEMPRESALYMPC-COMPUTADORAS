<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\TelegramAccount;
use App\Models\TelegramLinkCode;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TelegramBotService
{
    private array $userSessions = [];

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

        $account = TelegramAccount::where('telegram_user_id', $fromId)->first();
        if (! $account) {
            $this->send($chatId, 'Primero vincula tu cuenta desde el portal de L&M PC y usa el código con /vincular.');
            return;
        }

        $account->update(['ultima_actividad_en' => now(), 'telegram_chat_id' => $chatId]);
        $user = User::find($account->id_usuario);
        if (! $user) {
            return;
        }

        if (preg_match('/^\/menu$/i', $text) || preg_match('/^\/start$/i', $text)) {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        if ($text === '/mis_citas') {
            $items = Cita::where('id_usuario', $user->id_usuario)->orderByDesc('fecha')->limit(5)->get();
            $reply = $items->isEmpty() ? 'No tienes citas registradas.' : 'Tus citas:' . $items->map(fn ($x) => "\n• {$x->fecha->format('d/m/Y')} — {$x->motivo}")->implode('');
            $this->send($chatId, $reply, $this->mainMenuKeyboard($user->rol));
            return;
        }

        if ($text === '/mis_pedidos') {
            $items = Pedido::where('id_usuario', $user->id_usuario)->orderByDesc('fecha_pedido')->limit(5)->get();
            $reply = $items->isEmpty() ? 'No tienes pedidos registrados.' : 'Tus últimos pedidos:' . $items->map(fn ($x) => "\n• #{$x->id_pedido}: $ {$x->total}")->implode('');
            $this->send($chatId, $reply, $this->mainMenuKeyboard($user->rol));
            return;
        }

        if ($text === '/agenda_hoy' && in_array(strtolower($user->rol), ['admin', 'tecnico', 'encargado', 'pasante'], true)) {
            $this->send($chatId, 'Citas de hoy: '.Cita::whereDate('fecha', today())->count(), $this->mainMenuKeyboard($user->rol));
            return;
        }

        if ($text === '/resumen' && strtolower($user->rol) === 'admin') {
            $this->send($chatId, 'Resumen de hoy: '.Pedido::whereDate('fecha_pedido', today())->count().' pedido(s) y '.Cita::whereDate('fecha', today())->count().' cita(s).', $this->mainMenuKeyboard($user->rol));
            return;
        }

        $session = $this->getSession($chatId);

        if ($text === 'Volver al menu' || $text === 'Atras') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        if (! isset($session['state'])) {
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        $this->routeState($user, $chatId, $text, $session);
    }

    private function routeState(User $user, string $chatId, string $text, array &$session): void
    {
        $state = $session['state'];

        switch ($state) {
            case 'MAIN_MENU':
                $this->handleMainMenu($user, $chatId, $text, $session);
                break;
            case 'PRODUCTOS':
                $this->handleProductCategory($user, $chatId, $text, $session);
                break;
            case 'PRODUCTO_DETALLE':
                $this->handleProductDetail($user, $chatId, $text, $session);
                break;
            case 'SERVICIO_TECNICO':
                $this->handleServicioTecnico($user, $chatId, $text, $session);
                break;
            case 'CONTACTO':
                $this->handleContacto($user, $chatId, $text, $session);
                break;
            case 'CONTACTO_MENSAJE':
                $this->handleContactoMensaje($user, $chatId, $text, $session);
                break;
            case 'CITA_FECHA':
                $this->handleCitaFecha($user, $chatId, $text, $session);
                break;
            case 'CITA_MOTIVO':
                $this->handleCitaMotivo($user, $chatId, $text, $session);
                break;
            case 'MI_CUENTA':
                $this->handleMiCuenta($user, $chatId, $text, $session);
                break;
            default:
                $this->resetSession($chatId);
                $this->sendMainMenu($chatId, $user->rol);
        }
    }

    private function handleMainMenu(User $user, string $chatId, string $text, array &$session): void
    {
        switch ($text) {
            case '🛒 Ver Productos':
                $session['state'] = 'PRODUCTOS';
                $this->send($chatId, 'Selecciona una categoria:', $this->productCategoriesKeyboard());
                break;
            case '🔧 Servicio Tecnico':
                $session['state'] = 'SERVICIO_TECNICO';
                $this->send($chatId, 'Selecciona una opcion de Servicio Tecnico:', $this->servicioTecnicoKeyboard());
                break;
            case '📱 Mi Cuenta':
                $session['state'] = 'MI_CUENTA';
                $this->send($chatId, 'Selecciona una opcion:', $this->miCuentaKeyboard($user->rol));
                break;
            case '💬 Hablar con Asesor':
                $session['state'] = 'CONTACTO';
                $this->send($chatId, 'Selecciona el tipo de consulta para contactar a un asesor:', $this->contactoTiposKeyboard());
                break;
            default:
                $this->sendMainMenu($chatId, $user->rol);
        }
    }

    private function handleProductCategory(User $user, string $chatId, string $text, array &$session): void
    {
        $map = [
            'PC Escritorio' => 'PC',
            'Laptops' => 'laptops',
            'Duplicadoras' => 'duplicadoras',
            'Tablets' => 'Tablets',
            'Impresoras' => 'impresoras',
            'Tintas' => 'tintas',
        ];

        $categoria = $map[$text] ?? null;
        if (! $categoria) {
            $this->send($chatId, 'Selecciona una categoria valida.', $this->productCategoriesKeyboard());
            return;
        }

        $session['state'] = 'PRODUCTO_DETALLE';
        $session['categoria'] = $categoria;

        $productos = $this->getProductosPorCategoria($categoria);
        if ($productos->isEmpty()) {
            $this->send($chatId, "No hay productos disponibles en esta categoria.", $this->productCategoriesKeyboard());
            $session['state'] = 'PRODUCTOS';
            return;
        }

        $lines = ["Productos en {$text}:\n"];
        foreach ($productos as $p) {
            $stock = (int) $p->unidades > 0 ? (int) $p->unidades . ' en stock' : 'Agotado';
            $lines[] = "• {$p->nombre}\n  Serie: {$p->serie}\n  Precio: $".number_format($p->precio, 2)."\n  Stock: {$stock}\n";
        }

        $keyboard = $productos->map(fn ($p) => ['text' => $p->nombre])->toArray();
        $keyboard[] = ['text' => 'Volver al menu'];

        $this->send($chatId, implode("\n", $lines), $this->buildReplyKeyboard($keyboard));
    }

    private function handleProductDetail(User $user, string $chatId, string $text, array &$session): void
    {
        if ($text === 'Volver al menu') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        $categoria = $session['categoria'] ?? '';
        $productos = $this->getProductosPorCategoria($categoria);
        $producto = $productos->firstWhere('nombre', $text);

        if (! $producto) {
            $this->send($chatId, 'Selecciona un producto de la lista.', $this->buildReplyKeyboard(
                $productos->map(fn ($p) => ['text' => $p->nombre])->push(['text' => 'Volver al menu'])->toArray()
            ));
            return;
        }

        $stock = (int) $producto->unidades > 0 ? (int) $producto->unidades . ' unidades disponibles' : 'Producto agotado';
        $reply = "Detalle del producto:\n\nNombre: {$producto->nombre}\nSerie: {$producto->serie}\nPrecio: $".number_format($producto->precio, 2)."\nStock: {$stock}\n\nSi deseas comprar o consultar, usa la opcion de Contacto en el menu principal.";
        $this->send($chatId, $reply, $this->buildReplyKeyboard([['text' => 'Volver al menu']]));
        $session['state'] = 'PRODUCTOS';
        $session['categoria'] = null;
    }

    private function handleServicioTecnico(User $user, string $chatId, string $text, array &$session): void
    {
        if ($text === 'Volver al menu') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        switch ($text) {
            case 'Horarios':
                $reply = "Horarios de atencion:\n\nLunes a Viernes: 9:00 AM - 7:00 PM\nSabado: 9:00 AM - 5:00 PM\nDomingo: Cerrado\n\nPara agendar una cita, selecciona 'Agendar Cita' en el menu de Servicio Tecnico.";
                $this->send($chatId, $reply, $this->servicioTecnicoKeyboard());
                break;
            case 'Ubicacion':
                $reply = "Ubicacion: La Ecuatoriana - Las Orquideas / Oe9 Martha Bucaram / S37-49 / S37a, Quito (170140), Ecuador.\n\nGoogle Maps: https://maps.app.goo.gl/V8ghM2XY9M4H8v6Z9";
                $this->send($chatId, $reply, $this->servicioTecnicoKeyboard());
                break;
            case 'Contacto':
                $session['state'] = 'CONTACTO';
                $this->send($chatId, 'Selecciona el tipo de consulta:', $this->contactoTiposKeyboard());
                break;
            case 'Agendar Cita':
                $session['state'] = 'CITA_FECHA';
                $this->send($chatId, 'Selecciona la fecha para tu cita (formato YYYY-MM-DD). Ejemplo: 2026-08-20\n\nEscribe la fecha o usa el boton de Volver.', $this->buildReplyKeyboard([['text' => 'Volver al menu']]));
                break;
            default:
                $this->send($chatId, 'Opcion no valida. Selecciona una opcion del menu.', $this->servicioTecnicoKeyboard());
        }
    }

    private function handleContacto(User $user, string $chatId, string $text, array &$session): void
    {
        if ($text === 'Volver al menu') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        $tipos = [
            'Soporte Tecnico' => 'Consulta sobre soporte tecnico, reparaciones o mantenimiento.',
            'Informacion Comercial' => 'Consulta sobre productos, precios o compras.',
            'Otro' => 'Otra consulta general.',
        ];

        if (! isset($tipos[$text])) {
            $this->send($chatId, 'Selecciona un tipo de consulta valido.', $this->contactoTiposKeyboard());
            return;
        }

        $session['contacto_tipo'] = $text;
        $session['state'] = 'CONTACTO_MENSAJE';
        $this->send($chatId, "Has seleccionado: {$text}\n\nAhora escribe tu mensaje. Un asesor se pondra en contacto contigo.\n\nO presiona 'Volver al menu' para cancelar.", $this->buildReplyKeyboard([['text' => 'Volver al menu']]));
    }

    private function handleContactoMensaje(User $user, string $chatId, string $text, array &$session): void
    {
        if ($text === 'Volver al menu') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        $tipo = $session['contacto_tipo'] ?? 'General';
        $mensaje = "[{$tipo}] {$text}";

        try {
            \App\Models\Contacto::create([
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->usuario,
                'correo' => $user->correo,
                'compania' => 'Telegram',
                'mensaje' => $mensaje,
            ]);
            $this->send($chatId, 'Tu mensaje ha sido registrado. Un asesor te contactara pronto.', $this->servicioTecnicoKeyboard());
        } catch (\Throwable $e) {
            report($e);
            $this->send($chatId, 'No fue posible enviar el mensaje. Intenta mas tarde.', $this->servicioTecnicoKeyboard());
        }
        $session['state'] = 'SERVICIO_TECNICO';
        $session['contacto_tipo'] = null;
    }

    private function handleCitaFecha(User $user, string $chatId, string $text, array &$session): void
    {
        if ($text === 'Volver al menu') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
            $this->send($chatId, 'Formato invalido. Usa YYYY-MM-DD (ejemplo: 2026-08-20).', $this->buildReplyKeyboard([['text' => 'Volver al menu']]));
            return;
        }

        $fecha = \Carbon\Carbon::createFromFormat('Y-m-d', $text);
        if ($fecha->isPast()) {
            $this->send($chatId, 'La fecha debe ser futura.', $this->buildReplyKeyboard([['text' => 'Volver al menu']]));
            return;
        }

        $session['cita_fecha'] = $text;
        $session['state'] = 'CITA_MOTIVO';
        $this->send($chatId, "Fecha seleccionada: {$text}\n\nAhora escribe el motivo de la cita (ejemplo: mantenimiento, reparacion, etc.):", $this->buildReplyKeyboard([['text' => 'Volver al menu']]));
    }

    private function handleCitaMotivo(User $user, string $chatId, string $text, array &$session): void
    {
        if ($text === 'Volver al menu') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        $fecha = $session['cita_fecha'] ?? '';
        $motivo = trim($text);

        if ($motivo === '') {
            $this->send($chatId, 'El motivo no puede estar vacio.', $this->buildReplyKeyboard([['text' => 'Volver al menu']]));
            return;
        }

        try {
            $fecha = $session['cita_fecha'] ?? '';
            Cita::create([
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->usuario,
                'apellido' => '-',
                'correo' => $user->correo,
                'fecha' => $fecha,
                'telefono' => $user->telegramAccounts->first()?->telefono ?? 'No registrado',
                'motivo' => $motivo,
            ]);
            $this->send($chatId, "Cita registrada para el {$fecha} por: {$motivo}\n\nRecibiras confirmacion por correo.", $this->mainMenuKeyboard($user->rol));
        } catch (\Throwable $e) {
            report($e);
            $this->send($chatId, 'No fue posible registrar la cita. Intenta mas tarde.', $this->mainMenuKeyboard($user->rol));
        }

        $session['state'] = null;
        $session['cita_fecha'] = null;
    }

    private function handleMiCuenta(User $user, string $chatId, string $text, array &$session): void
    {
        if ($text === 'Volver al menu') {
            $this->resetSession($chatId);
            $this->sendMainMenu($chatId, $user->rol);
            return;
        }

        if ($text === 'Mis Citas') {
            $items = Cita::where('id_usuario', $user->id_usuario)->orderByDesc('fecha')->limit(5)->get();
            $reply = $items->isEmpty() ? 'No tienes citas registradas.' : 'Tus citas:' . $items->map(fn ($x) => "\n• {$x->fecha->format('d/m/Y')} — {$x->motivo}")->implode('');
            $this->send($chatId, $reply, $this->mainMenuKeyboard($user->rol));
        } elseif ($text === 'Mis Pedidos') {
            $items = Pedido::where('id_usuario', $user->id_usuario)->orderByDesc('fecha_pedido')->limit(5)->get();
            $reply = $items->isEmpty() ? 'No tienes pedidos registrados.' : 'Tus últimos pedidos:' . $items->map(fn ($x) => "\n• #{$x->id_pedido}: $ {$x->total}")->implode('');
            $this->send($chatId, $reply, $this->mainMenuKeyboard($user->rol));
        } else {
            $this->send($chatId, 'Opcion no valida.', $this->mainMenuKeyboard($user->rol));
        }

        $session['state'] = null;
    }

    private function sendMainMenu(string $chatId, string $role): void
    {
        $text = "Bienvenido a L&M PC Computadoras.\n\nSelecciona una opcion para continuar:";
        $this->send($chatId, $text, $this->mainMenuKeyboard($role));
        $this->setSession($chatId, ['state' => 'MAIN_MENU']);
    }

    private function mainMenuKeyboard(string $role): array
    {
        $keyboard = [
            ['🛒 Ver Productos'],
            ['🔧 Servicio Tecnico'],
            ['📱 Mi Cuenta'],
            ['💬 Hablar con Asesor'],
        ];

        return $this->buildReplyKeyboard($keyboard);
    }

    private function productCategoriesKeyboard(): array
    {
        return $this->buildReplyKeyboard([
            ['PC Escritorio'],
            ['Laptops'],
            ['Duplicadoras'],
            ['Tablets'],
            ['Impresoras'],
            ['Tintas'],
            ['Volver al menu'],
        ]);
    }

    private function servicioTecnicoKeyboard(): array
    {
        return $this->buildReplyKeyboard([
            ['Horarios'],
            ['Ubicacion'],
            ['Contacto'],
            ['Agendar Cita'],
            ['Volver al menu'],
        ]);
    }

    private function contactoTiposKeyboard(): array
    {
        return $this->buildReplyKeyboard([
            ['Soporte Tecnico'],
            ['Informacion Comercial'],
            ['Otro'],
            ['Volver al menu'],
        ]);
    }

    private function miCuentaKeyboard(string $role): array
    {
        return $this->buildReplyKeyboard([
            ['Mis Citas'],
            ['Mis Pedidos'],
            ['Volver al menu'],
        ]);
    }

    private function buildReplyKeyboard(array $rows): array
    {
        return [
            'keyboard' => $rows,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            'selective' => true,
        ];
    }

    public function handleCallback(array $callback): void
    {
        $chatId = (string) data_get($callback, 'message.chat.id');
        if (! $chatId) {
            return;
        }
        $data = data_get($callback, 'data');
        $this->answerCallback($callback);
        $this->send($chatId, "Callback recibido: {$data}\n\nEsta funcion se expandira en el futuro.", $this->mainMenuKeyboard('usuario'));
    }

    private function answerCallback(array $callback): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return;
        }
        $callbackQueryId = data_get($callback, 'id');
        if ($callbackQueryId) {
            Http::timeout(8)->post("https://api.telegram.org/bot{$token}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => 'Procesado',
            ]);
        }
    }

    private function start(string $code, string $chatId, string $fromId, string $username): void
    {
        if ($code === '') {
            $this->send($chatId, 'Para vincularte, genera un código en el portal y envía /vincular CÓDIGO.');
            return;
        }
        $link = TelegramLinkCode::where('codigo_hash', hash('sha256', strtoupper($code)))->whereNull('usado_en')->where('expira_en', '>', now())->first();
        if (! $link) {
            $this->send($chatId, 'Código inválido, usado o vencido. Genera otro código en el portal.');
            return;
        }
        if (TelegramAccount::where('id_usuario', $link->id_usuario)->exists() || TelegramAccount::where('telegram_user_id', $fromId)->exists()) {
            $this->send($chatId, 'Esta cuenta de usuario o Telegram ya está vinculada. Desvincúlala primero desde el portal.');
            return;
        }
        TelegramAccount::create(['id_usuario' => $link->id_usuario, 'telegram_chat_id' => $chatId, 'telegram_user_id' => $fromId, 'username' => $username ?: null, 'ultima_actividad_en' => now()]);
        $link->update(['usado_en' => now()]);
        $this->send($chatId, 'Cuenta vinculada. Para verificar tu número, usa el botón “Compartir mi contacto” y después escribe /menu.', [['text' => 'Compartir mi contacto', 'request_contact' => true]]);
    }

    private function saveContact(?TelegramAccount $account, array $contact, string $fromId, string $chatId): void
    {
        if (! $account || (string) data_get($contact, 'user_id') !== $fromId) {
            $this->send($chatId, 'Por seguridad, solo acepto el contacto de la misma cuenta de Telegram vinculada.');
            return;
        }
        $phone = preg_replace('/[^0-9+]/', '', (string) data_get($contact, 'phone_number'));
        if (strlen(str_replace('+', '', $phone)) < 8 || strlen($phone) > 20) {
            $this->send($chatId, 'El número recibido no es válido.');
            return;
        }
        $account->update(['telefono' => $phone, 'telefono_verificado_en' => now()]);
        $this->send($chatId, 'Número verificado. Escribe /menu para ver tus acciones disponibles.');
    }

    private function getProductosPorCategoria(string $categoria)
    {
        $map = [
            'PC' => ['PC'],
            'laptops' => ['ASUS', 'lenovo', 'ominibook', 'msi', 'DELL'],
            'duplicadoras' => ['DUPLICADORACD', 'DUPLICADORADVD', 'DUPLICADORABLU'],
            'Tablets' => ['Tablets'],
            'impresoras' => ['EPSON', 'CANON'],
            'tintas' => ['TINTA100', 'TINTA1000'],
        ];

        $categorias = $map[$categoria] ?? [$categoria];
        return Producto::whereIn('categoria', $categorias)->orderBy('nombre')->get();
    }

    private function send(string $chatId, string $text, ?array $keyboard = null): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            return;
        }
        $body = [
            'chat_id' => $chatId,
            'text' => mb_substr($text, 0, 4000),
            'parse_mode' => 'HTML',
        ];
        if ($keyboard) {
            $body['reply_markup'] = $keyboard;
        }
        Http::timeout(8)->post("https://api.telegram.org/bot{$token}/sendMessage", $body);
    }

    private function getSession(string $chatId): array
    {
        if (! isset($this->userSessions[$chatId])) {
            $this->userSessions[$chatId] = [];
        }
        return $this->userSessions[$chatId];
    }

    private function setSession(string $chatId, array $data): void
    {
        $this->userSessions[$chatId] = $data;
    }

    private function resetSession(string $chatId): void
    {
        $this->userSessions[$chatId] = [];
    }
}
