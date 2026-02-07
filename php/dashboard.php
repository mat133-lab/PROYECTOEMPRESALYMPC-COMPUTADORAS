<?php
session_start();
//comprobar si entro como usuario comun, tecnico, etc, pero especialmente para admin cuando 
// vaya a citas podra ver todas las citas si es otro usuario no podra ver la tabla citas
include_once '../includes/db.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
/*Si el administrador necesita volver al panel administrador le redirija al dashboard correspondiente dependiendo el usuario*/
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboardadmin.php");
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="../img/logo.webp">
    <title>Dashboard - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
            <!-- CARRITO / CANASTA -->
            <div class="submenu me-3">
                <img src="../img/canasta.webp" id="img-libro" alt="Canasta">

                <div id="libro">
                    <table id="lista-libro">
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
                        <tbody></tbody>
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

            <a class="navbar-brand" href="../php/dashboard.php">L&M PC Computadoras</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">

                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">
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
                                    <a class="dropdown-item categoria-link" href="../secciones/constructores.php"
                                        data-categoria="estructura">
                                        Pc Dell
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item categoria-link" href="#" data-categoria="techos">
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
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="madera">ASUS</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pisos">LENOVO</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="armarios">HP
                                        OMNIBOOK </a></li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="armarios">MSI</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="armarios">DELL</a>
                                </li>

                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Duplicadora
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="#"
                                        data-categoria="electricidad">CD</a></li>
                                <li><a class="dropdown-item categoria-link" href="#"
                                        data-categoria="iluminacion">DVD</a></li>
                                <li><a class="dropdown-item categoria-link" href="#"
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

                        <!-- BAÑO -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Servicio Tecnico
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/horario.php" data-categoria="bano">Horarios</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/contacto.php" data-categoria="bano">Contacto</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/gestion_citas.php"
                                        data-categoria="bano">Citas</a></li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="bano">Ubicacion</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Impresoras con Tinta Continua
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">EPSON</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">CANON</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Tintas
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">Tinta de
                                        100 ML</a></li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">Tinta de
                                        1000 ML</a></li>
                            </ul>
                        </li>
                        <div class="d-flex align-items-center">

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
        </div>
    </nav>

    <header class="header">
        <div class="header-content container">
            <div class="header-txt">
                <h1><span>Bienvenido a L&M PC Computadoras -
                    </span>Explora todo nuestro catalogo </h1>
                <p>
                    Hola usuario aqui encontraras todo lo que necesitas
                    relacionado a computacion, desde componentes hasta
                    accesorios y mucho mas.
                </p>
                <a href="https://www.facebook.com/LyM010?locale=es_LA" class="btn-1">Mas Informacion</a>
            </div>
        </div>

    </header>
    <section class="info">
        <div class="info-content container">
            <div class="info-img">
                <img src="../img/promocion.jpg" alt>
            </div>
            <div class="info-txt">
                <h2>Los mejores en accesorios para el Usuario</h2>
                <p>
                    En L&M PC Computadoras, nos enorgullece ofrecerte una amplia
                    gama de productos y accesorios de alta calidad para satisfacer todas tus
                    necesidades tecnológicas.
                </p>
                <a href="https://www.facebook.com/LyM010?locale=es_LA" class="btn-1">Mas Informacion</a>
            </div>
        </div>
    </section>
    <main class="products container">
        <h2>Productos Destacados</h2>
        <p>
            Las mejores ofertas y descuentos en productos seleccionados
        </p>
        <div class="box-container" id="lista-1">
            <div class="box" data-id="1">
                <img src="../uploads/asus.jpeg" alt>
                <div class="product-txt">
                    <h3>NOTEBOOK /ASUS RP058 CORE 7 240H/DISCO SOLIDO 1TB"1000gb" /MEMORIA RAM 16GB/RTX 5050 8G/
                        PANTALLA16" 144HZ</h3>
                    <p><span class="product-units" data-id="1">8</span> Unidades</p>
                    <p>Serie Ultra Core</p>
                    <p>precio inicial $1'666,00</p>
                    <p>Descuento de $150.00</p>
                    <p class="precio">$1'516.00</p>
                    <a href="#" class="agregar-libro btn-3" data-id="1">Agregar al carrito</a>
                </div>
            </div>
            <div class="box" data-id="2">
                <img src="../uploads/dell.jpeg" alt>
                <div class="product-txt">
                    <h3>PC DELL</h3>
                    <p><span class="product-units" data-id="2">8</span> Unidades</p>
                    <p>SERIE 7000</p>
                    <p>precio inicial $800.00</p>
                    <p>Descuento de $1.50</p>
                    <p class="precio">$798.50</p>
                    <a href="#" class="agregar-libro btn-3" data-id="2">Agregar al carrito</a>
                </div>
            </div>

            <div class="box" data-id="3">
                <img src="../uploads/tablets.webp" alt>
                <div class="product-txt">
                    <h3>Tablet</h3>
                    <p><span class="product-units" data-id="3">8</span> Unidades</p>
                    <p>Ipad</p>
                    <p>precio inicial $700</p>
                    <p>Descuento de $1.50</p>
                    <p class="precio">$698.50</p>
                    <a href="#" class="agregar-libro btn-3" data-id="3">Agregar al carrito</a>
                </div>
            </div>
            <div class="box" data-id="4">
                <img src="../uploads/tinta.webp" alt>
                <div class="product-txt">
                    <h3>Tinta</h3>
                    <p><span class="product-units" data-id="4">4</span> Unidades</p>
                    <p>EPSON 1000 ML</p>
                    <p>precio inicial $380</p>
                    <p>Descuento de $80</p>
                    <p class="precio">$300</p>
                    <a href="#" class="agregar-libro btn-3" data-id="4">Agregar al carrito</a>
                </div>
            </div>
            <div class="box" data-id="5">
                <img src="../uploads/duplicadora.webp" alt>
                <div class="product-txt">
                    <h3>Duplicadora</h3>
                    <p><span class="product-units" data-id="5">12</span> Unidades</p>
                    <p>Fax 2001</p>
                    <p>Precio Inicial $1200</p>
                    <p>Descuento de $400</p>
                    <p class="precio">$800</p>
                    <a href="#" class="agregar-libro btn-3" data-id="5">Agregar al carrito</a>
                </div>
            </div>

        </div>
        <div class="btn-2" id="load-more">Cargar mas Ofertas</div>
    </main>

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
    <script src="../js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>