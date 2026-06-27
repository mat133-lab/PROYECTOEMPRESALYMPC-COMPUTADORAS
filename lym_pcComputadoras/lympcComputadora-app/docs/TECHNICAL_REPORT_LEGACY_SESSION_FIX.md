
# Informe técnico: Error de `session_start()` en scripts legacy

Fecha: 2026-06-26

Resumen
--
Se corrigió un fallo que provocaba la excepción:

```
session_start(): Session ID is too long or contains illegal characters. Only the A-Z, a-z, 0-9, "-", and "," characters are allowed
```

Contexto
--
- Proyecto: `lympcComputadora-app` (Laravel) con una carpeta `resources/legacy/php` que contiene scripts PHP antiguos (legacy) ejecutados a través de `App\Support\Legacy\LegacyRuntime`.
- PHP: 8.3.30
- Error ocurrido al solicitar `POST /php/login.php` (script legacy `login.php`) porque el script hace `session_start()` inmediatamente.

Causa raíz
--
- El cliente envió una cookie `PHPSESSID` cuyo valor contenía datos serializados/encriptados (provenientes de Laravel u otras cookies), con caracteres fuera del alfabeto permitido por PHP para un session id.
- Al ejecutar `session_start()` en el script legacy, PHP valida el valor de la cookie `PHPSESSID` y lanza la excepción si contiene caracteres inválidos.
- La ejecución legacy ocurre dentro del proceso de Laravel: las cookies previas del entorno pueden estar presentes en `$_COOKIE` y provocar el fallo.

Cambios aplicados
--
1. Modificado: `app/Support/Legacy/LegacyRuntime.php`

Descripción del cambio:
- Antes de ejecutar el script legacy, ahora se comprueba la cookie con el nombre `session_name()` (por defecto `PHPSESSID`).
- Si la cookie existe y su valor contiene caracteres inválidos (no coincide con `/^[A-Za-z0-9,-]+$/`), la cookie es eliminada de `$_COOKIE` y se envía una cookie caducada al cliente (`setcookie(..., time() - 3600, '/')`).

Motivación:
- Evitar que `session_start()` falle por un ID inválido cuando el legacy script espera iniciar una sesión PHP nativa.

Archivos modificados
--
- `app/Support/Legacy/LegacyRuntime.php` (se sanitiza/elimina la cookie de sesión problemática antes de `require` del script legacy)

Pruebas recomendadas (local)
--
1. Iniciar servidor Laravel (desde la raíz del proyecto):

```bash
php artisan serve
```

2. Abrir el formulario de login legacy: `http://localhost:8000/php/login.php`
3. Enviar credenciales de prueba; verificar que no se presente la excepción `session_start()` y que el login funcione como antes.
4. Comprobar en el navegador que la cookie `PHPSESSID` se ha eliminado o reemplazado cuando el valor original era inválido.

Notas operativas y alternativas
--
- Esta solución es no invasiva: solo quita el valor de cookie cuando contiene caracteres inválidos. No intenta forzar un nuevo `session_id()` ni fusionar sesiones Laravel/legacy.
- Alternativa si se desea integración: renombrar el nombre de sesión usado por los scripts legacy (p. ej. `session_name('LEGACYSESSID')`) o adaptar Laravel para no emitir cookies que colisionen con `PHPSESSID`.

Registro de cambios (antes/durante/después)
--
- Antes: `LegacyRuntime::run()` ejecutaba `require $path` sin tocar `$_COOKIE`. Si `$_COOKIE[PHPSESSID]` tenía un valor inválido, `session_start()` en el legacy fallaba.
- Durante: Se detectó el valor inválido al leer `$_COOKIE[session_name()]` y se decidió eliminar la entrada y enviar una cookie caducada.
- Después: Los scripts legacy pueden ejecutar `session_start()` de forma segura y crear/usar sesiones nativas sin chocar con cookies preexistentes con formato inválido.

Recomendaciones futuras
--
- Revisar si otros nombres de cookie colisionan (ej. `laravel_session` vs `PHPSESSID`) y documentar la política de cookies.
- Considerar documentar una guía de migración para integrar sesiones Laravel con el legacy si se necesita compartir estado.

Contacto
--
Para más ajustes o despliegues, indícame si quieres que: 1) aplique tests automáticos, 2) cambie el nombre de sesión legacy, o 3) agregue más registros/monitoreo.
