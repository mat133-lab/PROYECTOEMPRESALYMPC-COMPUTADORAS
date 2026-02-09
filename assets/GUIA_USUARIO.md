# Guía de Usuario - L&M PC Computadoras

## Contenido

1. Registro e Inicio de Sesión
2. Dashboard de Usuario
3. Solicitud de Citas
4. Visualización de Citas
5. Calendario de Usuario
6. Dashboard Administrativo
7. Gestión de Citas (Admin)
8. Calendario Administrativo

## 1. Registro e Inicio de Sesión

### Crear una Cuenta Nueva

1. Ve a la página de inicio: http://localhost/lymPCComputadoras/registro.html
2. Rellena el formulario de registro:
   - Campo Usuario: Elige un nombre de usuario único
   - Campo Correo: Ingresa tu correo electrónico válido
   - Campo Código Admin: Déjalo en blanco (es solo para administradores)
   - Campo Contraseña: Crea una contraseña segura (mínimo 8 caracteres recomendado)
3. Haz clic en el botón "Registrarse"
4. Si todo es correcto, tu cuenta será creada
5. Serás redirigido a la página de login

### Iniciar Sesión

1. Ve a http://localhost/lymPCComputadoras/login.html
2. Ingresa tu usuario o correo electrónico
3. Ingresa tu contraseña
4. Haz clic en "Iniciar Sesión"
5. Si las credenciales son correctas, serás redirigido a tu dashboard

### Recuperar Contraseña

Si olvidaste tu contraseña:

1. Ve a http://localhost/lymPCComputadoras/php/reset_password.php
2. Ingresa tu correo electrónico
3. Sigue las instrucciones que se enviarán a tu correo (si está implementado)

## 2. Dashboard de Usuario

Después de iniciar sesión, verás tu dashboard personal donde se muestra:

- Tu nombre de usuario en la esquina superior derecha
- Navegación a diferentes secciones de la aplicación
- Acceso a búsqueda de servicios
- Enlaces a formularios de citas
- Vista del calendario de citas personales

## 3. Solicitud de Citas

### Via Formulario de Citas

1. Desde tu dashboard, ve a la sección "Solicitar Cita"
2. Rellena el siguiente formulario:
   - Nombre: Tu nombre completo
   - Apellido: Tu apellido
   - Correo: Tu correo electrónico (se autocompleta)
   - Fecha: Selecciona la fecha y hora deseada
   - Teléfono: Tu número de contacto
   - Motivo: Describe el problema o servicio que necesitas
3. Haz clic en "Guardar Cita"
4. Recibirás una confirmación si la cita fue registrada exitosamente
5. Puedes ver tu cita en el calendario personal

### Información a Proporcionar

Nombre: Tu primer nombre
Apellido: Tu apellido o apellidos
Correo: Email válido para contacto
Fecha: Elige fecha disponible (formato YYYY-MM-DD)
Hora: Selecciona la hora que prefieras
Teléfono: Número de celular o fijo
Motivo: Descripción breve del servicio que necesitas

## 4. Visualización de Citas

### Ver mis Citas en Calendario

1. Desde tu dashboard, ve a la sección "Mi Horario" o "Calendario"
2. Verás un calendario del mes actual
3. Los días con citas agendadas mostrarán tu nombre en una etiqueta naranja
4. Los botones de flecha navegan entre meses

### Ver Detalles de una Cita

1. En el calendario, haz clic en tu nombre en el día de la cita
2. Se abrirá un modal con los detalles:
   - Nombre completo
   - Correo
   - Teléfono
   - Motivo
   - Hora exacta
3. Haz clic en "Cancelar" para cerrar el modal

## 5. Calendario de Usuario

El calendario muestra:

- Navegación por meses (botones Anterior/Siguiente)
- Todos los días del mes en un formato de grilla
- Badges verdes de cita en los días que tienen citas agendadas
- El nombre del cliente y motivo de la cita al pasar el mouse

Características Visuales:

Los badges de cita son cuadrados verdes que contienen:
- Nombre completo del cliente
- Al pasar el mouse, se muestra el motivo completo de la cita
- Color verde para fácil identificación de días con citas
- Animación al pasar el mouse (elevación y cambio de tono)

Funcionalidades:

- Navegar entre meses del año
- Ver todas tus citas del mes en badges verdes
- Haz clic en un badge de cita para ver detalles en el modal
- El calendario se actualiza automáticamente con nuevas citas

## 6. Dashboard Administrativo

Después de iniciar sesión como administrador, accederás a:

http://localhost/lymPCComputadoras/php/dashboardadmin.php

En el dashboard verás cuatro secciones de acceso rápido:

1. Gestión de Citas: Ir al calendario y tabla de citas
2. Registro de Admins: Registrar nuevos usuarios administrativos
3. Ver Contacto: Ver mensajes de soporte recibidos
4. Ver Citas de Usuarios: Ver citas desde otra vista

## 7. Gestión de Citas (Admin)

### Acceder a la Gestión

Ve a: http://localhost/lymPCComputadoras/php/horarioadmin.php

Verás una interfaz dividida en dos partes:

Lado Izquierdo:
- Calendario grande del mes atual
- Todos los días del mes
- Citas mostradas como etiquetas en los dias que corresponden
- Navegación entre meses

Lado Derecho:
- Tabla compacta de todas las citas
- Botón "Nueva" para crear una cita
- Columnas: ID, Nombre, Fecha, Acciones (Editar/Borrar)

### Crear una Cita Nueva

Metodo 1: Via Botón Nueva
1. Haz clic en el botón "Nueva" (esquina superior derecha de la tabla)
2. Se abrirá un modal de edición vacío titulado "Nueva Cita"

Metodo 2: Via Calendario
1. Haz clic en un día del calendario donde desees agregar una cita
2. Aparecerá el modal de edición

En ambos casos, rellena los campos:

Campo Nombre: Nombre del cliente
Campo Apellido: Apellido del cliente
Campo Correo: Email del cliente
Campo Fecha: Selecciona fecha y hora (formato datetime)
Campo Telefono: Teléfono de contacto
Campo Motivo: Descripción del servicio

Luego haz clic en "Guardar" para crear la cita.

### Editar una Cita Existente

Metodo 1: Via Tabla
1. En la tabla de citas, encuentra la cita que deseas editar
2. Haz clic en el botón "Editar" (botón azul)
3. El modal se abrirá con los datos de la cita
4. Modifica los campos que necesites
5. Haz clic en "Guardar"

Metodo 2: Via Calendario
1. En el calendario, haz clic en la etiqueta de la cita
2. El modal se abrirá con los datos cargados
3. Edita los campos
4. Haz clic en "Guardar"

### Eliminar una Cita

Metodo 1: Via Tabla
1. En la tabla, busca la cita a eliminar
2. Haz clic en el botón "Borrar" (botón rojo)
3. Confirma la eliminación en el diálogo de confirmación
4. La cita será eliminada de la base de datos

Metodo 2: Via Modal
1. Abre el modal de edición de la cita (ver Editar)
2. Haz clic en el botón "Eliminar" (en el footer del modal)
3. Confirma la eliminación
4. La cita será eliminada

### Exportar a PDF

1. En la tabla de citas, haz clic en el botón "Exportar PDF"
2. Tu navegador abrirá la vista de impresión
3. Guarda como PDF o imprime directamente
4. El PDF contendrá todas las citas de la tabla

## 8. Calendario Administrativo

Características especiales del calendario:

- Vista de mes completo
- Muestra todas las citas del mes con badges verdes
- Navega entre meses con botones Anterior/Siguiente
- Haz clic en un badge de cita para editar
- Citas mostradas con nombre del cliente y motivo

Elementos Visuales:

- Color verde: Badges con citas (nombre cliente + motivo)
- Fondos grises: Días con citas para fácil identificación
- Badges interactivos: Cambian de color al pasar el mouse
- Información al pasar el mouse: Muestra nombre completo y motivo

Interacción:

- Un clic en el badge abre el modal de edición
- Desde el modal puedes editar o eliminar la cita
- La tabla de la derecha muestra todas las citas ordenadas por fecha

## Cierre de Sesión

Para cerrar sesión:

1. Haz clic en el botón "Salir" en la esquina superior derecha
2. Tu sesión se cerrará
3. Serás redirigido a la página de login

## Consejos de Seguridad

- No compartas tu contraseña con otros usuarios
- Cierra sesión antes de dejar la computadora desatendida
- Utiliza contraseñas fuertes (mayúsculas, números, caracteres especiales)
- Verifica siempre la URL en la barra de direcciones
- Mantén tu navegador y sistema operativo actualizados

## Preguntas Frecuentes

Pregunta: Me aparece un error "No autorizado"
Respuesta: El error indica que tu sesión ha expirado o no tienes permisos suficientes. Intenta iniciar sesión nuevamente.

Pregunta: Mi cita no aparece en el calendario
Respuesta: Verifica que la fecha sea correcta. También asegúrate de haber hecho clic en "Guardar". Si el problema persiste, recarga la página.

Pregunta: No puedo editar citas de otros usuarios
Respuesta: Los usuarios comunes solo pueden ver sus propias citas. Solo los administradores pueden ver y editar todas las citas.

Pregunta: La contraseña no funciona
Respuesta: Las contraseñas son sensibles a mayúsculas y minúsculas. Verifica que esté escribiendo correctamente. Si continúa fallando, intenta el reset de contraseña.

Pregunta: No recuerdo mi contraseña
Respuesta: Ve a la página de recuperación de contraseña e ingresa tu correo. Si el sistema está configurado, recibirás instrucciones de reset.

