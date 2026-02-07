# Guía de Instalación - L&M PC Computadoras

## Requisitos Previos

Antes de instalar la aplicación, asegúrate de contar con:

- XAMPP instalado (versión 7.4 o superior) con PHP 7.x
- MySQL o MariaDB corriendo
- Navegador web moderno (Chrome, Firefox, Edge, Safari)
- Acceso a la carpeta htdocs de XAMPP

## Pasos de Instalación

### 1. Descarga y Ubicación

Descarga los archivos del proyecto y colócalos en la carpeta:

```
C:\xampp\htdocs\lymPCComputadoras
```

Tu estructura de carpetas debe verse así:

```
C:\xampp\htdocs\
  lymPCComputadoras/
    css/
    img/
    includes/
    js/
    php/
    uploads/
    assets/
    contraseña.html
    dashboard.html
    login.html
    registro.html
```

### 2. Configuración de la Base de Datos

#### Paso 2.1: Abre phpMyAdmin

Abre tu navegador y ve a:

```
http://localhost/phpmyadmin
```

Inicia sesión con las credenciales de XAMPP (usuario: root, sin contraseña por defecto).

#### Paso 2.2: Crea la Base de Datos

Haz clic en "Nueva base de datos" y crea una con el nombre:

```
lympc_db
```

#### Paso 2.3: Crea la Tabla usuarios

En la base de datos recién creada, abre la pestaña SQL y ejecuta:

```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) UNIQUE NOT NULL,
  correo VARCHAR(100) UNIQUE NOT NULL,
  contraseña VARCHAR(255) NOT NULL,
  rol VARCHAR(50) DEFAULT 'usuario'
);
```

#### Paso 2.4: Crea la Tabla citas

Ejecuta:

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

#### Paso 2.5: Crea la Tabla contacto

Ejecuta:

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

### 3. Configuración del Archivo de Conexión

#### Paso 3.1: Abre el archivo de conexión

Abre:

```
includes/db.php
```

#### Paso 3.2: Verifica/Actualiza los datos de conexión

Asegúrate de que contiene:

```php
<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "lympc_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
```

Ajusta los valores si tu configuración es diferente.

### 4. Crear Usuario Administrativo Inicial

#### Opción A: Via phpMyAdmin

En phpMyAdmin, ve a la tabla usuarios e inserta un registro:

- usuario: admin
- correo: admin@lympc.com
- contraseña: (usa la función de PHP) md5 o bcrypt de tu contraseña
- rol: admin

#### Opción B: Via Formulario de Registro

1. Abre en tu navegador:

```
http://localhost/lymPCComputadoras/registro.html
```

2. Rellena el formulario con usuario y correo
3. En el campo de código de adminisrador ingresa: ADMIN2026
4. Crea la contraseña
5. Haz clic en Registrarse

Verifica que se haya creado correctamente en phpMyAdmin.

### 5. Verificar la Instalación

#### Paso 5.1: Inicia XAMPP

Asegúrate de que los servicios Apache y MySQL estén corriendo en XAMPP Control Panel.

#### Paso 5.2: Accede a la Aplicación

En tu navegador, ve a:

```
http://localhost/lymPCComputadoras/login.html
```

Deberías ver la página de login. Si no carga, revisa:
- Que los servicios de XAMPP estén activos
- Que el archivo carpeta exista en htdocs
- Los permisos de la carpeta

#### Paso 5.3: Prueba de Acceso

1. Intenta iniciar sesión con las credenciales del admin creado
2. Si el login es exitoso, serás redirigido al dashboard administrativo
3. Verifica que puedas:
   - Ver el calendario
   - Ver la tabla de citas
   - Crear una nueva cita
   - Editar una cita existente
   - Eliminar una cita

### 6. Configuración Adicional

#### Directorio de Cargas

La carpeta uploads debe tener permisos de escritura. Si tienes problemas:

1. Haz clic derecho en la carpeta uploads
2. Selecciona Propiedades
3. Ve a Seguridad
4. Asegúrate de que el usuario de XAMPP tiene permisos de lectura/escritura

#### Zona Horaria (Opcional)

Si las fechas no se muestran correctamente, agrega al inicio de php/horarioadmin.php:

```php
date_default_timezone_set('America/Bogota');
```

## Solución de Problemas Comunes

### Error: Página en blanco

- Verifica que PHP esté habilitado en XAMPP
- Revisa el archivo de log de Apache: C:\xampp\apache\logs\error.log
- Asegúrate de que la base de datos y tablas existan

### Error: No se puede conectar a la base de datos

- Verifica que MySQL esté corriendo en XAMPP
- Revisa que los datos de conexión en db.php sean correctos
- Confirma que la base de datos lympc_db existe

### Error: Login no funciona

- Verifica que la tabla usuarios existe
- Confirma que los datos del usuario fueron insertados correctamente
- Revisa que la contraseña fue codificada correctamente (password_hash)

### Error: El calendario no muestra citas

- Verifica que la tabla citas existe y tiene registros
- Asegúrate de que el formato de fecha es YYYY-MM-DD HH:MM:SS
- Revisa la consola del navegador (F12) para errores de JavaScript

### Sesión se cierra constantemente

- Verifica que session.save_path en php.ini tiene la ruta correcta
- Comprueba que el directorio tiene permisos de escritura
- Aumenta el session.gc_maxlifetime en php.ini si es necesario

## Actualización

Para actualizar la aplicación:

1. Haz backup de la base de datos
2. Reemplaza los archivos PHP y JavaScript
3. Ejecuta cualquier script de migración de base de datos si lo hay
4. Limpia la caché del navegador (Ctrl+Shift+Del)
5. Prueba todas las funcionalidades

## Desinstalación

Para desinstalar:

1. Haz un backup de la base de datos en phpMyAdmin
2. Elimina la carpeta lymPCComputadoras de htdocs
3. Opcionalmente, elimina la base de datos lympc_db en phpMyAdmin

