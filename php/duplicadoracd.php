<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$es_admin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') ? true : false;

$query = "SELECT * FROM productos WHERE categoria = 'DUPLICADORACD'";
$result = $conn->query($query); 
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
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="bano">Ubicacion</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Impresoras con Tinta Continua
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/epson.php" data-categoria="pintura">EPSON</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/canon.php" data-categoria="pintura">CANON</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Tintas
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/tinta100.php" data-categoria="pintura">Tinta de
                                        100 ML</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/tinta1000.php" data-categoria="pintura">Tinta de
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
    <div class="container" style="margin-top: 100px;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Duplicadoras de CD</h2>

            <?php if ($es_admin): ?>
            <a href="#" class="btn btn-success">
                <i class="fas fa-plus"></i> Agregar Nuevo Producto
            </a>
            <?php endif; ?>
        </div>

        <div class="row">
            <?php if ($result && $result->rowCount() > 0): ?>
            <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="../img/<?php echo $row['imagen']; ?>" class="card-img-top" alt="Producto"
                        style="height: 200px; object-fit: cover;">

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $row['nombre']; ?></h5>
                        <p class="card-text text-muted"><?php echo $row['serie']; ?></p>

                        <div class="mt-auto">
                            <h4 class="text-primary">$<?php echo number_format($row['precio'], 2); ?></h4>
                            <p class="text-secondary">Unidades disponibles: <span class="product-units"
                                    data-id="<?php echo $row['id_producto']; ?>"><?php echo $row['unidades']; ?></span>
                            </p>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary agregar-libro"
                                    data-id="<?php echo $row['id_producto']; ?>">Agregar al Carrito</button>

                                <?php if ($es_admin): ?>
                                <div class="d-flex gap-2 mt-2">
                                    <a href="../php/editar.php?id=<?php echo $row['id_producto']; ?>"
                                        class="btn btn-warning w-50">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="../php/eliminar.php?id=<?php echo $row['id_producto']; ?>"
                                        class="btn btn-danger w-50"
                                        onclick="return confirm('¿Estás seguro de borrar esto?');">
                                        <i class="fas fa-trash"></i> Borrar
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No hay productos disponibles en este momento.</div>
            </div>
            <?php endif; ?>
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
    <script src="../js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>