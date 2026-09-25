# L&M PC Computadoras

## Creador
Cisneros Mateo

## Descripción del Proyecto

L&M PC Computadoras es una plataforma web integral para la gestión de una empresa de tecnología especializada en computadoras, laptops, accesorios y servicios técnicos. El sistema combina un **módulo legacy PHP** monolítico con un **wrapper moderno en Laravel 13**, integrando además un **bot de Telegram** con interfaz de botones y un **widget de chat web con IA**.

---

## Características Principales

### Catálogo de Productos
- Gestión de productos por categorías: laptops, PCs, tablets, impresoras, insumos, duplicadores
- Control de inventario (unidades, precios, series, imágenes)
- Carrito de compras con pasarela de pago
- Generación de PDFs de pedidos

### Gestión de Citas
- Sistema de agendamiento de citas técnicas
- Calendarios interactivos para usuarios, administradores y pasantes
- Búsqueda, edición y eliminación de citas
- API de horarios por rango de fechas

### Administración de Usuarios
- Registro con verificación de correo electrónico
- Roles: Usuario, Administrador, Técnico, Pasante, Asistente
- Recuperación y cambio de contraseña
- Vinculación de cuentas de Telegram

### Chat en Línea con IA
- Widget flotante de chat para usuarios web
- Clasificador de intenciones mediante red neuronal Python
- Escalamiento a asistente humano
- Sistema de calificación de conversaciones (1-10)

### Bot de Telegram
- Navegación mediante botones personalizados (sin texto libre)
- Búsqueda de productos por categorías
- Programación de citas desde Telegram
- Envío de mensajes de contacto
- Acciones confirmadas por personal administrativo
- Comandos: `/start`, `/menu`, `/vincular`, `/mis_citas`, `/mis_pedidos`, `/agenda_hoy`, `/resumen`

### Panel Administrativo
- Dashboard con gráficos y estadísticas
- Gestión de productos, citas, pedidos y usuarios
- Soporte técnico y contactos
- Notificaciones del sistema

---

## Arquitectura y Stack Tecnológico

### Componentes Principales

```
lymPCComputadoras/
├── lymptcComp/                          # Módulo Legacy PHP (XAMPP)
│   ├── php/                             # Controladores y vistas monolíticas
│   ├── includes/                        # Conexión BD, helpers, configuración
│   ├── js/                              # JavaScript del lado cliente
│   ├── python/                          # Chatbot IA (Python)
│   └── vendor/                          # Dependencias Composer (PHPMailer, Dompdf)
│
└── lym_pcComputadoras/
    └── lmpcComputadora-app/             # Aplicación Laravel 13
        ├── app/
        │   ├── Http/Controllers/        # Controladores Laravel
        │   ├── Models/                  # Modelos Eloquent
        │   ├── Services/                # Lógica de negocio (Telegram, IA)
        │   └── Support/Legacy/          # Runtime para PHP legacy
        ├── routes/
        │   ├── web.php                  # Rutas web (proxying legacy)
        │   └── api.php                  # APIs REST
        ├── database/
        │   ├── migrations/              # Migraciones Laravel
        │   └── legacy/                  # Dump SQL legacy
        └── public/                      # Assets públicos (CSS, JS)
```

### Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|-----------|---------|
| **Servidor Web** | XAMPP (Apache) | - |
| **Base de Datos Legacy** | MariaDB | 10.x |
| **Base de Datos Laravel** | SQLite | 3.x |
| **Backend Legacy** | PHP | 8.2+ |
| **Backend Moderno** | Laravel | 13.8+ |
| **Frontend** | HTML, CSS, JavaScript, Tailwind CSS | v4 |
| **Build Tool** | Vite | 8.x |
| **Bot Telegram** | PHP (Laravel) + Webhook/Polling | - |
| **IA Chatbot** | Python (Red Neuronal) | 3.x |
| **PDF** | Dompdf | 3.1 |
| **Email** | PHPMailer | 7.1 |
| **IA Providers** | Gemini / Qwen / Groq | Configurable |

---

## Módulo Legacy PHP (`lympcComp/`)

El módulo legacy es la base del sistema, desarrollado en PHP puro bajo el patrón monolítico donde cada archivo actúa como controlador y vista simultáneamente.

### Estructura de Directorios

```
lympcComp/
├── php/                    # Scripts principales (controladores + vistas)
├── includes/               # Conexiones, helpers y configuración
│   ├── db.php              # PDO a MariaDB
│   ├── chat_helpers.php    # Lógica del chat web
│   ├── chat_widget.php     # Widget HTML del chat
│   ├── mail_config.php     # Configuración PHPMailer
│   └── ...
├── js/                     # JavaScript cliente
├── css/                    # Hoja de estilos
├── img/                    # Imágenes estáticas
├── uploads/                # Archivos subidos por usuarios
├── docs/                   # Documentos de pasantes
├── python/                 # Chatbot neuronal Python
├── vendor/                 # Composer dependencies
└── BASE DE DATOS/          # Dump SQL de lmpc_bd
```

### Autenticación y Sesiones

- Sesiones nativas PHP (`$_SESSION`)
- Roles almacenados en `$_SESSION['rol']`
- Contraseñas hasheadas con `password_hash()` / `password_verify()`
- Verificación de correo electrónico con tokens
- Aislamiento de sesiones mediante cookie `LEGACYSESSID` para evitar conflictos con Laravel

### Roles del Sistema

| Rol | Descripción | Dashboard |
|-----|-------------|-----------|
| `usuario` | Cliente final | `dashboard.php` |
| `admin` | Administrador | `dashboardadmin.php` |
| `tecnico` | Personal técnico | `dashboard_tecnico.php` |
| `pasante` | Practicante | `dashboard_pasante.php` |
| `asistente` | Asistente administrativo | `dashboard_asistente.php` |

### Flujo de Autenticación

1. **Registro** (`registro.php`): Captura datos, genera token de verificación, envía correo
2. **Verificación** (`verify_email.php`): Valida token y activa cuenta
3. **Login** (`login.php`): Verifica credenciales y estado de verificación
4. **Recuperación** (`reset_password.php`): Token temporal por correo
5. **Logout** (`logout.php`): Destruye sesión

### Gestión de Productos

Archivos: `productos.php`, `guardar_producto.php`, `editar.php`, `eliminar.php`

- CRUD completo de productos
- Categorías: ASUS, HP/Dell, Lenovo, MSI, Omnibook, PC, Tablets, Tinta 100, Tinta 1000, Canon, Epson, Duplicadoras CD/DVD/Blu-ray
- Campos: nombre, serie, fecha, unidades, precio, imagen, categoría, id_soporte

### Carrito de Compras

Archivo: `cart_action.php`

- Acciones: `add` (agregar), `clear` (limpiar), `purchase` (comprar)
- Validación de stock
- Creación de pedido con detalles

### Gestión de Citas

Archivos:
- `horario.php`, `horarioadmin.php`, `horario_pasante.php` - Calendarios
- `guardar_cita.php` - Crear cita
- `buscar_cita.php` - Buscar citas
- `eliminar_cita.php` - Eliminar cita
- `editar.php` - Editar cita
- `gestion_citas.php`, `gestion_citasU.php` - Administración

Campos de cita: nombre, apellido, correo, cédula, RUC, archivos adjuntos, acción sensible, fecha, teléfono, motivo.

### Gestión de Pedidos

Archivos: `pedidos.php`, `generar_pedido_pdf.php`, `procesar_compra.php`

- Listado de pedidos por usuario
- PDF generado con Dompdf
- Detalles de productos por pedido

### Contacto y Soporte

Archivos: `contacto.php`, `contactoadmin.php`, `contacto_tecnico.php`, `soporteadmin.php`

- Formulario de contacto con datos de empresa
- Gestión de mensajes de soporte
- Respuestas desde panel admin

### Perfiles de Usuario

Archivos: `perfilUsu.php`, `perfiladmin.php`, `perfiltecnico.php`, `perfilpasante.php`, `perfilasistente.php`

- Visualización y edición de datos personales
- Subida de foto de perfil (`subir_foto.php`)
- Historial de citas y pedidos

### Documentos y Reportes

Archivos: `subirpasante.php`, `subireport.php`, `lecturadoc.php`, `pasante_upload.php`

- Subida de documentos por pasantes
- Lector de documentos integrado
- Almacenamiento en `lympcComp/docs/`

### Calendarios y Gráficos

- `calendar.js`, `calendario.js` - FullCalendar
- `horario-calendario.js`, `horario-calendario-admin.js` - Calendarios específicos
- `grafico_asistente.js`, `grafica.php` - Gráficos estadísticos

---

## Módulo Laravel (`lym_pcComputadoras/lympcComputadora-app/`)

El módulo Laravel actúa como contenedor moderno que envuelve y extiende el sistema legacy, proporcionando APIs REST, gestión del bot de Telegram, y servicios de inteligencia artificial.

### Estructura del Proyecto Laravel

```
lympcComputadora-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LegacyController.php              # Proxy de scripts legacy
│   │   │   ├── LegacyFileController.php          # Servicio seguro de archivos legacy
│   │   │   ├── TelegramWebhookController.php     # Receptor de webhooks Telegram
│   │   │   ├── TelegramLinkController.php        # Vinculación/desvinculación Telegram
│   │   │   └── Api/
│   │   │       └── ProductoController.php        # API REST de productos
│   │   ├── Middleware/
│   │   │   └── VerifyCsrfToken.php               # Exención CSRF para endpoints legacy
│   │   └── Resources/
│   │       └── ProductoResource.php              # Transformador JSON de productos
│   ├── Models/
│   │   ├── User.php / Usuario.php                # Modelo de usuario (tabla usuarios)
│   │   ├── Cita.php                              # Modelo de citas
│   │   ├── Pedido.php                            # Modelo de pedidos
│   │   ├── DetallePedido.php                     # Modelo de detalles de pedido
│   │   ├── Producto.php                          # Modelo de productos
│   │   ├── Soporte.php                           # Modelo de soporte
│   │   ├── Contacto.php                          # Modelo de contactos
│   │   ├── Notificacion.php                      # Modelo de notificaciones
│   │   ├── ChatConversacion.php                  # Modelo de conversaciones de chat
│   │   ├── ChatMensaje.php                       # Modelo de mensajes de chat
│   │   ├── TelegramAccount.php                   # Cuentas Telegram vinculadas
│   │   ├── TelegramLinkCode.php                  # Códigos de vinculación
│   │   ├── TelegramActionRequest.php             # Solicitudes de acciones desde Telegram
│   │   └── TelegramActionAudit.php               # Auditoría de acciones Telegram
│   ├── Services/
│   │   ├── TelegramBotService.php                # Lógica principal del bot (menús, estado)
│   │   ├── TelegramActionService.php             # Ejecución de acciones de negocio
│   │   └── AiIntentService.php                   # Interpretación de intenciones con IA
│   ├── Support/Legacy/
│   │   └── LegacyRuntime.php                     # Sandbox para ejecutar PHP legacy
│   ├── Console/Commands/
│   │   └── PollTelegramUpdates.php               # Comando Artisan para polling Telegram
│   └── Mail/
│       └── TelegramActionExecutedMail.php        # Notificación por correo de acciones
├── routes/
│   ├── web.php                                   # Rutas web (legacy proxy, Telegram linking)
│   └── api.php                                   # Rutas API (productos, webhook)
├── database/
│   ├── migrations/                               # Migraciones Laravel
│   └── legacy/
│       └── lmpc_bd.sql                           # Dump SQL de base de datos legacy
├── resources/
│   └── legacy/                                   # Espejo de assets legacy
└── public/                                       # Raíz pública (CSS, JS, imágenes)
```

### Controlador Legacy (`LegacyController`)

**Archivo**: `app/Http/Controllers/LegacyController.php`

Función: Ejecuta scripts PHP legacy dentro del entorno Laravel manteniendo sesiones aisladas.

- Ruta: `/php/{script}`
- Crea entorno de sesión aislado con nombre `LEGACYSESSID`
- Sanitiza cookies de sesión inválidas antes de `session_start()`
- Ejecuta el script legacy en el mismo proceso PHP
- Aísla el estado de sesión para evitar conflictos con Laravel

### Servicio Legacy (`LegacyRuntime`)

**Archivo**: `app/Support/Legacy/LegacyRuntime.php`

- Ejecuta código PHP legacy de forma sandboxada
- Maneja la inicialización de sesiones legacy
- Proporciona métodos para obtener/conectar a la base de datos legacy
- Aísla el ámbito de variables globales

### Controlador de Archivos Legacy (`LegacyFileController`)

**Archivo**: `app/Http/Controllers/LegacyFileController.php`

Sirve archivos del sistema legacy de forma segura:

- Rutas: `/uploads/{path}`, `/docs/{path}`, `/assets/{path}`
- Previene acceso directorio traversal
- Sirve desde `lympcComp/uploads/`, `lympcComp/docs/`, `lympcComp/assets/`

### Modelos Eloquent

#### `User` / `Usuario`
- Tabla: `usuarios`
- Campos clave: id_usuario, usuario, correo, cedula, contraseña, rol, email_verified, reset_token
- Relaciones: hasMany(Cita), hasMany(Pedido), hasMany(TelegramAccount)

#### `Cita`
- Tabla: `citas`
- Campos: id_cita, id_usuario, nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, accion_sensible, fecha, telefono, motivo
- Relaciones: belongsTo(User)

#### `Pedido`
- Tabla: `pedidos`
- Campos: id_pedido, id_usuario, fecha_pedido, total
- Relaciones: belongsTo(User), hasMany(DetallePedido)

#### `DetallePedido`
- Tabla: `detalles_pedido`
- Campos: id_detalle, id_pedido, id_producto, cantidad, precio_unitario
- Relaciones: belongsTo(Pedido), belongsTo(Producto)

#### `Producto`
- Tabla: `productos`
- Campos: id_producto, nombre, serie, fecha, unidades, precio, imagen, id_soporte, categoria
- Relaciones: belongsTo(Soporte), hasMany(DetallePedido)

#### `ChatConversacion`
- Tabla: `chat_conversaciones`
- Campos: id_conversacion, id_usuario, nombre_usuario, correo_usuario, estado, tema, fecha_creacion, fecha_actualizacion, calificacion, comentario_calificacion, fecha_cierre
- Estados: ia, pendiente_asistente, finalizado
- Relaciones: hasMany(ChatMensaje), belongsTo(User)

#### `ChatMensaje`
- Tabla: `chat_mensajes`
- Campos: id_mensaje, id_conversacion, remitente, nombre_remitente, mensaje, fecha_envio
- Remitentes: usuario, ia, asistente
- Relaciones: belongsTo(ChatConversacion)

#### `TelegramAccount`
- Tabla: `telegram_accounts`
- Campos: id, id_usuario, telegram_chat_id, telegram_user_id, username, telefono, telefono_verificado_en, ultima_actividad_en
- Relaciones: belongsTo(User)

#### `TelegramActionRequest`
- Tabla: `telegram_action_requests`
- Campos: id, id_usuario, telegram_chat_id, accion, datos, resumen, confirmacion_hash, estado, expira_en, confirmada_en, error
- Estados: pendiente, confirmada, rechazada, expirada, ejecutada, fallida
- Relaciones: belongsTo(User), hasMany(TelegramActionAudit)

### Rutas Web (`routes/web.php`)

| Ruta | Método | Controlador | Descripción |
|------|--------|-------------|-------------|
| `/php/{script}` | GET | LegacyController | Ejecuta script PHP legacy |
| `/uploads/{path}` | GET | LegacyFileController | Sirve archivos de uploads |
| `/docs/{path}` | GET | LegacyFileController | Sirve documentos |
| `/assets/{path}` | GET | LegacyFileController | Sirve assets estáticos |
| `/telegram` | GET/POST | TelegramLinkController | Vincular cuenta Telegram |
| `/telegram/codigo-vinculacion` | POST | TelegramLinkController | Generar código de vinculación |
| `/telegram/desvincular` | POST | TelegramLinkController | Desvincular cuenta Telegram |

### Rutas API (`routes/api.php`)

| Ruta | Método | Controlador | Descripción |
|------|--------|-------------|-------------|
| `/api/productos` | GET | ProductoController@index | Listar productos (paginado, filtros) |
| `/api/productos` | POST | ProductoController@store | Crear producto (admin) |
| `/api/telegram/webhook` | POST | TelegramWebhookController | Webhook de Telegram |

### Middleware CSRF

**Archivo**: `app/Http/Middleware/VerifyCsrfToken.php`

Excluye de verificación CSRF:
- `cart_action.php`
- `chat_api.php`
- Otras rutas AJAX legacy

---

## Bot de Telegram

El bot de Telegram está completamente rediseñado con interfaz de **botones personalizados** (sin texto libre). Utiliza webhooks o polling para recibir actualizaciones, y mantiene estado de sesión por chat para flujos multi-paso.

### Arquitectura del Bot

```
Telegram Webhook/Polling
        ↓
TelegramWebhookController
        ↓
TelegramBotService (máquina de estados)
        ↓
    ├── AiIntentService (IA opcional)
    ├── TelegramActionService (ejecuta acciones)
    └── Vistas/Teclados (botones personalizados)
```

### Servicio Principal (`TelegramBotService`)

**Archivo**: `app/Services/TelegramBotService.php`

Responsabilidades:
- Gestión de menús principales y submenús
- Máquina de estados por chat (`$sessions`)
- Navegación por categorías de productos
- Programación de citas paso a paso
- Formularios de contacto guiados
- Comandos del sistema

### Estados de Sesión por Chat

| Estado | Flujo | Descripción |
|--------|-------|-------------|
| `main` | Inicial | Menú principal con 4 botones |
| `products_category` | Catálogo | Selección de categoría de productos |
| `products_list` | Catálogo | Lista de productos de categoría |
| `product_detail` | Catálogo | Detalle de producto con acciones |
| `appointment_name` | Citas | Captura de nombre |
| `appointment_lastname` | Citas | Captura de apellido |
| `appointment_email` | Citas | Captura de correo |
| `appointment_cedula` | Citas | Captura de cédula |
| `appointment_phone` | Citas | Captura de teléfono |
| `appointment_date` | Citas | Selección de fecha |
| `appointment_reason` | Citas | Captura de motivo |
| `contact_name` | Contacto | Captura de nombre |
| `contact_company` | Contacto | Captura de empresa |
| `contact_message` | Contacto | Captura de mensaje |

### Teclados (Botones Personalizados)

#### Menú Principal
- 🛒 Ver Productos
- 🔧 Servicio Técnico
- 📱 Mi Cuenta
- 💬 Hablar con Asesor

#### Categorías de Productos
- 💻 Laptops
- 🖥️ PCs de Escritorio
- 📱 Tablets
- 🖨️ Impresoras
- 🧪 Insumos
- 💿 Duplicadores
- 🔙 Volver al Menú

#### Acciones de Producto
- 📝 Agendar Cita
- 💬 Solicitar Información
- 🛒 Comprar
- 🔙 Volver

#### Mi Cuenta
- 📋 Mis Citas
- 📦 Mis Pedidos
- 📅 Agenda de Hoy
- 📊 Resumen
- 🔙 Volver

#### Contacto
- ✅ Enviar Mensaje
- 🔙 Cancelar

### Comandos Disponibles

| Comando | Descripción |
|---------|-------------|
| `/start` | Inicia el bot, muestra menú principal |
| `/menu` | Regresa al menú principal desde cualquier estado |
| `/vincular CODIGO` | Vincula cuenta web con Telegram |
| `/mis_citas` | Muestra citas próximas del usuario |
| `/mis_pedidos` | Muestra pedidos recientes del usuario |
| `/agenda_hoy` | Muestra citas del día actual |
| `/resumen` | Resumen de actividad reciente |

### Flujos de Navegación

#### Flujo de Productos
1. Usuario presiona "🛒 Ver Productos"
2. Bot muestra categorías
3. Usuario selecciona categoría
4. Bot lista productos de la categoría
5. Usuario selecciona producto
6. Bot muestra detalle con acciones (Agendar Cita, Solicitar Info, Comprar)
7. Si selecciona "Agendar Cita", inicia flujo de citas
8. Si selecciona "Solicitar Información", crea mensaje de contacto automático
9. Si selecciona "Comprar", crea pedido simulado

#### Flujo de Citas
1. Usuario presiona "🔧 Servicio Técnico" → "Agendar Cita"
2. Bot solicita nombre → apellido → correo → cédula → teléfono → fecha → motivo
3. Cada respuesta se captura en el estado de sesión
4. Al completar, se crea `TelegramActionRequest` con resumen
5. Administrador confirma desde portal web
6. `TelegramActionService` ejecuta la acción y guarda en BD

#### Flujo de Contacto
1. Usuario presiona "💬 Hablar con Asesor"
2. Bot solicita nombre → empresa → mensaje
3. Crea `TelegramActionRequest`
4. Admin confirma desde portal web
5. Se guarda mensaje en tabla `contacto`

### Vinculación de Cuentas

Proceso seguro de vinculación web ↔ Telegram:

1. Usuario web genera código en `/telegram/codigo-vinculacion`
2. Sistema crea `TelegramLinkCode` con hash SHA-256, expiración 10 min
3. Usuario envía `/vincular CODIGO` al bot
4. Bot valida código y crea/actualiza `TelegramAccount`
5. Usuario queda vinculado; puede usar `/mis_citas`, `/mis_pedidos`, etc.

### Acciones Confirmadas por Personal

Para acciones sensibles (crear citas, contactos, etc.), el bot no ejecuta directamente:

1. `TelegramBotService` crea `TelegramActionRequest` en estado `pendiente`
2. Administrador ve solicitudes pendientes en dashboard
3. Admin confirma o rechaza desde portal web
4. `TelegramActionService` ejecuta en transacción de BD
5. Se registra en `TelegramActionAudit`
6. Bot notifica al usuario del resultado

### Interpretación de IA (`AiIntentService`)

**Archivo**: `app/Services/AiIntentService.php`

- Proveedores: Gemini (default), Qwen, Groq
- Envía texto del usuario a IA para extraer intención estructurada
- Acciones soportadas: `crear_cita`, `crear_contacto`, `crear_producto`, `consultar_cita`, `consultar_pedido`, `consultar_producto`
- Parámetros extraídos: nombres, fechas, montos, categorías
- Si la IA no puede interpretar, responde con opciones de botón

### Modo de Ejecución

#### Webhook (Producción)
- URL: `https://tudominio.com/api/telegram/webhook`
- Secreto: `TELEGRAM_WEBHOOK_SECRET`
- Configuración: `POST https://api.telegram.org/bot<TOKEN>/setWebhook?url=<URL>&secret=<SECRET>`

#### Polling (Desarrollo)
- Comando: `php artisan telegram:poll`
- Archivo: `app/Console/Commands/PollTelegramUpdates.php`
- Obtiene actualizaciones mediante `getUpdates` en bucle
- Usa `TELEGRAM_ALLOWED_UPDATE_IDS` para evitar reprocesar

### Protecciones

- Validación de token secreto en webhook
- Deduplicación por `update_id` en `telegram_webhook_updates`
- Rate limiting implícito por sesión
- Timeout en solicitudes a Telegram API
- Sanitización de entrada en todos los campos
- Límite de intentos en códigos de vinculación

---

## Widget de Chat Web

El widget de chat web es un componente flotante que permite a los usuarios interactuar con un asistente virtual basado en IA o escalar a un asistente humano.

### Arquitectura del Chat

```
Usuario Web
    ↓
chat_widget.php (HTML/CSS)
chat_widget.js (JS - polling, UI)
    ↓
chat_api.php (API backend)
    ↓
chat_helpers.php
    ↓
    ├── chatbot_ia.py (IA - Python)
    └── chatBotFallback() (PHP - keyword matching)
```

### Interfaz de Usuario

**Archivo**: `lympcComp/includes/chat_widget.php`

- Widget flotante en esquina inferior derecha
- Solo visible para usuarios autenticados que NO son staff
- Botones de acción rápida (sin texto libre)
- Historial de mensajes con diferenciación de remitente (usuario, IA, asistente)
- Sistema de calificación post-conversación (1-10 con comentario)
- Indicador de estado: typing, en línea, fuera de línea

### Backend API (`chat_api.php`)

**Archivo**: `lympcComp/php/chat_api.php`

Acciones soportadas:

| Acción | Parámetros | Descripción |
|--------|-----------|-------------|
| `bootstrap` | - | Inicializa o recupera conversación activa |
| `send` | mensaje, tema | Envía mensaje y obtiene respuesta |
| `rate` | calificacion, comentario | Califica la conversación finalizada |

Flujo:
1. Verifica sesión activa y rol de usuario
2. Si no es staff, permite acceso
3. Para `bootstrap`: crea o recupera conversación en estado `ia`
4. Para `send`: guarda mensaje, invoca IA, actualiza estado
5. Para `rate`: cierra conversación con calificación

### Lógica de Chat (`chat_helpers.php`)

**Archivo**: `lympcComp/includes/chat_helpers.php`

Funciones principales:

- `createConversation($id_usuario, $nombre, $correo)`: Crea nueva conversación
- `getActiveConversation($id_usuario)`: Obtiene conversación activa del usuario
- `getMessages($id_conversacion)`: Obtiene historial de mensajes
- `sendMessage($id_conversacion, $remitente, $nombre, $mensaje)`: Guarda mensaje
- `closeConversation($id_conversacion, $calificacion, $comentario)`: Cierra conversación
- `chatBotResponse($mensaje)`: Invoca chatbot Python o fallback PHP
- `chatBotFallback($mensaje)`: Respuesta por palabras clave en PHP

### Chatbot IA (`chatbot_ia.py`)

**Archivo**: `lympcComp/python/chatbot_ia.py`

Tecnología: Red neuronal Python con capas y neuronas (forward/backward propagation).

- Normalización Unicode de entrada
- Clasificación de intenciones:
  - Soporte técnico
  - Información de horarios/citas
  - Ubicación de la empresa
  - Información general de la empresa
  - Solicitud de asistente humano
- Si intención = asistente humano: cambia estado a `pendiente_asistente`
- Invocado mediante `proc_open()` desde PHP

### Red Neuronal (`neuronal_network.py`, `layer.py`, `neuron.py`)

**Archivos**: `lympcComp/python/neuronal_network.py`, `layer.py`, `neuron.py`

Componentes:
- `Neuron`: Neurona con activación sigmoide, pesos y bias
- `Layer`: Capa de neuronas, propagación hacia adelante y atrás
- `NeuronalNetwork`: Red completa con MSE loss, entrenamiento y predicción

### Estados de Conversación

| Estado | Descripción |
|--------|-------------|
| `ia` | Asistente virtual activo |
| `pendiente_asistente` | Escalado a humano, esperando conexión |
| `finalizado` | Conversación cerrada con calificación |

### JavaScript del Widget (`chat_widget.js`)

**Archivo**: `lympcComp/js/chat_widget.js`

Funcionalidades:
- Polling cada 2 segundos para nuevos mensajes
- Renderizado de burbujas de chat (usuario, IA, asistente)
- Scroll automático al último mensaje
- Sistema de calificación con estrellas
- Botones de acción rápida en el teclado
- Indicadores de estado (online/offline/typing)
- Responsive design

### Flujo de Conversación

1. Usuario abre widget → `bootstrap` crea/recupera conversación
2. Usuario envía mensaje → `send` guarda y procesa
3. `chatBotResponse()` clasifica intención
4. Si es consulta simple → respuesta automática de IA
5. Si requiere humano → estado cambia a `pendiente_asistente`
6. Asistente se conecta y responde desde panel admin
7. Conversación se cierra → usuario califica (1-10 + comentario)

### Limitaciones

- No disponible para personal staff (admin, técnico, pasante, asistente)
- Requiere sesión activa de usuario web
- Python debe estar disponible en el servidor para IA; si no, usa fallback PHP
- Una conversación activa por usuario a la vez

---

## Esquema de Base de Datos

El sistema utiliza dos bases de datos: **MariaDB** para datos legacy y **SQLite** para tablas de Laravel.

### Base de Datos Legacy (`lympc_bd`)

Conexión: `lympcComp/includes/db.php`
- Intenta puerto 3306, fallback a 3307
- Auto-migración de columnas de verificación de correo

#### Tablas de Negocio

**`usuarios`** - Usuarios del sistema
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_usuario` | INT (PK, AI) | Identificador único |
| `usuario` | VARCHAR | Nombre de usuario |
| `correo` | VARCHAR | Correo electrónico (único) |
| `cedula` | VARCHAR | Cédula de identidad |
| `archivo_ruc` | VARCHAR | Ruta a archivo RUC |
| `archivo_cedula` | VARCHAR | Ruta a archivo cédula |
| `contraseña` | VARCHAR | Contraseña hasheada |
| `rol` | ENUM | usuario, admin, tecnico, pasante, asistente |
| `reset_token` | VARCHAR | Token de recuperación |
| `reset_expiration` | DATETIME | Expiración del token |
| `email_verified` | TINYINT | Estado de verificación (0/1) |
| `email_verification_token` | VARCHAR | Token de verificación |
| `email_verification_expires` | DATETIME | Expiración del token |

**`citas`** - Citas técnicas
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_cita` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario |
| `nombre` | VARCHAR | Nombre del cliente |
| `apellido` | VARCHAR | Apellido del cliente |
| `correo` | VARCHAR | Correo electrónico |
| `cedula` | VARCHAR | Cédula de identidad |
| `archivo_ruc` | VARCHAR | Ruta a RUC |
| `archivo_cedula` | VARCHAR | Ruta a cédula |
| `accion_sensible` | TINYINT | Marca de acción sensible |
| `fecha` | DATETIME | Fecha y hora de la cita |
| `telefono` | VARCHAR | Teléfono de contacto |
| `motivo` | TEXT | Motivo de la cita |

**`productos`** - Catálogo de productos
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_producto` | INT (PK, AI) | Identificador único |
| `nombre` | VARCHAR | Nombre del producto |
| `serie` | VARCHAR | Número de serie |
| `fecha` | DATE | Fecha de registro |
| `unidades` | INT | Cantidad en inventario |
| `precio` | DECIMAL | Precio unitario |
| `imagen` | VARCHAR | Ruta a imagen |
| `id_soporte` | INT (FK) | Referencia a categoría soporte |
| `categoria` | VARCHAR | Categoría del producto |

**`soporte`** - Categorías de soporte
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_soporte` | INT (PK, AI) | Identificador único |
| `Nombre` | VARCHAR | Nombre de la categoría |
| `Correo` | VARCHAR | Correo de soporte |
| `cedula` | VARCHAR | Cédula responsable |
| `archivo_ruc` | VARCHAR | Ruta a RUC |
| `archivo_cedula` | VARCHAR | Ruta a cédula |
| `Compania` | VARCHAR | Compañía asociada |
| `Mensaje` | TEXT | Mensaje de soporte |

**`contacto`** - Mensajes de contacto
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_mensaje` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario (opcional) |
| `nombre` | VARCHAR | Nombre del remitente |
| `correo` | VARCHAR | Correo electrónico |
| `compania` | VARCHAR | Empresa |
| `mensaje` | TEXT | Contenido del mensaje |
| `cedula` | VARCHAR | Cédula |
| `archivo_ruc` | VARCHAR | Ruta a RUC |
| `archivo_cedula` | VARCHAR | Ruta a cédula |
| `fecha` | DATETIME | Fecha de envío |

**`pedidos`** - Órdenes de compra
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_pedido` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario |
| `fecha_pedido` | DATETIME | Fecha del pedido |
| `total` | DECIMAL | Monto total |

**`detalles_pedido`** - Detalles de pedido
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_detalle` | INT (PK, AI) | Identificador único |
| `id_pedido` | INT (FK) | Referencia a pedido |
| `id_producto` | INT (FK) | Referencia a producto |
| `cantidad` | INT | Cantidad comprada |
| `precio_unitario` | DECIMAL | Precio al momento de compra |

#### Tablas de Chat

**`chat_conversaciones`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_conversacion` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario |
| `nombre_usuario` | VARCHAR | Nombre del usuario |
| `correo_usuario` | VARCHAR | Correo del usuario |
| `estado` | ENUM | ia, pendiente_asistente, finalizado |
| `tema` | VARCHAR | Tema de la conversación |
| `fecha_creacion` | DATETIME | Inicio de conversación |
| `fecha_actualizacion` | DATETIME | Última actualización |
| `calificacion` | INT | Calificación 1-10 |
| `comentario_calificacion` | TEXT | Comentario del usuario |
| `fecha_cierre` | DATETIME | Cierre de conversación |

**`chat_mensajes`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_mensaje` | INT (PK, AI) | Identificador único |
| `id_conversacion` | INT (FK) | Referencia a conversación |
| `remitente` | ENUM | usuario, ia, asistente |
| `nombre_remitente` | VARCHAR | Nombre visible del remitente |
| `mensaje` | TEXT | Contenido del mensaje |
| `fecha_envio` | DATETIME | Fecha de envío |

#### Tablas de Telegram

**`telegram_accounts`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario web |
| `telegram_chat_id` | VARCHAR | Chat ID de Telegram |
| `telegram_user_id` | INT | User ID de Telegram |
| `username` | VARCHAR | Username de Telegram |
| `telefono` | VARCHAR | Número de teléfono |
| `telefono_verificado_en` | DATETIME | Fecha de verificación |
| `ultima_actividad_en` | DATETIME | Última interacción |

**`telegram_link_codes`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario |
| `codigo_hash` | VARCHAR | Hash SHA-256 del código |
| `expira_en` | DATETIME | Fecha de expiración (10 min) |
| `usado_en` | DATETIME | Fecha de uso |
| `ip_solicitud` | VARCHAR | IP del solicitante |

**`telegram_webhook_updates`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK, AI) | Identificador único |
| `update_id` | BIGINT | ID único de Telegram |
| `created_at` | TIMESTAMP | Fecha de recepción |

**`telegram_action_requests`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario |
| `telegram_chat_id` | VARCHAR | Chat ID origen |
| `accion` | VARCHAR | Tipo de acción (crear_cita, etc.) |
| `datos` | JSON | Datos de la solicitud |
| `resumen` | TEXT | Resumen legible |
| `confirmacion_hash` | VARCHAR | Hash de confirmación |
| `estado` | ENUM | pendiente, confirmada, rechazada, expirada, ejecutada, fallida |
| `expira_en` | DATETIME | Fecha de expiración |
| `confirmada_en` | DATETIME | Fecha de confirmación |
| `error` | TEXT | Mensaje de error si aplica |

**`telegram_action_audits`**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK, AI) | Identificador único |
| `id_usuario` | INT (FK) | Referencia a usuario |
| `telegram_chat_id` | VARCHAR | Chat ID origen |
| `accion` | VARCHAR | Tipo de acción |
| `estado` | VARCHAR | Estado final |
| `detalle` | TEXT | Detalle del resultado |
| `ip_origen` | VARCHAR | IP del administrador |
| `created_at` | TIMESTAMP | Fecha de creación |

### Diagrama de Relaciones

```
usuarios (1) ────── (N) citas
usuarios (1) ────── (N) pedidos
usuarios (1) ────── (N) chat_conversaciones
usuarios (1) ────── (N) telegram_accounts
usuarios (1) ────── (N) telegram_action_requests

pedidos (1) ────── (N) detalles_pedido
productos (1) ────── (N) detalles_pedido
productos (N) ────── (1) soporte

chat_conversaciones (1) ────── (N) chat_mensajes
telegram_action_requests (1) ────── (N) telegram_action_audits
```

### Migraciones Laravel

Las migraciones crean las tablas de Telegram y Chat en la base de datos configurada en Laravel (SQLite por defecto):

- `create_telegram_accounts_table.php`
- `create_telegram_link_codes_table.php`
- `create_telegram_webhook_updates_table.php`
- `create_telegram_action_requests_table.php`
- `create_telegram_action_audits_table.php`
- `create_chat_conversaciones_table.php`
- `create_chat_mensajes_table.php`

---

## Instalación y Configuración

### Requisitos Previos

- PHP 8.2+
- Composer
- Node.js y npm
- XAMPP (Apache + MariaDB)
- Python 3.x (para chatbot IA)
- Extensión cURL de PHP habilitada
- Extensión PDO de PHP habilitada
- Extensión fileinfo de PHP habilitada

### Instalación del Módulo Legacy

1. Clonar el repositorio en `C:\xampp\htdocs\lymPCComputadoras\`
2. Importar base de datos:
   ```sql
   mysql -u root -p < lymptcComp/BASE DE DATOS/lympc_bd.sql
   ```
3. Configurar conexión en `lympcComp/includes/db.php`:
   ```php
   $host = 'localhost';
   $db   = 'lympc_bd';
   $user = 'root';
   $pass = '';
   $port = 3306; // o 3307 si aplica
   ```
4. Verificar que Python esté disponible:
   ```bash
   py --version
   ```
5. Acceder a `http://localhost/lymPCComputadoras/lympcComp/php/login.php`

### Instalación del Módulo Laravel

1. Navegar al directorio Laravel:
   ```bash
   cd lym_pcComputadoras/lympcComputadora-app
   ```
2. Instalar dependencias PHP:
   ```bash
   composer install
   ```
3. Instalar dependencias Node.js:
   ```bash
   npm install
   ```
4. Configurar archivo `.env`:
   ```env
   APP_NAME="L&M PC Computadoras"
   APP_URL=http://localhost/lymPCComputadoras/lym_pcComputadoras/lympcComputadora-app/public

   # Base de datos Laravel
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite

   # Telegram
   TELEGRAM_BOT_TOKEN=tu_token_aqui
   TELEGRAM_WEBHOOK_SECRET=tu_secreto_aqui
   TELEGRAM_BOT_USERNAME=tu_bot_aqui

   # IA (Gemini por defecto)
   AI_PROVIDER=gemini
   GEMINI_API_KEY=tu_api_key_aqui
   PYTHON_BIN=py
   ```
5. Crear archivo SQLite:
   ```bash
   copy nul database/database.sqlite
   ```
6. Generar clave de aplicación:
   ```bash
   php artisan key:generate
   ```
7. Ejecutar migraciones:
   ```bash
   php artisan migrate
   ```
8. Compilar assets:
   ```bash
   npm run build
   ```
   O para desarrollo:
   ```bash
   npm run dev
   ```
9. Iniciar servidor:
   ```bash
   php artisan serve
   ```
10. Acceder a `http://localhost:8000`

### Configuración del Bot de Telegram

#### Obtener Token
1. Abrir Telegram y buscar `@BotFather`
2. Enviar `/newbot` y seguir instrucciones
3. Guardar el token proporcionado

#### Configurar Webhook
```bash
curl -F "url=https://tudominio.com/api/telegram/webhook" \
     -F "secret=tu_secreto_aqui" \
     https://api.telegram.org/bot<TOKEN>/setWebhook
```

#### Modo Desarrollo (Polling)
```bash
php artisan telegram:poll
```

### Configuración de IA

Proveedores soportados:

**Gemini (Google)**
- Obtener API key en [Google AI Studio](https://aistudio.google.com/)
- Configurar `AI_PROVIDER=gemini` y `GEMINI_API_KEY`

**Qwen (Alibaba DashScope)**
- Obtener API key en [DashScope](https://dashscope.console.aliyun.com/)
- Configurar `AI_PROVIDER=qwen` y `QWEN_API_KEY`

**Groq**
- Obtener API key en [Groq Console](https://console.groq.com/)
- Configurar `AI_PROVIDER=groq` y `GROQ_API_KEY`

---

## Referencia de API

### APIs Legacy (PHP)

#### `api_horarios.php`
**Método**: GET  
**Auth**: Sesión de usuario  
**Parámetros**: `desde` (YYYY-MM-DD), `hasta` (YYYY-MM-DD)  
**Respuesta**: JSON con array de citas del usuario en el rango

#### `api_horarios_admin.php`
**Métodos**: GET, POST, PUT, DELETE  
**Auth**: Sesión (admin, tecnico, encargado, pasante)  
**GET**: Listar citas (con `desde`, `hasta`, o `ids` para específicas)  
**POST**: Crear cita  
**PUT**: Actualizar cita  
**DELETE**: Eliminar cita

#### `cart_action.php`
**Método**: POST  
**Auth**: Sesión de usuario  
**Parámetros**: `action` (add/clear/purchase), `id_producto` (para add), `cantidad` (para add)  
**Respuesta**: JSON con resultado de la operación

#### `chat_api.php`
**Método**: POST/GET  
**Auth**: Sesión de usuario (no staff)  
**Parámetros GET**: `action=bootstrap`  
**Parámetros POST**: `action` (bootstrap/send/rate), `mensaje`, `tema`, `calificacion`, `comentario`  
**Respuesta**: JSON con datos de conversación, mensajes o confirmación

### APIs Laravel

#### `GET /api/productos`
**Auth**: Pública  
**Parámetros query**:
- `search` - Búsqueda por nombre
- `categoria` - Filtro por categoría
- `page` - Página para paginación
- `per_page` - Items por página

**Respuesta**:
```json
{
  "data": [
    {
      "id_producto": 1,
      "nombre": "Laptop HP",
      "precio": 899.99,
      "categoria": "laptops",
      "imagen": "ruta/imagen.jpg",
      "unidades": 5
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

#### `POST /api/productos`
**Auth**: No documentada (requiere autenticación admin)  
**Parámetros**: JSON con datos del producto  
**Respuesta**: Producto creado o error de validación

#### `POST /api/telegram/webhook`
**Auth**: Token secreto (`TELEGRAM_WEBHOOK_SECRET`)  
**Parámetros**: Update de Telegram (JSON)  
**Respuesta**: 200 OK o 403 Forbidden

---

## Despliegue y Mantenimiento

### Consideraciones de Producción

#### Servidor Web
- Configurar virtual host en Apache para Laravel (apuntar `public/` como DocumentRoot)
- Habilitar mod_rewrite para URLs amigables
- Configurar HTTPS con certificado SSL válido
- Establecer `APP_ENV=production` y `APP_DEBUG=false`

#### Base de Datos
- Utilizar MariaDB en producción (no SQLite)
- Configurar `DB_CONNECTION=mysql` en `.env`
- Ejecutar migraciones: `php artisan migrate --force`
- Configurar backups automáticos de `lympc_bd`
- Optimizar tablas con índices en campos de búsqueda frecuente

#### Sesiones
- Legacy PHP usa cookie `LEGACYSESSID` - asegurar dominio coincidente
- Laravel usa su propio driver de sesiones (file/database/redis)
- Configurar `SESSION_DOMAIN` y `SESSION_SECURE_COOKIE`

#### Cache
- Configurar driver de cache (redis recomendado para producción)
- Ejecutar `php artisan config:cache`
- Ejecutar `php artisan route:cache`
- Ejecutar `php artisan view:cache`

#### Assets
- Ejecutar `npm run build` para compilar assets de producción
- Configurar Vite para versionado de assets
- Configurar CDN si aplica

#### Colas (Queue)
- Configurar driver de colas para jobs asíncronos
- Ejecutar worker: `php artisan queue:work --daemon`

#### Logs
- Configurar rotación de logs en `storage/logs/`
- Monitorear `laravel.log` para errores
- Configurar logging a servicio externo (Papertrail, Sentry, etc.)

### Mantenimiento del Bot de Telegram

#### Actualizaciones
- Monitorear cambios en API de Telegram
- Actualizar token si se revoca
- Revisar límites de rate de Telegram API
- Verificar deduplicación de updates

#### Acciones Pendientes
- Revisar `telegram_action_requests` periódicamente
- Notificar a administradores de acciones pendientes
- Limpiar registros expirados con scheduled command

#### Monitoreo
- Log de webhooks en `storage/logs/`
- Tabla `telegram_webhook_updates` para deduplicación
- Tabla `telegram_action_audits` para trazabilidad

### Mantenimiento del Chat Web

#### Conversaciones
- Revisar conversaciones en estado `pendiente_asistente`
- Cerrar conversaciones abandonadas
- Exportar calificaciones para análisis

#### IA
- Revisar logs de `chatbot_ia.py` para errores
- Actualizar modelo de red neuronal si es necesario
- Verificar disponibilidad de Python en el servidor

### Backup

#### Base de Datos Legacy
```bash
mysqldump -u root -p lmpc_bd > backup_lympc_bd_$(date +%Y%m%d).sql
```

#### Base de Datos Laravel (SQLite)
```bash
cp database/database.sqlite backup_database_$(date +%Y%m%d).sqlite
```

#### Archivos Subidos
```bash
tar -czf backup_uploads_$(date +%Y%m%d).tar.gz lymptcComp/uploads/
```

### Actualizaciones

1. Pull de cambios en Git
2. `composer install --no-dev --optimize-autoloader`
3. `npm run build`
4. `php artisan migrate --force`
5. `php artisan config:cache`
6. `php artisan route:cache`
7. `php artisan view:cache`
8. Reiniciar servicios (Apache, queue workers)

---

## Contacto

### L&M PC Computadoras

**WhatsApp**: +593 98 309 3667  
**Facebook**: https://www.facebook.com/LyM010?locale=es_LA  
**Google Maps**: https://maps.app.goo.gl/JxiKmPJ1s7dtaac57

---

## Documentación Adicional

- `lympcComp/assets/DOCUMENTACION.md` - Documentación general del sistema
- `lympcComp/assets/GUIA_USUARIO.md` - Guía de usuario
- `lympcComp/assets/API.md` - Documentación de APIs legacy
- `lym_pcComputadoras/lympcComputadora-app/docs/TELEGRAM.md` - Guía de integración Telegram
- `lym_pcComputadoras/lympcComputadora-app/docs/TECHNICAL_REPORT_LEGACY_SESSION_FIX.md` - Reporte técnico de corrección de sesiones