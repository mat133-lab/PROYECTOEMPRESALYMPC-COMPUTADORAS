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
        <div class="container-fluid position-relative">
            <!-- CARRITO / CANASTA -->
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

            <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard.php" style="max-width: 45%; text-align: center;"> L&M PC Computadoras</a>
            <div class="d-flex align-items-center gap-2">
                <form class="d-none d-lg-flex m-0" role="search" onsubmit="event.preventDefault();">
                    <input class="form-control me-2" type="search" placeholder="Buscar..." aria-label="Search" id="search-input"/>
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
                        <input type="search" class="form-control me-2" placeholder="Buscar..." aria-label="Search" id="search-input-mobile"/>
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
                                <li><a class="dropdown-item categoria-link" href="../php/ubicacion.php" data-categoria="bano">Ubicacion</a>
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
        </div>
    </nav>
    <main id="product-container" class="grid-container">


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
            <div id="no-results" style="display: none; text-align: center; margin-bottom: 20px; width: 100%;">
                <p>No se Encontro el producto</p>
            </div>

            <div class="box-container" id="lista-1">
                <?php
            // Consultamos TODOS los productos de tu base de datos
            $stmt = $conn->prepare("SELECT * FROM productos ORDER BY id_producto ASC");
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Por cada producto en tu base de datos, creamos un lugar para guardarlo
            foreach ($productos as $row): 
            ?>
                <div class="box" data-id="<?= $row['id_producto'] ?>">
                    <img src="<?= htmlspecialchars($row['imagen']) ?>" alt="<?= htmlspecialchars($row['nombre']) ?>"
                        style="width: 100%; max-height: 200px; object-fit: contain;">
                    <div class="product-txt">
                        <h3 class="product-name"><?= htmlspecialchars($row['nombre']) ?></h3>
                        <p><span class="product-units"
                                data-id="<?= $row['id_producto'] ?>"><?= $row['unidades'] ?></span>
                            Unidades</p>
                        <p><?= htmlspecialchars($row['serie']) ?></p>
                        <p class="precio">$<?= number_format($row['precio'], 2) ?></p>
                        <a href="#" class="agregar-libro btn-3" data-id="<?= $row['id_producto'] ?>">Agregar al
                            carrito</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="btn-2" id="load-more" style="cursor: pointer; text-align: center;">Cargar mas Ofertas</div>
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
                        <li><a href="https://maps.app.goo.gl/Hr7jt9W4ejWCdhmN7"> La Ecuatoriana - Las Orquídeas / Oe9
                                Martha
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