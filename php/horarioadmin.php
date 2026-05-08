<?php
    session_start();
    require_once '../includes/db.php';

    // Verificar rol
    $rolesConAcceso = ['admin', 'tecnico', 'encargado', 'pasante'];
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $rolesConAcceso)) {
        header('Location: ../php/login.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Administrador - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/horariostyleadmin.css">
    <link rel="stylesheet" href="../css/style.css">
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

    <div class="container-fluid main px-4" style="margin-top: 80px;">
        <div class="row g-4 h-100">
            <div class="col-lg-9 mb-4">
                <div class="calendar">
                    <div class="calendar__header">
                        <button class="calendar__button--previous-admin" aria-label="Anterior"> &lt; </button>
                        <h2 id="calendar-date-admin">Cargando...</h2>
                        <button class="calendar__button--next-admin" aria-label="Siguiente"> &gt; </button>
                    </div>

                    <div class="calendar__weekdays">
                        <div class="calendar__weekday">Lun</div>
                        <div class="calendar__weekday">Mar</div>
                        <div class="calendar__weekday">Mié</div>
                        <div class="calendar__weekday">Jue</div>
                        <div class="calendar__weekday">Vie</div>
                        <div class="calendar__weekday">Sáb</div>
                        <div class="calendar__weekday">Dom</div>
                    </div>

                    <ol class="calendar__days">
                        <?php 
                        // Generamos 35 espacios para asegurar una cuadrícula rectangular 5 filas x 7 cols
                        for ($d = 1; $d <= 35; $d++): 
                        ?>
                        <li class="calendar__day" data-day="<?php echo $d; ?>">
                            <span class="day-number text-muted fw-bold"
                                style="font-size: 0.9rem; margin-bottom:5px; display:block;">
                                <?php echo ($d <= 31) ? $d : ''; ?>
                            </span>

                        </li>
                        <?php endfor; ?>
                    </ol>
                </div>
            </div>

            <div class="col-lg-3 mb-4">
                <div class="citas-card">
                    <div class="citas-header">
                        <h5>Próximas Citas</h5>
                        <button id="btn-new-appointment" class="btn-nueva-cita">
                            + Nueva
                        </button>
                    </div>

                    <div class="citas-body">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Cliente / Fecha</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="admin-appointments-table">
                                <?php
                                // Consulta segura a la base de datos
                                $stmt = $conn->query("SELECT id_cita, nombre, apellido, fecha FROM citas ORDER BY fecha DESC LIMIT 15");
                                
                                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                    $id = htmlspecialchars($row['id_cita']);
                                    $nombre = htmlspecialchars($row['nombre'] . ' ' . $row['apellido']);
                                    
                                    // Formato de fecha limpio
                                    $fechaObj = new DateTime($row['fecha']);
                                    $fechaDia = $fechaObj->format('d M'); // dia y mes
                                    
                                    echo "<tr data-id=\"$id\">\n<td>\n<div class='cita-item-nombre'>$nombre</div>\n<div class='cita-item-fecha'>\n $fechaDia \n</div>\n</td>\n<td class='text-end'>\n<button class='btn-icon btn-edit-custom btn-edit' data-id='$id' title='Editar'>✏️</button>\n<button class='btn-icon btn-delete-custom btn-delete' data-id='$id' title='Borrar'>🗑️</button>\n</td>\n</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <dialog class="dialog-nativo" id="admin-appointment-modal">
        <div class="modal__card p-4 rounded-3 shadow bg-white" style="max-width: 500px; width: 100%; margin: auto; ">
            <header class="modal__header d-flex justify-content-between align-items-center mb-3">
                <h3 class="modal__heading m-0 fw-bold text-white">Gestionar Cita</h3>
                <button type="button" class="btn-close" id="admin-modal-close-icon"></button>
            </header>

            <div class="modal__list__container">
                <input type="hidden" id="appointment-id">

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Nombre</label>
                        <input id="appointment-nombre" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Apellido</label>
                        <input id="appointment-apellido" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-muted">Correo</label>
                    <input id="appointment-correo" class="form-control form-control-sm" type="email">
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-7">
                        <label class="form-label small fw-bold text-muted">Fecha</label>
                        <input id="appointment-fecha" class="form-control form-control-sm" type="date">
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-bold text-muted">Teléfono</label>
                        <input id="appointment-telefono" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Motivo / Descripción</label>
                    <textarea id="appointment-motivo" class="form-control form-control-sm" rows="3"></textarea>
                </div>
            </div>

            <footer class="modal__footer d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                <button type="button" id="admin-modal-cancel-btn" class="btn btn-light text-muted">Cancelar</button>
                <button id="btn-delete-appointment" class="btn btn-outline-danger">Eliminar</button>
                <button id="btn-save-appointment" class="btn btn-warning text-white fw-bold">Guardar Cambios</button>
                <button id="btn-generar-appointment" class="btn btn-primary">Generar Cita</button>
            </footer>
        </div>
    </dialog>


    <dialog class="dialog-nativo" id="modal-calendar"
        style="border: none; border-radius: 12px; padding: 0; max-width: 450px; width: 90%; background: transparent;">
        <div class="modal__card shadow"
            style="background: white; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; max-height: 80vh;">

            <header class="d-flex justify-content-between align-items-center p-3" style="background-color: #ffc107;">
                <h5 class="m-0 text-white fw-bold" id="modal-heading">Citas del día</h5>
                <button type="button"
                    style="background: transparent; border: none; color: white; font-size: 1.5rem; font-weight: bold; cursor: pointer; outline: none;"
                    onclick="document.getElementById('modal-calendar').close()">
                    &#10006;
                </button>
            </header>

            <ul class="list-unstyled m-0 p-3" id="modal-calendar-list"
                style="overflow-y: auto; overflow-x: hidden; flex-grow: 1;"></ul>

            <div class="d-flex justify-content-end align-items-center p-3 border-top gap-2"
                style="background-color: #f8f9fa;">
                <button type="button" class="btn btn-light text-muted border"
                    onclick="document.getElementById('modal-calendar').close()">Cerrar</button>
                <button type="button" class="btn btn-danger text-white fw-bold" id="btn-delete-calendar"
                    style="display: none;">Eliminar</button>
                <button type="button" class="btn btn-warning text-white fw-bold" id="btn-edit-calendar"
                    style="display: none;">Editar</button>
            </div>
        </div>
    </dialog>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../js/horario-calendario-admin.js"></script>
</body>

</html>

</html>