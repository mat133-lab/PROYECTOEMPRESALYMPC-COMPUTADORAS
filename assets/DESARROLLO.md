# Guía de Desarrollo - L&M PC Computadoras

## Descripción para Desarrolladores

Este documento proporciona información técnica detallada para desarrolladores que deseen mantener, extender o contribuir a la aplicación L&M PC Computadoras.

## Arquitectura de la Aplicación

La aplicación sigue un patrón tradicional de Cliente-Servidor con separación de capas:

Capa de Presentación:
- HTML5 semántico
- CSS3 responsivo
- JavaScript ES6 con módulos

Capa de Lógica:
- PHP 7.x con sesiones
- Endpoints RESTful para APIs
- Validación de datos en servidor

Capa de Datos:
- Base de datos MySQL/MariaDB
- PDO para acceso a datos
- Prepared statements para seguridad

## Estructura de Carpetas Detallada

```
lymPCComputadoras/
  assets/                      - Documentación y activos
  css/
    auth.css                   - Estilos para login/registro
    estiloadmin.css           - Estilos del dashboard admin
    style.css                  - Estilos generales
    stylecitas.css            - Estilos de tablas de citas
    stylehorario.css          - Estilos del calendario
  img/                         - Imágenes del proyecto
  includes/
    db.php                     - Conexión PDO a la base de datos
  js/
    admin.js                   - Scripts del dashboard admin
    dashboard.js               - Scripts del dashboard usuario
    funciones-horario.js       - Funciones utilitarias calendario
    horario-calendario.js      - módulo calendario usuario
    horario-calendario-admin.js - módulo calendario admin
    selectores-horario.js      - selectores DOM calendario usuario
    selectores-horario-admin.js - selectores DOM calendario admin
  php/
    api_horarios.php           - API para usuarios
    api_horarios_admin.php     - API para administradores
    cart_action.php            - Carrito (no implementado)
    contacto.php               - Procesar contacto
    contactoadmin.php          - Ver contactos (admin)
    contraseña.php             - Cambiar contraseña
    dashboard.php              - Dashboard usuario
    dashboardadmin.php         - Dashboard admin
    gestion_citas.php          - Gestión citas
    gestion_citasU.php         - Citas de usuario
    horario.php                - Calendario usuario
    horarioadmin.php           - Calendario admin
    login.php                  - Login
    login_admin.php            - Login admin
    logout.php                 - Logout
    registro.php               - Registro
    registro_admin.php         - Registro admin
    reset_password.php         - Reset de contraseña
  uploads/                     - Archivos cargados (futuro)
  contraseña.html
  dashboard.html
  login.html
  registro.html
```

## Stack Tecnológico

### Backend

PHP 7.x: Lenguaje de servidor
PDO: Acceso a base de datos
MySQL/MariaDB: Base de datos relacional
Sessions: Gestión de autenticación

### Frontend

HTML5: Estructura semántica
CSS3: Estilos responsivos
JavaScript ES6: Módulos y funciones
Bootstrap 5.3.8: Framework CSS
Remixicon 4.6.0: Librería de iconos

## Flujo de Autenticación Detallado

1. Usuario accede a login.php o login_admin.php
2. Formulario HTML envia POST con usuario/correo y contraseña
3. Script PHP valida credenciales:
   - Consulta base de datos con email/usuario
   - Valida contraseña con password_verify()
   - Para admin, valida código especial
4. Si es válido:
   - Crea variable $_SESSION con datos del usuario
   - Redirige a dashboard correspondiente
5. Páginas protegidas verifican $_SESSION al inicio
6. Si sesión no existe, redirige a login
7. logout.php destruye la sesión

## Flujo de Crear/Editar Cita

Flujo Usuario:

1. Usuario rellena formulario en gestion_citasU.php
2. JavaScript envia POST a api_horarios.php (futuro) o submit a form tradicional
3. PHP inserta en tabla citas
4. Usuario ve cita en su calendario

Flujo Admin:

1. Admin abre horarioadmin.php
2. JavaScript renderiza calendario y tabla con citas del mes
3. Admin hace clic en "Nueva" o en una cita existente
4. Modal abre con formulario vacío o con datos
5. Admin rellena campos
6. JavaScript envia PUT (editar) o POST (crear) a api_horarios_admin.php
7. API PHP actualiza o crea en base de datos
8. JavaScript actualiza calendario y tabla
9. Usuario ve los cambios en su calendario

## Base de Datos - Esquema Detallado

### Tabla usuarios

```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) UNIQUE NOT NULL,
  correo VARCHAR(100) UNIQUE NOT NULL,
  contraseña VARCHAR(255) NOT NULL,
  rol VARCHAR(50) DEFAULT 'usuario'
);
```

Índices:
- PRIMARY KEY en id
- UNIQUE en usuario
- UNIQUE en correo
- Considerar INDEX en rol para queries frecuentes

Roles válidos:
- usuario: Usuario regular
- tecnico: Personal técnico
- encargado: Personal con responsabilidad
- pasante: Practicante
- admin: Administrador del sistema

### Tabla citas

```sql
CREATE TABLE citas (
  id_cita INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  fecha DATETIME NOT NULL,
  telefono VARCHAR(20),
  motivo TEXT
);
```

Índices Recomendados:
- PRIMARY KEY en id_cita
- INDEX en correo (para filtros de usuario)
- INDEX en fecha (para rangos de fechas)
- COMPOSITE INDEX en (correo, fecha) para queries comunes

### Tabla contacto

```sql
CREATE TABLE contacto (
  id_soporte INT AUTO_INCREMENT PRIMARY KEY,
  Nombre VARCHAR(100) NOT NULL,
  Correo VARCHAR(100) NOT NULL,
  Compania VARCHAR(100),
  Mensaje TEXT NOT NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Índices:
- PRIMARY KEY en id_soporte
- INDEX en Correo
- INDEX en fecha_creacion

## Módulos JavaScript

### selectores-horario.js

Exporta referencias DOM para el calendario de usuario:
- calendarHeading: h3 con fecha actual
- calendarDays: contenedor ol.calendar__days
- previousMonthBtn: botón mes anterior
- nextMonthBtn: botón mes siguiente
- modal: dialog elemento
- modalHeading: h3 del modal
- modalCalendarList: ul lista de citas en modal
- Etc.

### funciones-horario.js

Funciones utilitarias:
- formatTitle(title): Capitaliza y reemplaza guiones bajos
- formatDateString(date): Devuelve "dia de mes" en español
- formatTime(date): Devuelve hora en formato 12h
- formatDateRange([start, end]): Devuelve rango ISO para API
- reloadPage(): Recarga la página

### horario-calendario.js

Módulo principal del calendario de usuario:
- Clase UI: Métodos estáticos para manipulación DOM
- renderCalendar(): Inicializa calendarioactual
- getMonthlyAppointments(): Fetch a API con rango
- displayAppointmentsInCalendar(): Renderiza badges
- setMonth(step): Navega entre meses
- loadAppointmentsModal(): Abre modal al hacer clic
- openModal()/closeModal(): Control de dialog

### selectores-horario-admin.js

Similar a selectores-horario.js pero con selectores adicionales:
- Selectores del modal
- Selectores de campos de formulario
- Selectores de tabla

### horario-calendario-admin.js

Módulo del calendario administrativo:
- renderCalendar(): Inicializa y fetch de todas las citas
- setMonth(step): Navega meses
- populateForm(data): Llena modal con datos o vacío
- onSave(e): POST/PUT a API según si es nuevo o editar
- onDelete(): DELETE a API con confirmación
- Event listeners para tabla, modal, calendario

## Patrón de Módulos ES6

Todos los módulos JavaScript utilizan el patrón ES6:

```javascript
// Importar selectores y funciones
import { selector1, selector2 } from './selectores.js';
import { funcio1, funcio2 } from './funciones.js';

// Definir lógica
function funcionLocal() { }

// Exportar si es necesario
export { funcionLocal };

// Listeners al final
document.addEventListener('DOMContentLoaded', () => {
  // Inicializar
});
```

Ventajas:
- Evita contaminación del scope global
- Dependencias explícitas
- Fácil de testear
- Reusable en contextos diferentes

## Estándares de Código

### HTML

- Usar HTML5 semántico
- Atributos aria- para accesibilidad
- data- para información del DOM
- Comentarios para secciones mayores
- Validar con W3C validator

### CSS

- Usar convención BEM para clases
- Variables CSS para colores y espacios
- Mobile-first responsive design
- Separar por componente en archivo
- Comentar bloques principales

### JavaScript

- Usar const por defecto, let si cambio
- Nombres descriptivos en camelCase
- Funciones pequeñas y reutilizables
- Manejo de errores con try/catch
- Comentarios para lógica compleja

### PHP

- UTF-8 encoding
- Usar prepared statements siempre
- htmlspecialchars() para salida
- session_start() al inicio
- require_once para includes
- Comentarios PhpDoc para funciones

## Seguridad

### Inyección SQL

Prevención: Usar PDO prepared statements

```php
// Correcto
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// Incorrecto (vulnerable)
$query = "SELECT * FROM users WHERE email = '$email'";
```

### XSS (Cross-Site Scripting)

Prevención: htmlspecialchars() en salida HTML

```php
// Correcto
echo htmlspecialchars($user_input);

// Incorrecto (vulnerable)
echo $user_input;
```

### CSRF (Cross-Site Request Forgery)

Prevención (futuro): Implementar tokens CSRF en formularios

```php
// Generar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validar en POST
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    throw new Exception('Token inválido');
}
```

### Almacenamiento de Contraseñas

Correcto: password_hash() y password_verify()

```php
// Al registrar
$hash = password_hash($password, PASSWORD_BCRYPT);

// Al validar
if (password_verify($input_password, $stored_hash)) {
    // Contraseña válida
}
```

## Testing

### Testing Manual

1. Test de Login: Intentar acceso con credenciales correctas e incorrectas
2. Test de Sesión: Verificar que sesión persiste y se destruye
3. Test de Calendarios: Navegar meses, crear/editar/eliminar citas
4. Test de API: Usar Postman o curl para probar endpoints
5. Test Responsivo: Verificar en móvil, tablet, desktop

### Testing Automatizado (Futuro)

Considerar PHP Unit para tests de backend

```php
class CitasTest extends TestCase {
    public function testCrearCita() {
        // Test
    }
}
```

## Estadísticas del Código

Archivos PHP: 15+
Archivos JavaScript: 4+
Archivos CSS: 5+
Archivos HTML: 4+

Líneas de Código Estimadas:
- Backend: 2000+ líneas
- Frontend: 1500+ líneas
- Estilos: 800+ líneas

## Performance

### Optimizaciones Realizadas

1. CSS separado por módulo
2. JavaScript modular ES6
3. Prepared statements en PHP
4. Índices en base de datos

### Mejoras Futuras

1. Minificación de CSS y JS
2. Compresión de imágenes
3. Lazy loading de componentes
4. Caching de resultados de API
5. CDN para librerías externas

## Debugging

### Habilitación de Error Reporting

En includes/db.php:

```php
// Error reporting en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En producción
error_reporting(0);
ini_set('display_errors', 0);
```

### Herramientas Recomendadas

1. Firefox Developer Tools o Chrome DevTools
2. Postman para testing de APIs
3. phpMyAdmin para inspección de BD
4. VS Code con PHP Intelephense

### Logs

Las queries de error de PDO se lanzan como excepciones. Considerar:

```php
try {
    // código
} catch (PDOException $e) {
    error_log($e->getMessage());
    // Mostrar error genérico al usuario
}
```

## Extensiones Futuras

1. Busqueda avanzada de citas
2. Notificaciones por email
3. Integración con calendarios externos
4. Sistema de reportes
5. Dashboard de analytics
6. API con autenticación OAuth
7. App móvil nativa
8. Gestión de usuarios mejorada
9. Sistema de permisos granular
10. Historial de auditoría

## Contribución

Para contribuir al proyecto:

1. Fork del repositorio
2. Crear rama feature/fix-nombre
3. Hacer commits descriptivos
4. Enviar Pull Request
5. Esperar revisión y aprobación

Estándares de Commit:

- feat: Nueva característica
- fix: Corrección de bug
- docs: Cambios en documentación
- style: Cambios de formato
- refactor: Reorganización sin cambio funcional

Ejemplo: "feat: agregar búsqueda de citas"

## Contacto Técnico

Para preguntas técnicas, documentación de API o contribuciones, contactar al equipo de desarrollo en dev@lympc.com

