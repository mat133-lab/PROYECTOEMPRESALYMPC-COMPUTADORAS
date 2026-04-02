<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../php/login_admin.php");
    exit();
}

$adminName = isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Administrador';
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

    <main class="container header">
        <div class="header-content">
            <div class="header-txt">
                <h1>Hola, <span><?php echo $adminName; ?></span></h1>
                <p>En esta seccion puede gestionar los productos que esten o no disponibles para los clientes.</p>
            </div>
        </div>

        <section class="products">
            <div class="container">
                <h2 style="text-align:center; margin-bottom:24px;">Acceso a los Productos</h2>
                <div class="box-container">
                    <a href="asus.php" class="box" style="text-decoration:none;">
                        <img src="../img/asus.webp" alt="Citas" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Asus</h3>
                            <p>Gestionar Productos de Asus</p>
                            <span class="precio">Gestionar Productos Asus</span>
                        </div>
                    </a>

                    <a href="canon.php" class="box" style="text-decoration:none;">
                        <img src="../img/canon.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Canon</h3>
                            <p>Gestionar Productos de Canon</p>
                            <span class="precio">Gestionar Productos Canon</span>
                        </div>
                    </a>

                    <a href="dell.php" class="box" style="text-decoration:none;">
                        <img src="../img/dell.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Dell</h3>
                            <p>Gestionar Productos Dell</p>
                            <span class="precio">Gestionar Productos Dell</span>
                        </div>
                    </a>

                    <a href="duplicadorablu.php" class="box" style="text-decoration:none;">
                        <img src="../img/dupli1.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Duplicadora Blu - Ray</h3>
                            <p>Gestionar Productos Duplicadoras Blu - Ray</p>
                            <span class="precio">Gestionar Productos Dell</span>
                        </div>
                    </a>

                    <a href="duplicadoracd.php" class="box" style="text-decoration:none;">
                        <img src="../img/dupli2.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Duplicadora CD</h3>
                            <p>Gestionar Productos Duplicadoras CD</p>
                            <span class="precio">Gestionar Productos CD</span>
                        </div>
                    </a>

                    <a href="duplicadoradvd.php" class="box" style="text-decoration:none;">
                        <img src="../img/dupli3.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Duplicadora DvD</h3>
                            <p>Gestionar Productos Duplicadoras DvD</p>
                            <span class="precio">Gestionar Productos DvD</span>
                        </div>
                    </a>

                    <a href="epson.php" class="box" style="text-decoration:none;">
                        <img src="../img/epson.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Epson</h3>
                            <p>Gestionar Productos Epson</p>
                            <span class="precio">Gestionar Productos Epson</span>
                        </div>
                    </a>

                    <a href="hpdell.php" class="box" style="text-decoration:none;">
                        <img src="../img/hpdell.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>HP Dell</h3>
                            <p>Gestionar Productos HP Dell</p>
                            <span class="precio">Gestionar Productos HP Dell</span>
                        </div>
                    </a>

                    <a href="lenovo.php" class="box" style="text-decoration:none;">
                        <img src="../img/lenovo.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Lenovo</h3>
                            <p>Gestionar Productos Lenovo</p>
                            <span class="precio">Gestionar Productos Lenovo</span>
                        </div>
                    </a>

                    <a href="msi.php" class="box" style="text-decoration:none;">
                        <img src="../img/msi.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>MSI</h3>
                            <p>Gestionar Productos MSI</p>
                            <span class="precio">Gestionar Productos MSI</span>
                        </div>
                    </a>

                    <a href="omnibook.php" class="box" style="text-decoration:none;">
                        <img src="../img/omnibook.webp" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Omnibook</h3>
                            <p>Gestionar Productos PC's (Generales)</p>
                            <span class="precio">Gestionar Productos PC</span>
                        </div>
                    </a>

                    <a href="tinta100.php" class="box" style="text-decoration:none;">
                        <img src="../img/tinta100.webp" alt="Soporte" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Tintas de 100 ML</h3>
                            <p>Gestionar Productos de Tinta de 100 ML </p>
                            <span class="precio">Gestionar Productos Tintas de 100 ML</span>
                        </div>
                    </a>

                    <a href="tinta1000.php" class="box" style="text-decoration:none;">
                        <img src="../img/tinta1000.webp" alt="Soporte" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Tintas de 1000 ML</h3>
                            <p>Gestionar Productos de Tinta de 1000 ML </p>
                            <span class="precio">Gestionar Productos Tintas de 1000 ML</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
        <div class="container footer-content">
            <div>
                <h3>L&M PC Computadoras</h3>
                <p style="max-width:320px; color:#bbb;">Panel de Productos Administrativo - Derechos Reservados</p>
            </div>
        </div>
    </footer>

    <script src="../js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>