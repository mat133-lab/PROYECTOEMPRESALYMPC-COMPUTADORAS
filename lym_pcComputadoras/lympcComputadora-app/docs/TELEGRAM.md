# Integración segura con Telegram

## Flujo de vinculación

1. El usuario inicia sesión en L&M PC y abre `/telegram`.
2. El portal genera un código aleatorio de un único uso, válido por 10 minutos.
3. En un chat privado con el bot, envía `/vincular CODIGO`.
4. El bot vincula el `chat_id` y pide compartir el contacto. Solo acepta un contacto cuyo `user_id` es el mismo del remitente, por lo que no se puede registrar el número de otra persona.

## Interfaz de botones

El bot funciona exclusivamente por botones (teclado personalizado). El usuario no necesita escribir texto libre. Las opciones principales son:

- 🛒 Ver Productos: navega por categorías y productos con precio y stock.
- 🔧 Servicio Técnico: horarios, ubicación, contacto y agendamiento de citas.
- 📱 Mi Cuenta: consulta de citas y pedidos.
- 💬 Hablar con Asesor: envía una solicitud de atención personalizada.

Para volver al menú principal, usa el botón **Volver al menu** o el comando `/menu`.

## Comandos disponibles

- `/start` o `/menu` — muestra el menú principal con botones.
- `/vincular CODIGO` — vincula tu cuenta de Telegram con el portal.
- `/mis_citas` — muestra tus próximas citas.
- `/mis_pedidos` — muestra tus últimos pedidos.
- `/agenda_hoy` — total de citas de hoy (solo personal: admin, técnico, encargado, pasante).
- `/resumen` — resumen operativo diario (solo admin).

## Configuración

Agrega al archivo `.env` valores generados para tu bot:

```dotenv
TELEGRAM_BOT_TOKEN=123456:token-entregado-por-botfather
TELEGRAM_WEBHOOK_SECRET=una-cadena-aleatoria-larga-de-al-menos-32-caracteres
TELEGRAM_BOT_USERNAME=TuBotSinArroba
```

Después ejecuta:

```powershell
php artisan migrate
php artisan config:clear
```

Cuando el sitio tenga una URL HTTPS pública, registra el webhook. Sustituye los valores y conserva el mismo secreto de `.env`:

```powershell
$token = 'TU_TOKEN'
$secret = 'TU_SECRETO'
Invoke-RestMethod -Method Post -Uri "https://api.telegram.org/bot$token/setWebhook" -Body @{ url = 'https://tu-dominio.com/api/telegram/webhook'; secret_token = $secret; allowed_updates = '["message","callback_query"]'; drop_pending_updates = 'true' }
```

Telegram no puede entregar webhooks a `localhost`. Para desarrollo usa temporalmente un túnel HTTPS (por ejemplo Cloudflare Tunnel) y registra su URL; no expongas XAMPP directamente a Internet.

### Sin ngrok: modo polling local

Si trabajas con XAMPP/MySQL local y no deseas usar ngrok, elimina antes cualquier webhook que hayas registrado y deja este proceso abierto:

```powershell
# Solo si antes configuraste webhook:
Invoke-RestMethod -Method Post -Uri "https://api.telegram.org/botTU_TOKEN/deleteWebhook" -Body @{ drop_pending_updates = 'false' }

php artisan telegram:poll --daemon
```

Telegram y `getUpdates` no funcionan simultáneamente con un webhook. El modo polling sirve mientras tu PC y este proceso estén encendidos; para producción debes desplegar Laravel en un servidor HTTPS público y usar el webhook.

En BotFather registra estos comandos visibles: `/start`, `/menu`, `/vincular`, `/mis_citas`, `/mis_pedidos`, `/agenda_hoy` y `/resumen`.

## Protecciones incluidas

- Secreto `X-Telegram-Bot-Api-Secret-Token` obligatorio en cada webhook.
- Control de duplicados de `update_id`, para reintentos del proveedor.
- Código aleatorio almacenado solo como hash SHA-256, con expiración y un uso.
- Una cuenta de Telegram y un usuario del portal solo pueden tener una vinculación activa.
- Control de origen, token CSRF propio de la sesión legacy y límites de solicitudes al generar/desvincular.
- Chat privado obligatorio; se rechazan mensajes de grupos.

## Acciones desde el bot

- Crear citas: el usuario selecciona fecha y motivo por botones; se registra directamente.
- Crear contactos: el usuario selecciona el tipo de consulta y escribe un mensaje breve; se registra directamente.
- Consultar productos: se muestra nombre, serie, precio y stock.
- Consultar pedidos y citas: se listan los últimos 5 registros.
- Hablar con asesor: se envía una solicitud de contacto que el equipo puede atender desde el panel.

```powershell
php artisan telegram:poll
php artisan telegram:poll --daemon
php artisan queue:work --sleep=3 --tries=3
```
