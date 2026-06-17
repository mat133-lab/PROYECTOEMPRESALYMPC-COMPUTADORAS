<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'asistente') {
    header('Location: ../php/login.php');
    exit();
}

$usuario = htmlspecialchars($_SESSION['usuario'] ?? 'asistente');
$usuarioId = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? ''; 

$alertType = '';
$alertMessage = '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <title>Grafica Asistente - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/carrusell_graficas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
            <?php 
            //Verificamos si el usuario actual es parte del personal para que nos muestre el carrito de compras
            $rolesStf = ['admin', 'tecnico', 'encargado', 'asistente'];
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
            $rolesStf = ['admin', 'tecnico', 'encargado', 'asistente'];
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
                                <li><a class="dropdown-item categoria-link" href="../php/tablets.php"
                                        data-categoria="Tablets">Tablets IPad</a>
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
                href="../php/dashboard_encargado.php" style="max-width: 45%; text-align: center;"> L&M PC
                Computadoras</a>
            <?php elseif(strtolower($_SESSION['rol']) === 'pasante'): ?>
            <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard_pasante.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <?php elseif(strtolower($_SESSION['rol']) === 'asistente'): ?>
            <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard_asistente.php" style="max-width: 45%; text-align: center;"> L&M PC
                Computadoras</a>
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
                                    <a class="dropdown-item categoria-link" href="../php/perfilasistente.php"
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

    <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <main class="container asistente-hero main-content" style="margin-top: 100px">
                    <!--Espacio dedicado para crear la grafica de las ultimas compras de los usuarios, llegada de productos al local,
        el incremento de envios tanto en la pagina contacto y citas de los usuarios asi como ver cuantos usuarios decidieron registrarse-->
                    <div class="grafica-container bg-white p-4 rounded shadow-sm">
                        <h2 class="mb-4 text-center">Grafica de Compras Recientes por los Usuarios</h2>
                        <div style="position: relative; height: 50vh; width: 100%;">
                            <canvas id="graficaAsistente"></canvas>
                            <div id="iaAnalisisGrafica0" class="ia-analisis-grafica ia-analisis-vacio">Cargando análisis de IA...</div>
                        </div>
                    </div>
                </main>
            </div>
            <div class="carousel-item">
                <main class="container asistente-hero main-content" style="margin-top: 100px">
                    <div class="grafica-container bg-white p-4 rounded shadow-sm">
                        <h2 class="mb-4 text-center">Grafica de la Cantidad de Productos Llegados al Local</h2>
                        <div style="position: relative; height: 50vh; width: 100%;">
                            <canvas id="graficaAsistente1"></canvas>
                            <div id="iaAnalisisGrafica1" class="ia-analisis-grafica ia-analisis-vacio">Cargando análisis de IA...</div>
                        </div>
                    </div>
                </main>
            </div>
            <div class="carousel-item">
                <main class="container asistente-hero main-content" style="margin-top: 100px">
                    <div class="grafica-container bg-white p-4 rounded shadow-sm">
                        <h2 class="mb-4 text-center">Grafica de los Datos enviados por el Usuario a la Pagina Contacto
                        </h2>
                        <div style="position: relative; height: 50vh; width: 100%;">
                            <canvas id="graficaAsistente2"></canvas>
                            <div id="iaAnalisisGrafica2" class="ia-analisis-grafica ia-analisis-vacio">Cargando análisis de IA...</div>
                        </div>
                    </div>
                </main>
            </div>
            <div class="carousel-item">
                <main class="container asistente-hero main-content" style="margin-top: 100px">
                    <div class="grafica-container bg-white p-4 rounded shadow-sm">
                        <h2 class="mb-4 text-center">Grafica de los Datos enviados por el Usuario a la Pagina Citas</h2>
                        <div style="position: relative; height: 50vh; width: 100%;">
                            <canvas id="graficaAsistente3"></canvas>
                            <div id="iaAnalisisGrafica3" class="ia-analisis-grafica ia-analisis-vacio">Cargando análisis de IA...</div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>

    </div>


    <footer class="footer mt-5">
        <div class="footer-content container">
            <div>
                <h3 style="text-align: center;">L&M PC Computadoras</h3>
                <p class="footer-note">Panel del Asistente con herramientas enfocadas en graficas de compras
                    rápidos.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../js/grafico_asistente.js"></script>

</body>

</html>