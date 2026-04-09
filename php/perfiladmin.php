<?php
session_start();
//Requerimos la base de datos para buscar la ruta del archivo
require_once '../includes/db.php'; 

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../php/login_admin.php");
    exit();
}

$adminName = isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Administrador';
$adminEmail = $_SESSION['correo'] ?? 'Correo no proporcionado';
$adminType = $_SESSION['rol'] ?? 'No especificado';
$adminCedula = $_SESSION['cedula'] ?? 'No proporcionada';

// Buscar los archivos en la base de datos usando el ID del usuario
$adminId = null;

if (!empty($_SESSION['id_usuario'])) {
    $adminId = $_SESSION['id_usuario'];
} elseif (!empty($_SESSION['id'])) {
    $adminId = $_SESSION['id'];
}

$adminCopia = $_SESSION['archivo_cedula'] ?? '';
$adminRuc = $_SESSION['archivo_ruc'] ?? '';

if ($adminId) {
    // Buscamos específicamente al usuario logueado en la base de datos por su id_usuario
    $stmt = $conn->prepare("SELECT archivo_cedula, archivo_ruc FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$adminId]);
    $archivos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($archivos) {
        $adminCopia = $archivos['archivo_cedula'] ?? $adminCopia;
        $adminRuc = $archivos['archivo_ruc'] ?? $adminRuc;
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
            $rolesStf = ['admin'];
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
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
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="herramientas"></a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="maquinaria"></a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="seguridad"></a>
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

                            <?php if (isset($_SESSION['usuario'])): ?>
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
            <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboardadmin.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">

                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Cuenta y Configuración
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <a class="dropdown-item categoria-link" href="../php/perfiladmin.php"
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

                            <?php if (isset($_SESSION['admin_name'])): ?>
                            <span class="text-white me-3">Buen dia,
                                <b><?php echo $_SESSION['admin_name']; ?></b></span>
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

        <div class="dashboard-header text-center">
            <h1><?php echo $adminName ?></h1>
            <button type="button" id="pass">Contraseña</button>
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
                    <div class="col-12 col-lg-4">
                        <div class="card card-custom position-relative text-center overflow-hidden h-100">
                            <div class="badge-admin">
                                ADMINISTRADOR <i class="fas fa-chevron-down ms-1"></i>
                            </div>

                            <div class="foto-placeholder shadow-sm">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="card card-custom p-4 h-100">
                            <div class="info-header">
                                <h5 class="m-0 text-secondary">Información del Usuario</h5>
                                <a href="#" class="text-muted text-decoration-none"
                                    style="font-size: 0.9rem;">editar</a>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 field-group">
                                    <div class="field-label">Nombre de Usuario:</div>
                                    <div class="field-value text-muted fw-normal" style="font-size: 0.95rem;">
                                        <?php echo $adminName; ?></div>
                                </div>

                                <div class="col-12 field-group position-relative">
                                    <div class="field-label">Correo Electronico:</div>
                                    <div class="field-value text-muted fw-normal" style="font-size: 0.95rem;">
                                        <?php echo $adminEmail; ?></div>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label">Numero de Cedula:</div>
                                    <div class="field-value text-muted fw-normal" style="font-size: 0.95rem;">
                                        <?php echo $adminCedula; ?></div>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label">Copia de Cedula:</div>
                                    <div class="field-value text-muted fw-normal" style="font-size: 0.95rem;">
                                        <?php if(!empty($adminCopia) && $adminCopia !== 'NULL'): ?>
                                            <div class="d-flex gap-2 mt-1">
                                                <a href="<?php echo htmlspecialchars($adminCopia); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i> Ver
                                                </a>
                                                <a href="<?php echo htmlspecialchars($adminCopia); ?>" download class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download me-1"></i> Descargar
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-danger" style="font-size: 0.85rem;">Documento no Proporcionado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label">Copia de RUC:</div>
                                    <div class="field-value text-muted fw-normal" style="font-size: 0.95rem;">
                                        <?php if (!empty($adminRuc) && $adminRuc !== 'NULL'): ?>
                                            <div class="d-flex gap-2 mt-1">
                                                <a href="<?php echo htmlspecialchars($adminRuc); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-eye me-1"></i> Ver
                                                </a>
                                                <a href="<?php echo htmlspecialchars($adminRuc); ?>" download class="btn btn-sm btn-success">
                                                    <i class="fas fa-download me-1"></i> Descargar
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-danger" style="font-size: 0.85rem;">No se ha subido ningún archivo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 field-group">
                                    <div class="field-label">Tipo de Usuario:</div>
                                    <div class="field-value text-muted fw-normal" style="font-size: 0.95rem;">
                                        <?php echo $adminType; ?></div>
                                </div>


                            </div>

                            <div class="mt-4 dropdown">
                                <a href="#" class="link-action" data-bs-toggle="dropdown"
                                    aria-expanded="false">Seleccionar campo</a>
                                <a href="#" class="link-action ms-3">Crear campo</a>

                                <ul class="dropdown-menu border-0 shadow py-2" style="border-radius: 8px;">
                                    <li><a class="dropdown-item text-muted py-2" href="#">Correo electrónico de
                                            contacto</a></li>
                                    <li><a class="dropdown-item text-muted py-2" href="#">Departamento</a></li>
                                    <li><a class="dropdown-item text-muted py-2" href="#">Registrado el</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <footer class="footer">
        <div class="container footer-content">
            <div>
                <h3>L&M PC Computadoras</h3>
                <p style="max-width:320px; color:#bbb;">Perfil administrativo - Derechos Reservados</p>
            </div>
        </div>
    </footer>

    <script src="../js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>