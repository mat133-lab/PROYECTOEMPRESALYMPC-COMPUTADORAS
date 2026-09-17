# Diagrama de flujo del chatbot de Telegram — L&M PC

Archivos incluidos en esta carpeta:

| Archivo | Uso |
| --- | --- |
| `flujo-chatbot.bpmn` | **Archivo principal.** BPMN 2.0 listo para importar en Bizagi Modeler (`File → Import → BPMN 2.0`). |
| `flujo-chatbot.svg` | Versión visual del mismo flujo, útil para previsualizar o pegar en documentación. |
| `README.md` | Este archivo: descripción paso a paso. |

## Cómo abrir en Bizagi

1. Abre **Bizagi Modeler**.
2. `File` → `Import` → `BPMN 2.0…`
3. Selecciona `flujo-chatbot.bpmn`.
4. Bizagi creará el diagrama con todos los nodos, gateways y conexiones.

> Si Bizagi te pregunta el tipo de proceso, elige **Process** (no Pool/Collaboration).

## Filosofía del flujo (estilo call center tipo Movistar)

- **Ningún campo de texto libre** salvo el comando `/vincular CÓDIGO` y el botón de "Compartir mi contacto" (que el propio Telegram gestiona).
- Todo lo demás se selecciona tocando un botón.
- Cada mensaje lleva debajo una `InlineKeyboardMarkup` con 1‑8 botones como máximo.
- La pantalla anterior **se reemplaza** con `editMessageText` (no se acumulan mensajes).

## Ramas principales del menú

```
🏠 Menú principal
├── 🛒 Ver Productos
│   ├── Categoría (Laptops, PCs, Tablets, Impresoras, Insumos, Duplicadores)
│   │   └── Lista paginada de 5 productos
│   │       ├── Agregar al carrito
│   │       └── Consultar este producto (crea Contacto con mención)
│   └── Cambiar de categoría (botones siempre visibles)
├── 🔧 Servicio Técnico
│   ├── 🕐 Horarios
│   ├── 📍 Ubicación
│   ├── 📅 Agendar Cita
│   │   ├── Día (14 días futuros en botones)
│   │   ├── Motivo (8 motivos predefinidos)
│   │   └── Crear Cita (INSERT)
│   └── 🆘 Hablar con soporte
├── 📱 Mi Cuenta
│   ├── Resumen (rol, total citas, total pedidos)
│   ├── Mis Citas (botón por cita → detalle)
│   └── Mis Pedidos (botón por pedido → detalle)
└── 💬 Hablar con Asesor
    ├── 8 motivos de consulta predefinidos
    └── Crear Contacto (INSERT) + email al usuario
```

## Estados / session

`TelegramSession` guarda por chat:
- `state`: estado actual (compatibilidad).
- `categoria`: categoría elegida.
- `cita_fecha`: fecha elegida.
- `payload.cart`: array persistente `{id_producto: cantidad}` para el carrito.
- `payload.asesor_producto`: id del producto cuando la consulta nace de un producto.
- `payload.pagina`: índice de paginación.

## Equivalencia Telegram ↔ BPMN

| Elemento BPMN | Implementación en el bot |
| --- | --- |
| `StartEvent` | `update.message` o `update.callback_query` recibido. |
| `ExclusiveGateway` (¿?) | `match($action)` en `TelegramBotService::dispatch()`. |
| `User Task` (mostrar X) | `editOrSend()` con un `inline_keyboard` específico. |
| `Sub-Process` | Sub-flujo de Cita o Asesor (pantallas anidadas). |
| `EndEvent` | Mensaje final con un único botón "🏠 Menú principal". |
| `Sequence Flow` (condicional) | Botón `callback_data` que el usuario toca. |
