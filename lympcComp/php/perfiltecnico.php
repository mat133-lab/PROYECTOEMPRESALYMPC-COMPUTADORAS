<?php
session_start();
//Requerimos la base de datos para buscar la ruta del archivo
require_once '../includes/db.php'; 

if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'tecnico') {
    header('Location: /php/login.php');
    exit();
}

// Por si acaso entran sin pasar por el login durante las pruebas
if (!isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}
$login_timestamp = $_SESSION['login_time'];

$UsuName = isset($_SESSION['rol']) ? htmlspecialchars($_SESSION['rol']) : 'Técnico';
$UsuEmail = $_SESSION['correo'] ?? 'Correo no proporcionado';
$UsuType = $_SESSION['rol'] ?? 'No especificado';
$UsusCedula = $_SESSION['cedula'] ?? 'No proporcionada';

// Buscar los archivos en la base de datos usando el ID del usuario
$UsuId = null;

if (!empty($_SESSION['id_usuario'])) {
    $UsuId = $_SESSION['id_usuario'];
} elseif (!empty($_SESSION['id'])) {
    $UsuId = $_SESSION['id'];
}

$UsuCopia = $_SESSION['archivo_cedula'] ?? '';
$UsuRuc = $_SESSION['archivo_ruc'] ?? '';

if ($UsuId) {
    // Buscamos específicamente al usuario logueado en la base de datos por su id_usuario
    $stmt = $conn->prepare("SELECT archivo_ruc, archivo_cedula, foto_perfil FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$UsuId]);
    $archivos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($archivos) {
        $UsuCopia = $archivos['archivo_cedula'] ?? $UsuCopia;
        $UsuRuc = $archivos['archivo_ruc'] ?? $UsuRuc;
        $UsuFoto = $archivos['foto_perfil'] ?? '';
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="../img/logo.webp">
    <title>Dashboard - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
     <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
            <?php 
            //Verificamos si el usuario actual es parte del personal para que nos muestre el carrito de compras
            $rolesStf = ['admin', 'tecnico', 'encargado', 'pasante'];
            $esStf = isset($_SESSION['rol']) && in_array(strtolower($_SESSION['rol']), $rolesStf);
            if(!$esStf):
            ?>
            <div class="submenu me-3">
                <img src="../img/canasta.webp" id="img-libro" alt="Canasta">

                <div id="libro">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Nombre</th>
                                <th>Serie</th>
                                <th>Fecha</th>
                                <th>Unidades</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody id="lista-libro"></tbody>
                    </table>

                    <div id="carrito-acciones" class="carrito-acciones disabled">
                        <div class="carrito-acciones-izquierda">
                            <button class="carrito-acciones-vaciar" id="carrito-acciones-vaciar">
                                Vaciar Carrito
                            </button>
                        </div>

                        <div class="carrito-acciones-derecha">
                            <div class="carrito-acciones-total">
                                <p>Compras Totales:</p>
                                <p id="total">$0</p>
                            </div>
                            <button class="carrito-acciones-comprar" id="carrito-acciones-comprar">
                                Comprar ahora
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php
            $rolesStf = ['admin', 'tecnico', 'encargado', 'pasante'];
            $esStf = isset($_SESSION['rol'])  && in_array(strtolower($_SESSION['rol']), $rolesStf);
            if(!$esStf):
            ?>
            <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <form class="d-none d-lg-flex m-0" role="search" onsubmit="event.preventDefault();">
                    <input class="form-control me-2" type="search" placeholder="Buscar..." aria-label="Search"
                        id="search-input" />
                    <button class="btn btn-success" type="button">Buscar</button>
                </form>

                <button class="navbar-toggler m-0" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">

                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">
                    <form class="d-flex d-lg-none mb-4" role="search" onsubmit="event.preventDefault();">
                        <input type="search" class="form-control me-2" placeholder="Buscar..." aria-label="Search"
                            id="search-input-mobile" />
                        <button class="btn btn-success" type="button">Buscar</button>
                    </form>

                    <ul class="navbar-nav flex-grow-1 pe-3">

                        <li class="nav-item">
                            <a class="nav-link categoria-link active" href="../php/dashboard.php"
                                data-categoria="todos">
                                Home
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Pc de Escritorio
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <a class="dropdown-item categoria-link" href="../php/pc.php"
                                        data-categoria="estructura">
                                        Pc Dell
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item categoria-link" href="../php/hpdell.php"
                                        data-categoria="techos">
                                        Hp Dell
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Laptops
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/asus.php"
                                        data-categoria="madera">ASUS</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/lenovo.php"
                                        data-categoria="pisos">LENOVO</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/omnibook.php"
                                        data-categoria="armarios">HP
                                        OMNIBOOK </a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/msi.php"
                                        data-categoria="armarios">MSI</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/dell.php"
                                        data-categoria="armarios">DELL</a>
                                </li>

                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Duplicadora
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/duplicadoracd.php"
                                        data-categoria="electricidad">CD</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/duplicadoradvd.php"
                                        data-categoria="iluminacion">DVD</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/duplicadorablu.php"
                                        data-categoria="domotica">BLU-RAY</a></li>
                            </ul>
                        </li>

                         <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Tablets
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/tablets.php" data-categoria="Tablets">Tablets IPad</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Servicio Tecnico
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/horario.php"
                                        data-categoria="bano">Horarios</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/contacto.php"
                                        data-categoria="bano">Contacto</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/gestion_citas.php"
                                        data-categoria="bano">Citas</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/ubicacion.php"
                                        data-categoria="bano">Ubicacion</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Impresoras con Tinta Continua
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/epson.php"
                                        data-categoria="pintura">EPSON</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/canon.php"
                                        data-categoria="pintura">CANON</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Tintas
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/tinta100.php"
                                        data-categoria="pintura">Tinta de
                                        100 ML</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/tinta1000.php"
                                        data-categoria="pintura">Tinta de
                                        1000 ML</a></li>
                            </ul>
                        </li>
                        <div class="d-flex align-items-center mt-3">

                            <?php if (isset($_SESSION['rol'])): ?> 
                            <span class="text-white me-3">Buen dia, <b><?php echo $_SESSION['usuario']; ?></b></span>
                            <a href="../php/logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
                            <?php else: ?>
                            <a href="../php/login.php" class="btn btn-light btn-sm me-2">Iniciar Sesión</a>
                            <a href="../php/register.php" class="btn btn-outline-light btn-sm">Registrarse</a>
                            <?php endif; ?>

                        </div>
                    </ul>
                </div>
            </div>
            <?php elseif($esStf): ?>
                <!-- Si el usuario es parte del personal lo redirigimo a su dashboard correspondiente si es tecnico, administrador, encargado o pasante lo redirigimos al dashboard correspondiente -->
            <?php if(strtolower($_SESSION['rol']) === 'admin'): ?>
                <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboardadmin.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <?php elseif(strtolower($_SESSION['rol']) === 'tecnico'): ?>
                <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard_tecnico.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <?php elseif(strtolower($_SESSION['rol']) === 'encargado'): ?>
                <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard_encargado.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <?php elseif(strtolower($_SESSION['rol']) === 'pasante'): ?>
                <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard_pasante.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <?php endif; ?>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">

                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Cuenta y Configuración
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <a class="dropdown-item categoria-link" href="../php/perfiltecnico.php"
                                        data-categoria="estructura">
                                        Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item categoria-link" href="#" data-categoria="techos">
                                        Configuracion
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item categoria-link" href="#" data-categoria="techos">
                                        Termino y Condiciones
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <div class="d-flex align-items-center">

                            <?php if (isset($_SESSION['rol'])): ?>
                            <span class="text-white me-3">Buen dia,
                                <b><?php echo $_SESSION['usuario']; ?></b></span>
                            <a href="../php/logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
                            <?php else: ?>
                            <a href="../php/login.php" class="btn btn-light btn-sm me-2">Iniciar Sesión</a>
                            <a href="../php/register.php" class="btn btn-outline-light btn-sm">Registrarse</a>
                            <?php endif; ?>

                        </div>

                    </ul>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </nav>

    <div class="main-content container mt-5 pt-4">

        <div class="dashboard-header text-left">
            <h1 style="color: #0caeff;">Hola, </h1>
            <h1 class="title"><?php echo $UsuName ?>🧑‍💼</h1>
            <label class="switch">
            <input type="checkbox" id="toggle">
            <span class="slider"></span>
        </label>
            <button type="button" id="pass" style="margin-bottom: 20px;">Contraseña</button>
        </div>

        <div class="dashboard-content">

            <ul class="nav nav-underline" id="perfilTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active bg-transparent border-0" id="general-tab" data-bs-toggle="tab"
                        data-bs-target="#general-pane" type="button" role="tab" aria-controls="general-pane"
                        aria-selected="true" style="color: #0d6efd; font-weight: 500;">General</button>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://drive.google.com" target="_blank"
                        rel="noopener noreferrer">Drive</a>
                </li>
            </ul>
        </div>

        <div class="tab-content p-4" id="perfilTabsContent"
            style="background-color: #f8f9fa; border-radius: 0 0 12px 12px;">

            <div class="tab-pane fade show active" id="general-pane" role="tabpanel" aria-labelledby="general-tab"
                tabindex="0">

                <div class="row g-4">

                    <div class="col-12 col-lg-4 d-flex flex-column gap-4">

                        <div class="card card-custom position-relative text-center overflow-hidden">
                            <div class="badge-admin" style="text-transform: uppercase;">
                                <?php echo $UsuType; ?></i>
                            </div>
                            <div class="foto-placeholder shadow-sm"
                                style="overflow: hidden; display: flex; justify-content: center; align-items: center; background-color: #e9ecef;">
                                <?php if(!empty($UsuFoto)): ?>
                                <img src="../uploads/profile/<?php echo $UsuFoto; ?>" alt="Foto de Perfil"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                <i class="fas fa-user" style="font-size: 3rem; color: #adb5bd;"></i>
                                <?php endif; ?>
                            </div>
                            <form action="../php/subir_foto.php" method="POST" enctype="multipart/form-data"
                                class="mt-3 mb-2">
                                <div class="submit-photo">
                                    <input type="file" id="foto-perfil" name="foto-perfil" accept="image/*"
                                        class="d-none" onchange="this.form.submit()">
                                    <label for="foto-perfil" class="for"
                                        style="cursor: pointer; padding: 10px 20px; border: 2px solid none; border-radius: 5px; background-color: rgb(255, 144, 8); color: #ffff">Subir
                                        Foto</label>
                                </div>
                            </form>
                        </div>

                        <div class="card card-custom gauge-card p-4">
                            <p class="gauge-title-label fw-bold mb-2 text-secondary text-center">Tiempo activo en sesión
                            </p>
                            <div class="gauge-svg-wrap mx-auto" style="width: 220px; height: 120px;">
                                <svg class="gauge-svg" viewBox="0 0 180 100" style="width: 100%; height: 100%;">
                                    <path class="gauge-bg" d="M 10 90 A 80 80 0 0 1 170 90" fill="none" stroke="#e9ecef"
                                        stroke-width="12" stroke-linecap="round" />
                                    <path id="gauge-arc" class="gauge-arc" d="M 10 90 A 80 80 0 0 1 170 90" fill="none"
                                        stroke="#22c55e" stroke-width="12" stroke-linecap="round"
                                        stroke-dasharray="251.2" stroke-dashoffset="251.2"
                                        style="transition: stroke-dashoffset 1s ease-out, stroke 1s ease-out;" />
                                    <line id="gauge-needle" class="gauge-needle" x1="90" y1="90" x2="25" y2="90"
                                        stroke="#333" stroke-width="4" stroke-linecap="round"
                                        style="transition: transform 1s ease-out; transform-origin: 90px 90px;" />
                                    <circle id="gauge-pivot" cx="90" cy="90" r="8" fill="#333" />
                                </svg>
                            </div>
                            <div id="gauge-time" class="gauge-time-display text-primary mt-2 text-center"
                                style="font-size: 2rem;">00:00:00</div>
                            <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                                <span id="gauge-badge" class="badge bg-success rounded-pill px-3 py-2">Bajo</span>
                                <span id="gauge-label" class="text-muted small">Calculando...</span>
                            </div>
                        </div>

                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="card card-custom p-4 h-100">

                            <div class="info-header mb-4 d-flex justify-content-between align-items-center">
                                <h5 class="m-0 text-secondary">Información del Usuario</h5>
                            </div>

                            <div class="row g-4">
                                <div class="col-12 pb-2 border-bottom">
                                    <h6 class="text-uppercase text-muted fw-bold mb-0"
                                        style="font-size: 0.8rem; letter-spacing: 0.5px;">
                                        Datos Personales
                                    </h6>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label" style="font-weight: 600; font-size: 0.85rem; color: #888;">
                                        Nombre Completo:</div>
                                    <div class="field-value text-muted fw-normal mt-1" style="font-size: 0.95rem;">
                                        <?php echo $UsuName; ?>
                                    </div>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label" style="font-weight: 600; font-size: 0.85rem; color: #888;">
                                        Correo Electrónico:</div>
                                    <div class="field-value text-muted fw-normal mt-1" style="font-size: 0.95rem;">
                                        <?php echo $UsuEmail; ?>
                                    </div>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label" style="font-weight: 600; font-size: 0.85rem; color: #888;">
                                        Cédula de Identidad:</div>
                                    <div class="field-value text-muted fw-normal mt-1" style="font-size: 0.95rem;">
                                        <?php echo $UsusCedula; ?>
                                    </div>
                                </div>


                                <div class="col-12 col-md-6 field-group">
                                    <div class="field-label" style="font-weight: 600; font-size: 0.85rem; color: #888;">
                                        Archivos Subidos:</div>
                                    <div class="field-value text-muted fw-normal mt-2" style="font-size: 0.95rem;">
                                        <?php if ($UsuCopia || $UsuRuc): ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php if ($UsuCopia && $UsuCopia !== 'NULL'): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="<?php echo htmlspecialchars($UsuCopia); ?>" target="_blank"
                                                    class="btn btn-sm btn-outline-primary"
                                                    style="padding: 0.2rem 0.6rem; font-size: 0.8rem;">
                                                    <i class="fas fa-eye me-1"></i> Ver Cédula
                                                </a>
                                                <a href="<?php echo htmlspecialchars($UsuCopia); ?>" download
                                                    class="btn btn-sm btn-primary"
                                                    style="padding: 0.2rem 0.6rem; font-size: 0.8rem;">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($UsuRuc && $UsuRuc !== 'NULL'): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="<?php echo htmlspecialchars($UsuRuc); ?>" target="_blank"
                                                    class="btn btn-sm btn-outline-success"
                                                    style="padding: 0.2rem 0.6rem; font-size: 0.8rem;">
                                                    <i class="fas fa-eye me-1"></i> Ver RUC
                                                </a>
                                                <a href="<?php echo htmlspecialchars($UsuRuc); ?>" download
                                                    class="btn btn-sm btn-success"
                                                    style="padding: 0.2rem 0.6rem; font-size: 0.8rem;">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-danger" style="font-size: 0.85rem;">No se ha subido ningún
                                            archivo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label" style="font-weight: 600; font-size: 0.85rem; color: #888;">
                                        Tipo de Usuario:</div>
                                    <div class="field-value text-muted fw-normal mt-2" style="font-size: 0.95rem;">
                                        <?php echo $UsuType; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>


    <footer class="footer">
        <div class="footer-content container">
            <div class="link">
                <h3>Pais - Ciudad</h3>
                <ul>
                    <li><a href="https://maps.app.goo.gl/BwLzsdgsGr3jjrmu5"> Ecuador - Quito</a></li>
                </ul>
            </div>
            <div class="link">
                <h3>Ubicaciones</h3>
                <ul>
                    <li><a href="https://maps.app.goo.gl/Hr7jt9W4ejWCdhmN7"> La Ecuatoriana - Las Orquídeas / Oe9 Martha
                            Bucaram / S37-49 / S37a</a></li>
                </ul>
            </div>
            <div class="link">
                <h3>Soporte</h3>
                <ul>
                    <li><a href="https://www.facebook.com/LyM010/about?locale=es_LA"> +593 98 309 3667</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
    window.gaugeLoginTime = <?php echo $login_timestamp; ?> * 1000;
    </script>

    <script src="../js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>