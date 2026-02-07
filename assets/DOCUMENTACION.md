# Documentación - L&M PC Computadoras

## Descripción General del Proyecto

L&M PC Computadoras es una aplicación web de gestión de citas y horarios para una empresa de servicios técnicos de computadoras. La aplicación permite a usuarios registrarse, solicitar citas de servicio, y a personal administrativo gestionar, ver, editar y eliminar citas desde una interfaz de calendario interactivo.

## Características Principales

### Para Usuarios Comunes
- Registro e inicio de sesión con validación de contraseñas
- Visualización de formulario para solicitar citas
- Historial de citas personales en calendario interactivo
- Visualización de detalles de citas por fecha

### Para Personal Administrativo
- Acceso a calendario administrativo con vista completa de citas
- Tabla compacta de todas las citas registradas
- Capacidad de crear nuevas citas
- Edición de detalles de citas existentes
- Eliminación de citas
- Exportación a PDF de citas registradas

## Estructura del Proyecto

La aplicación está organizada en la siguiente estructura:

```
lymPCComputadoras/
  css/              - Estilos CSS
  img/              - Imágenes del proyecto
  includes/         - Archivos de inclusión PHP
  js/               - Scripts JavaScript
  php/              - Archivos PHP del servidor
  uploads/          - Directorio para archivos subidos
  assets/           - Documentación y complementos
```

## Tecnologías Utilizadas

Backend:
- PHP 7.x con extensión PDO
- Base de datos SQL (MySQL/MariaDB)
- Sesiones de PHP para autenticación

Frontend:
- HTML5 semántico
- CSS3 con diseño responsivo
- JavaScript ES6 con módulos
- Bootstrap 5.3.8 para componentes UI
- Remixicon 4.6.0 para iconos

## Roles de Usuario

La aplicación maneja los siguientes roles:

Usuario Comun: Acceso limitado a formulario de citas y visualización personal del calendario

Tecnico: Acceso administrativo completo a citas

Encargado: Acceso administrativo completo a citas

Pasante: Acceso administrativo completo a citas

Admin: Acceso administrativo completo, incluyendo gestión de usuarios

## Flujo de Autenticación

1. Usuario ingresa a página de login
2. Ingresa credenciales (usuario/correo y contraseña)
3. Sistema valida contra base de datos
4. Si es válido, se crean variables de sesión
5. Usuario redirigido a su dashboard correspondiente
6. Sesión se mantiene durante la navegación
7. Al logout, sesión se destruye

## Variables de Sesión

Las variables de sesión que se utilizan son:

- $_SESSION['usuario']: Nombre de usuario
- $_SESSION['correo']: Correo electrónico del usuario
- $_SESSION['rol']: Rol del usuario (usuario, tecnico, encargado, pasante, admin)
- $_SESSION['id']: ID del usuario en la base de datos
- $_SESSION['admin_logged_in']: Bandera booleana para admin
- $_SESSION['admin_name']: Nombre del admin logueado

## Base de Datos

La aplicación utiliza tres tablas principales:

### Tabla usuarios
Campos: id, usuario, correo, contraseña (hash), rol
Almacena los datos de autenticación de usuarios

### Tabla citas
Campos: id_cita, nombre, apellido, correo, fecha, telefono, motivo
Almacena todas las citas registradas

### Tabla contacto
Campos: id_soporte, Nombre, Correo, Compania, Mensaje
Almacena mensajes de soporte/contacto enviados por usuarios

## Esquema de Colores

Color primario: #ff9100 (Naranja)
Color primario hover: #ff7b00 (Naranja oscuro)
Color de texto principal: #1D1D1B (Gris oscuro)
Color de fondo claro: #f7f9fb (Gris muy claro)
Color de fondo secundario: #f5f5f5 (Gris claro)

## Seguridad

La aplicación implementa las siguientes medidas de seguridad:

- Contraseñas almacenadas con hash usando password_hash()
- Validación de contraseñas con password_verify()
- Protección de sesión en páginas administrativas
- Validación de roles para acceso a funcionalidades
- Uso de prepared statements en consultas SQL para prevenir inyección
- Código administrativo especial (ADMIN2026) para registro de administradores
- Limpieza de salida con htmlspecialchars() en puntos críticos

## Mantenimiento y Actualizaciones

### Tablas de Cookies
Las sesiones se manejan exclusivamente con sesiones PHP del servidor, sin cookies en el navegador (excepto la sesión de PHP).

### Borrado de Datos
Los datos antiguos no se borran automáticamente. Se recomienda implementar un proceso de archivado para citas antiguas.

### Backups
Se recomienda hacer backups regulares de la base de datos para proteger la información de citas.

## Contacto y Soporte

Para reportar problemas, errores o sugerencias de mejora, dirígete al administrador del sistema o crea un ticket en el sistema de soporte integrado en la aplicación.

