<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}


$es_admin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') ? true : false;

$query = "SELECT p.*, s.Nombre AS nombre_contacto, s.Compania 
          FROM productos p 
          LEFT JOIN soporte s ON p.id_soporte = s.id_soporte
          WHERE p.categoria = 'PCHP'";
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
    <div class="container" style="margin-top: 100px;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Laptop HP Dell</h2>

            <?php if ($es_admin): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal"
                data-bs-whatever="@mdo">+ Agregar Nuevo Producto</button>

            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="background: #ffc107">
                            <header class="modal__header d-flex justify-content-between align-items-center mb-3">
                                <h3 class="modal__heading m-0 fw-bold text-white">Agregar Producto</h3>
                            </header>
                        </div>
                        <div class="modal-body">
                            <form action="../php/guardar_producto.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="nombre" class="col-form-label">Nombre:</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="serie" class="col-form-label">Serie:</label>
                                        <input type="text" class="form-control" id="serie" name="serie" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fecha" class="col-form-label">Fecha:</label>
                                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="unidades" class="col-form-label">Unidades:</label>
                                        <input type="number" class="form-control" id="unidades" name="unidades"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="precio" class="col-form-label">Precio:</label>
                                        <input type="number" step="0.01" class="form-control" id="precio" name="precio"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="imagen" class="form-label">Subir una Imagen del Producto:</label>
                                        <input type="file" class="form-control" id="imagen" name="imagen"
                                            accept="image/*" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="categoria" class="form-label">Categoría:</label>
                                        <select class="form-select" id="categoria" name="categoria" required>
                                            <option value="" selected>Seleccione la Categoría..</option>
                                            <option value="PCHP">HP DELL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary"
                                        data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-warning">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

       <div class="row" id="product-container">
            <?php if ($result && $result->rowCount() > 0): ?>
            <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>

            <div class="col-md-4 mb-4">

                <div class="box h-100 w-100">

                    <img src="../img/<?php echo htmlspecialchars($row['imagen']); ?>" alt="Producto"
                        style="width: 100%; height: 180px; object-fit: contain; padding-top: 10px;">

                    <div class="product-txt d-flex flex-column h-100 w-100">
                        <h3 class="product-name" style="font-size: 18px; font-weight: 600;">
                            <?php echo htmlspecialchars($row['nombre']); ?></h3>
                        <p class="text-muted" style="font-size: 14px; margin-bottom: 5px;">
                            <?php echo htmlspecialchars($row['serie']); ?></p>

                        <?php if (!empty($row['nombre_contacto'])): ?>
                        <p style="font-size: 13px; color: #198754; margin-bottom: 10px;">
                            <i class="fas fa-headset"></i> Soporte:
                            <b><?php echo htmlspecialchars($row['nombre_contacto']); ?></b>
                            (<?php echo htmlspecialchars($row['Compania']); ?>)
                        </p>
                        <?php endif; ?>

                        <div class="mt-auto w-100">
                            <p class="precio"
                                style="font-size: 20px; font-weight: 700; color: #ff9100; margin: 10px 0;">
                                $<?php echo number_format($row['precio'], 2); ?>
                            </p>
                            <p class="text-secondary" style="font-size: 13px;">Unidades disponibles:
                                <span class="product-units"
                                    data-id="<?php echo $row['id_producto']; ?>"><?php echo htmlspecialchars($row['unidades']); ?></span>
                            </p>

                            <button class="btn-3 agregar-libro w-100 border-0 mt-2" style="cursor: pointer;"
                                data-id="<?php echo $row['id_producto']; ?>">
                                Agregar al Carrito
                            </button>

                            <?php if ($es_admin): ?>
                            <div class="d-flex gap-2 mt-3">

                                <button type="button" class="btn btn-warning w-50 btn-sm text-dark fw-bold btn-editar"
                                    data-id="<?php echo $row['id_producto']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                    data-serie="<?php echo htmlspecialchars($row['serie']); ?>"
                                    data-fecha="<?php echo htmlspecialchars($row['fecha'] ?? ''); ?>"
                                    data-unidades="<?php echo htmlspecialchars($row['unidades']); ?>"
                                    data-precio="<?php echo htmlspecialchars($row['precio']); ?>"
                                    data-categoria="<?php echo htmlspecialchars($row['categoria'] ?? 'PCHP'); ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>

                                <button type="button" class="btn btn-danger w-50 btn-sm fw-bold"
                                    onclick="confirmarBorrado(<?php echo $row['id_producto']?>)">
                                    <i class="fas fa-trash"></i>Borrar
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>

            <div class="col-12 mt-4 text-center" id="no-results" style="display: none;">
                <div class="alert alert-warning shadow-sm">
                    <p>No se encontraron productos con ese nombre.</p>
                </div>
            </div>

            <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No hay productos disponibles en la categoría Hp Dell en este
                    momento.</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="modal fade" id="modalEditarUnico" tabindex="-1" aria-labelledby="modalLabelEditar" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: #ffc107">
                    <h5 class="modal-title fw-bold text-white" id="modalLabelEditar">Editar Producto</h5>
                </div>
                <div class="modal-body text-start">
                    <form action="../php/editar.php" method="POST" enctype="multipart/form-data">

                        <input type="hidden" id="edit_id_producto" name="id_producto" value="">

                        <div class="mb-3">
                            <label for="edit_nombre" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_serie" class="col-form-label">Serie:</label>
                            <input type="text" class="form-control" id="edit_serie" name="serie" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_fecha" class="col-form-label">Fecha:</label>
                            <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_unidades" class="col-form-label">Unidades:</label>
                            <input type="number" class="form-control" id="edit_unidades" name="unidades" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_precio" class="col-form-label">Precio:</label>
                            <input type="number" step="0.01" class="form-control" id="edit_precio" name="precio"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_imagen" class="form-label">Actualizar Imagen (Opcional):</label>
                            <input type="file" class="form-control" id="edit_imagen" name="imagen" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="edit_categoria" class="form-label">Categoría:</label>
                            <select class="form-select" id="edit_categoria" name="categoria" required>
                                <option value="" selected>Seleccione la Categoría..</option>
                                <option value="PCHP">HP DELL</option>
                            </select>
                        </div>

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                        </div>
                    </form>
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
    <script src="../js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>