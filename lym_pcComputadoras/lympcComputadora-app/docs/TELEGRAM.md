# Integración segura con Telegram

## Flujo de vinculación

1. El usuario inicia sesión en L&M PC y abre `/telegram`.
2. El portal genera un código aleatorio de un único uso, válido por 10 minutos.
3. En un chat privado con el bot, envía `/vincular CODIGO`.
4. El bot vincula el `chat_id` y pide compartir el contacto. Solo acepta un contacto cuyo `user_id` es el mismo del remitente, por lo que no se puede registrar el número de otra persona.

## Interfaz y comandos

La interfaz principal del bot son los botones inline: cada selección abre la siguiente pantalla o ejecuta la acción correspondiente. Los comandos son accesos directos opcionales. También puede interpretar texto libre con el proveedor de IA seleccionado en `AI_PROVIDER`. Las opciones principales son:

- 🛒 Ver Productos: navega por categorías y productos con precio y stock.
- 🔧 Servicio Técnico: horarios, ubicación y agendamiento de citas.
- 📱 Mi Cuenta: consulta de citas y pedidos.
- 💬 Hablar con Asesor: envía una solicitud de atención personalizada.
- 🛍 Carrito: permite solicitar al asesor los productos seleccionados.

La confirmación del carrito crea una solicitud de contacto para que un asesor verifique disponibilidad y precio. No cobra, no crea un pedido pagado y no descuenta inventario. La venta se completa posteriormente por el canal acordado con el asesor o por el checkout del sitio.

Las consultas y solicitudes de pedido quedan guardadas en la tabla `contacto`. El sistema envía un aviso a los correos válidos de las cuentas con rol `asistente`, con la dirección del cliente como `Reply-To`. El asesor también puede abrir **Solicitudes de clientes** desde su panel y usar **Responder por correo**. Si falla el envío, la solicitud sigue guardada en la bandeja y el bot lo informa.

Para volver al menú principal, usa el botón **Menú principal** o el comando `/menu`.

## Comandos disponibles

- `/start` o `/menu` — muestra el menú principal con botones.
- `/vincular CODIGO` — vincula tu cuenta de Telegram con el portal.
- `/mis_citas` — muestra tus últimas citas.
- `/mis_pedidos` — muestra tus últimos pedidos.
- `/ayuda` — muestra los comandos y funciones disponibles para tu rol.
- `/confirmar CODIGO` — confirma una acción propuesta por IA; el código dura 10 minutos.
- `/agenda_hoy` — agenda de hoy (admin, técnico, encargado o pasante).
- `/resumen` — conteo diario de citas, solicitudes de contacto y pedidos registrados (solo admin).

Los comandos de consulta también pueden escribirse con texto normal, por ejemplo: “¿cuáles son mis pedidos?”. Las acciones propuestas por IA no se ejecutan directamente: el bot presenta un resumen y botones para confirmar o cancelar. `/confirmar CODIGO` es una alternativa opcional.

El bot exige vincular la cuenta antes de usar el menú o las funciones privadas. Si responde que la cuenta ya está vinculada, no repitas `/vincular`: usa `/start`. Para vincular otra cuenta del portal, primero desvincula la anterior desde el perfil web.

## Configuración

Agrega al archivo `.env` valores generados para tu bot:

```dotenv
TELEGRAM_BOT_TOKEN=123456:token-entregado-por-botfather
TELEGRAM_WEBHOOK_SECRET=una-cadena-aleatoria-larga-de-al-menos-32-caracteres
TELEGRAM_BOT_USERNAME=TuBotSinArroba
AI_PROVIDER=nvidia
NVIDIA_API_KEY=tu-clave-de-NVIDIA
NVIDIA_MODEL=openai/gpt-oss-20b
NVIDIA_BASE_URL=https://integrate.api.nvidia.com/v1
```

No compartas ni subas claves reales. Si falta la clave del proveedor seleccionado o el proveedor no responde, los comandos y botones siguen disponibles, pero el texto libre no podrá interpretarse.

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

En BotFather registra estos comandos visibles: `/start`, `/menu`, `/vincular`, `/mis_citas`, `/mis_pedidos`, `/ayuda`, `/confirmar`, `/agenda_hoy` y `/resumen`.

## Protecciones incluidas

- Secreto `X-Telegram-Bot-Api-Secret-Token` obligatorio en cada webhook.
- Control de duplicados de `update_id`, para reintentos del proveedor.
- Código aleatorio almacenado solo como hash SHA-256, con expiración y un uso.
- Una cuenta de Telegram y un usuario del portal solo pueden tener una vinculación activa.
- Control de origen, token CSRF propio de la sesión legacy y límites de solicitudes al generar/desvincular.
- Chat privado obligatorio; se rechazan mensajes de grupos.

## Acciones desde el bot

- Crear citas: se puede usar el flujo de botones o describir la cita por texto; las acciones propuestas por IA requieren confirmación.
- Crear contactos: se puede usar el flujo de asesor o describir la consulta por texto; las acciones propuestas por IA requieren confirmación.
- Consultar productos: se muestra nombre, serie, precio y stock.
- Consultar pedidos y citas: se listan los últimos 5 registros.
- Hablar con asesor: se envía una solicitud de contacto que el equipo puede atender desde el panel.
- Gestionar productos: admin y técnico pueden proponer altas, cambios o eliminaciones por texto; cada operación requiere pulsar el botón de confirmación o usar `/confirmar CODIGO`.
- Solicitar productos desde el carrito: crea una solicitud de pedido en Contacto, no una venta ni un pedido pagado.
- Los avisos de consultas/pedidos se envían a las cuentas con rol `asistente`; se puede responder al cliente desde el enlace del correo o desde **Solicitudes de clientes** en el panel de asistente.

```powershell
# Elige una sola opción; deja esa terminal abierta mientras pruebas el bot.
php artisan telegram:poll --timeout=25
# O mantén el polling en ejecución hasta detenerlo con Ctrl+C:
php artisan telegram:poll --daemon
```

`Polling activo` solo indica que arrancó el comando. Mantén una sola instancia de `telegram:poll`; dos procesos reciben un error 409. Si la consulta falla, revisa el detalle que imprime (por ejemplo, un error DNS o de conexión). El polling requiere acceso HTTPS a `api.telegram.org` y no se ejecuta al mismo tiempo que un webhook. No hace falta `queue:work` cuando `QUEUE_CONNECTION=sync`.

### Diagnóstico cuando el bot no responde

- `Polling activo` solo indica que arrancó y la terminal permanece en silencio mientras espera. Al recibir un mensaje o botón, la versión actual imprime `Actualización ... procesada`, sin mostrar el contenido ni el chat.
- `php artisan telegram:poll` hace una consulta de espera larga y luego termina. `php artisan telegram:poll --daemon` sigue consultando hasta detenerlo con `Ctrl+C`. No ejecutes ambos a la vez ni abras varias terminales con polling.
- Un error `409 Conflict` significa que hay otro proceso usando `getUpdates` o un webhook activo. Detén los procesos de polling duplicados y comprueba el webhook antes de iniciar uno.
- Si Telegram recibe una actualización pero rechaza la respuesta del bot, el comando ahora muestra el método y el error HTTP de Telegram. El token se oculta en ese mensaje.
- Si no aparece ningún error y el bot sigue sin contestar, confirma que escribiste al mismo bot configurado en `TELEGRAM_BOT_TOKEN` y vuelve a probar `/start` con una sola instancia activa.
