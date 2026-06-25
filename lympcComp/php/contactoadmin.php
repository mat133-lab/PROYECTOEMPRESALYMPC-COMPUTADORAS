<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Si el usuario NO es admin, lo enviamos a contacto.php (vista normal)
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: /php/contacto.php");
    exit();
}

if (isset($_POST['enviar'])) {
    $nombre = trim($_POST['name']);
    $correo = trim($_POST['email']);
    $compania = trim($_POST['company']);
    $mensaje = trim($_POST['message']);
    $cedula = trim($_POST['cedula']); // Nuevo campo
    
    $id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

    if(!empty($nombre) && !empty($correo) && !empty($mensaje) && !empty($cedula)){
        
        $ruta_ruc = null;
        $ruta_cedula = null;
        $directorio_subida = "../docs/";

        // Procesar subida de RUC
        if (isset($_FILES['archivo_ruc']) && $_FILES['archivo_ruc']['error'] === UPLOAD_ERR_OK){
            $destino_ruc = $directorio_subida . time() . "_ruc_" . basename($_FILES['archivo_ruc']['name']);
            if (move_uploaded_file($_FILES['archivo_ruc']['tmp_name'], $destino_ruc)) $ruta_ruc = $destino_ruc;
        }

        // Procesar subida de Cédula
        if (isset($_FILES['archivo_cedula']) && $_FILES['archivo_cedula']['error'] === UPLOAD_ERR_OK){
            $destino_cedula = $directorio_subida . time() . "_cedula_" . basename($_FILES['archivo_cedula']['name']);
            if (move_uploaded_file($_FILES['archivo_cedula']['tmp_name'], $destino_cedula)) $ruta_cedula = $destino_cedula;
        }

        // INSERT actualizado con los nuevos campos
        $stmt = $conn->prepare("INSERT INTO contacto (id_usuario, nombre, correo, compania, mensaje, cedula, archivo_ruc, archivo_cedula) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_usuario, $nombre, $correo, $compania, $mensaje, $cedula, $ruta_ruc, $ruta_cedula]);
        
        header("Location: /php/contactoadmin.php?status=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactanos - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/stylecitas.css">
    <link rel="stylesheet" href="../css/footer.css">
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
    <div class="main-content" style="margin-top: 100px;">
        <h1>Contactanos - Administrador</h1>
        <div class="text-center">
            <p>Hola <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>, en esta sección puedes ver todos los mensajes enviados por los clientes.</p>
        </div>

        <div class="form-section">
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> Mensaje de prueba enviado exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <p>Nombre</p>
                    <input type="text" name="name" id="Nombre" required>
                </div>
                <div class="form-group">
                    <p>Correo Electronico</p>
                    <input type="email" name="email" id="Correo" required>
                </div>

                <div class="form-group mb-3">
                    <p>Número de Cédula (Requerido)</p>
                    <input type="text" class="form-control" name="cedula" id="cedula" maxlength="10" required>
                </div>

                <div class="form-group">
                    <p>Compania u Organizacion</p>
                    <input type="text" name="company" id="Compania">
                </div>

                <div id="div_documentos" class="border p-3 mb-3 bg-white">
                    <div class="mb-3">
                        <label for="archivo_ruc" class="form-label">Subir RUC (Opcional si no es empresa)</label>
                        <input class="form-control" type="file" name="archivo_ruc" id="archivo_ruc" accept=".pdf, .jpg, .png, .webp">
                    </div>
                    
                    <div class="mb-3">
                        <label for="archivo_cedula" class="form-label">Subir Copia de Cédula</label>
                        <input class="form-control" type="file" name="archivo_cedula" id="archivo_cedula" accept=".pdf, .jpg, .png, .webp">
                    </div>
                </div>

                <div class="form-group">
                    <p>Mensaje</p>
                    <textarea name="message" id="Mensaje" rows="5" required></textarea>
                </div>
                <div class="form-group full">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        Enviar Prueba
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php
    $rolesConAcceso = ['admin', 'tecnico', 'encargado', 'pasante'];
    $puedeVerTabla = isset($_SESSION['rol']) && in_array(strtolower($_SESSION['rol']), $rolesConAcceso);
    
    if ($puedeVerTabla) {
    ?>
    <div class="container mt-5 mb-5">
        <h1>Buzón de Mensajes de Clientes</h1>
        <div class="card compact-card shadow-sm p-3">
            <div class="table-wrapper" style="overflow-x: auto;">
                <table class="table table-striped table-hover compact-table">
                    <thead class="table-dark">
                        <tr>
                            <th># ID</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Correo</th>
                            <th>Compañía</th>
                            <th>Mensaje</th>
                            <th>Archivos (Docs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = $conn->query("SELECT * FROM contacto ORDER BY id_mensaje DESC");
                            if ($query->rowCount() > 0) {
                                foreach($query as $row){
                                    
                                    // Generar botones de descarga para RUC y Cédula
                                    $btnRuc = !empty($row['archivo_ruc']) ? "<a href='{$row['archivo_ruc']}' target='_blank' class='btn btn-sm btn-info mb-1 d-block' style='font-size:0.75rem;'>RUC</a>" : "";
                                    $btnCedula = !empty($row['archivo_cedula']) ? "<a href='{$row['archivo_cedula']}' target='_blank' class='btn btn-sm btn-secondary d-block' style='font-size:0.75rem;'>Cédula</a>" : "";
                                    $archivos = ($btnRuc == "" && $btnCedula == "") ? "<span class='text-muted' style='font-size:0.8rem;'>Ninguno</span>" : $btnRuc . $btnCedula;

                                    echo "<tr>
                                        <td>#{$row['id_mensaje']}</td>
                                        <td>{$row['fecha']}</td>
                                        <td>" . htmlspecialchars($row['nombre']) . "</td>
                                        <td>" . htmlspecialchars($row['cedula'] ?? 'N/A') . "</td>
                                        <td>" . htmlspecialchars($row['correo']) . "</td>
                                        <td>" . htmlspecialchars($row['compania'] ?? 'N/A') . "</td>
                                        <td>" . htmlspecialchars($row['mensaje']) . "</td>
                                        <td>{$archivos}</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No hay mensajes en la bandeja de entrada.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:1rem; text-align:right;">
                <button onclick="window.print()" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Exportar PDF</button>
            </div>
        </div>
    </div>
    <?php
    }
    ?>

    <script src="../js/contacto.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>