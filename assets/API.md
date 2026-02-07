# Documentación de APIs - L&M PC Computadoras

## Descripción General

Las siguientes APIs disponibles en la aplicación proporcionan acceso programático a los datos de citas. Todas las solicitudes deben ser realizadas desde un cliente autenticado (sesión PHP activa).

## Endpoints Disponibles

Hay dos conjuntos de endpoints: uno para usuarios y otro para administradores.

## API de Usuarios: api_horarios.php

URL Base: /lymPCComputadoras/php/api_horarios.php

Este endpoint permite a usuarios autenticados obtener sus propias citas filtradas por rango de fechas.

### Autenticación

- Requerida: Si
- Tipo: Sesión PHP ($_SESSION['usuario'] debe estar definido)
- Respuesta sin autenticación: HTTP 401 Unauthorized

### GET - Obtener Citas por Rango de Fechas

Descripción: Obtiene todas las citas del usuario autenticado dentro de un rango de fechas especificado.

URL: GET /lymPCComputadoras/php/api_horarios.php?desde=YYYY-MM-DDTHH:MM&hasta=YYYY-MM-DDTHH:MM

Parámetros de Query:

desde (requerido): Fecha y hora de inicio en formato ISO 8601 (YYYY-MM-DDTHH:MM)
hasta (requerido): Fecha y hora de fin en formato ISO 8601 (YYYY-MM-DDTHH:MM)

Ejemplo de Solicitud:

```
GET /lymPCComputadoras/php/api_horarios.php?desde=2026-02-01T00:00&hasta=2026-02-28T23:59
Host: localhost
```

Respuesta Exitosa: HTTP 200 OK

Cuerpo de Respuesta:

```json
[
  {
    "id": 1,
    "nombre": "Juan",
    "apellido": "Perez",
    "correo": "juan@example.com",
    "fecha": "2026-02-10 14:30:00",
    "telefono": "3001234567",
    "motivo": "Reparacion de monitor"
  },
  {
    "id": 2,
    "nombre": "Juan",
    "apellido": "Perez",
    "correo": "juan@example.com",
    "fecha": "2026-02-15 10:00:00",
    "telefono": "3001234567",
    "motivo": "Instalacion de antivirus"
  }
]
```

Códigos de Error: 

401: No autenticado
500: Error en la base de datos

## API de Administradores: api_horarios_admin.php

URL Base: /lymPCComputadoras/php/api_horarios_admin.php

Este endpoint permite a administradores autenticados obtener, crear, editar y eliminar citas de cualquier usuario.

### Autenticación

- Requerida: Si
- Tipo: Sesión PHP ($_SESSION['rol'] debe estar en ["admin", "tecnico", "encargado", "pasante"])
- Respuesta sin autenticación: HTTP 401 Unauthorized

### GET - Obtener Citas por Rango de Fechas

Descripción: Obtiene todas las citas en el sistema dentro de un rango de fechas.

URL: GET /lymPCComputadoras/php/api_horarios_admin.php?desde=YYYY-MM-DDTHH:MM&hasta=YYYY-MM-DDTHH:MM

Parámetros de Query:

desde (requerido): Fecha de inicio en formato ISO 8601
hasta (requerido): Fecha de fin en formato ISO 8601

Ejemplo de Solicitud:

```
GET /lymPCComputadoras/php/api_horarios_admin.php?desde=2026-02-01T00:00&hasta=2026-02-28T23:59
Host: localhost
```

Respuesta Exitosa: HTTP 200 OK

Cuerpo de Respuesta: Array JSON con objetos cita (mismo formato que api_horarios.php)

### GET - Obtener Citas por IDs

Descripción: Obtiene citas específicas usando sus IDs.

URL: GET /lymPCComputadoras/php/api_horarios_admin.php?ids=1,2,3

Parámetros de Query:

ids (requerido): IDs separados por comas

Ejemplo de Solicitud:

```
GET /lymPCComputadoras/php/api_horarios_admin.php?ids=5,7,9
Host: localhost
```

Respuesta Exitosa: HTTP 200 OK

Cuerpo de Respuesta: Array JSON con las citas solicitadas

### POST - Crear Nueva Cita

Descripción: Crea una nueva cita en el sistema.

URL: POST /lymPCComputadoras/php/api_horarios_admin.php

Headers: Content-Type: application/json

Cuerpo de Solicitud:

```json
{
  "nombre": "Carlos",
  "apellido": "Lopez",
  "correo": "carlos@example.com",
  "fecha": "2026-02-20 15:30:00",
  "telefono": "3009876543",
  "motivo": "Reparacion de teclado"
}
```

Campos Requeridos:
- nombre
- apellido
- correo
- fecha (formato YYYY-MM-DD HH:MM:SS)
- telefono
- motivo

Respuesta Exitosa: HTTP 200 OK

Cuerpo de Respuesta:

```json
{
  "success": true,
  "id": 15
}
```

Ejemplo en JavaScript:

```javascript
const cita = {
  nombre: "Carlos",
  apellido: "Lopez",
  correo: "carlos@example.com",
  fecha: "2026-02-20 15:30:00",
  telefono: "3009876543",
  motivo: "Reparacion de teclado"
};

const response = await fetch('/lymPCComputadoras/php/api_horarios_admin.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify(cita)
});

const data = await response.json();
console.log(data);
```

### PUT - Actualizar Cita Existente

Descripción: Modifica los datos de una cita existente.

URL: PUT /lymPCComputadoras/php/api_horarios_admin.php

Headers: Content-Type: application/json

Cuerpo de Solicitud:

```json
{
  "id": 5,
  "nombre": "Carlos",
  "apellido": "Lopez",
  "correo": "carlos@example.com",
  "fecha": "2026-02-20 16:00:00",
  "telefono": "3009876543",
  "motivo": "Reparacion y mantenimiento"
}
```

Campos Requeridos:
- id (ID de la cita a actualizar)
- nombre
- apellido
- correo
- fecha
- telefono
- motivo

Respuesta Exitosa: HTTP 200 OK

Cuerpo de Respuesta:

```json
{
  "success": true
}
```

Códigos de Error:
- 400: ID inválido
- 500: Error en la base de datos

Ejemplo en JavaScript:

```javascript
const citaActualizada = {
  id: 5,
  nombre: "Carlos",
  apellido: "Lopez",
  correo: "carlos@example.com",
  fecha: "2026-02-20 16:00:00",
  telefono: "3009876543",
  motivo: "Reparacion y mantenimiento"
};

const response = await fetch('/lymPCComputadoras/php/api_horarios_admin.php', {
  method: 'PUT',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify(citaActualizada)
});

const data = await response.json();
console.log(data);
```

### DELETE - Eliminar Cita

Descripción: Elimina una cita del sistema.

URL (opción 1): DELETE /lymPCComputadoras/php/api_horarios_admin.php?id=5

URL (opción 2): DELETE /lymPCComputadoras/php/api_horarios_admin.php

Parámetros:

Via Query String: id (ID de la cita a eliminar)
Via JSON Body: id (ID de la cita a eliminar)

Ejemplo de Solicitud (opción 1):

```
DELETE /lymPCComputadoras/php/api_horarios_admin.php?id=5
Host: localhost
```

Ejemplo de Solicitud (opción 2):

```
DELETE /lymPCComputadoras/php/api_horarios_admin.php
Host: localhost
Content-Type: application/json

{
  "id": 5
}
```

Respuesta Exitosa: HTTP 200 OK

Cuerpo de Respuesta:

```json
{
  "success": true
}
```

Códigos de Error:
- 400: ID inválido
- 500: Error en la base de datos

Ejemplo en JavaScript:

```javascript
const response = await fetch('/lymPCComputadoras/php/api_horarios_admin.php?id=5', {
  method: 'DELETE'
});

const data = await response.json();
console.log(data);
```

## Códigos HTTP Estándar

200 OK: Solicitud completada exitosamente
400 Bad Request: Parámetros inválidos o faltantes
401 Unauthorized: Usuario no autenticado o permisos insuficientes
405 Method Not Allowed: Método HTTP no soportado
500 Internal Server Error: Error en el servidor o base de datos

## Manejo de Errores

Todas las respuestas de error incluyen un objeto JSON con la siguiente estructura:

```json
{
  "error": "Descripción del error"
}
```

Ejemplo:

```json
{
  "error": "No autorizado"
}
```

## Consideraciones de Seguridad

1. Todas las solicitudes deben ser autenticadas
2. Valida todos los datos en el cliente antes de enviar
3. No expongas las URLs de las APIs directamente en HTML público
4. Usa HTTPS en producción para encriptar datos en tránsito
5. El servidor valida y sanuza todos los datos antes de procesarlos
6. Las contraseñas nunca se incluyen en las respuestas
7. Los IDs de sesión están protegidos por httpOnly flags (si están configurados)

## Límites de Tasa (Rate Limiting)

Actualmente no hay implementado rate limiting, pero se recomienda:
- Máximo 100 solicitudes por minuto por IP
- Máximo 1000 solicitudes por hora por usuario

## Formato de Fechas

Todas las fechas se envían en formato ISO 8601:

Para Rango (parámetros desde/hasta): YYYY-MM-DDTHH:MM
Para Cuerpo de Solicitud: YYYY-MM-DD HH:MM:SS
Para Respuesta: YYYY-MM-DD HH:MM:SS

Ejemplos:
- 2026-02-10 14:30:00 (formato de respuesta)
- 2026-02-01T00:00 (formato de parámetro)

## Ejemplos de Uso Completo

### Ejemplo 1: Obtener citas del mes actual (usuario)

```javascript
const now = new Date();
const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
const monthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);

const desde = monthStart.toISOString().split('T')[0] + 'T00:00';
const hasta = monthEnd.toISOString().split('T')[0] + 'T23:59';

const response = await fetch(`/lymPCComputadoras/php/api_horarios.php?desde=${desde}&hasta=${hasta}`);
const citas = await response.json();
console.log(citas);
```

### Ejemplo 2: Crear y luego recuperar una cita (admin)

```javascript
// Crear cita
const nuevaCita = {
  nombre: "Juan",
  apellido: "Gomez",
  correo: "juan@example.com",
  fecha: "2026-02-25 11:00:00",
  telefono: "3001111111",
  motivo: "Soporte tecnico"
};

const createResponse = await fetch('/lymPCComputadoras/php/api_horarios_admin.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify(nuevaCita)
});

const createdData = await createResponse.json();
const citaId = createdData.id;

// Recuperar la cita creada
const getResponse = await fetch(`/lymPCComputadoras/php/api_horarios_admin.php?ids=${citaId}`);
const citas = await getResponse.json();
console.log(citas[0]);
```

