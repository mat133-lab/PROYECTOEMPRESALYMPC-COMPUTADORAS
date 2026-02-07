<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Administrador - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/stylehorario.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
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
<nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">

            <a class="navbar-brand" href="../php/dashboardadmin.php">L&M PC Computadoras</a>

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
        </div>
    </nav>

<div class="container main">
    <div class="row mt-5">
        <div class="col-lg-8">
            <section class="calendar">
                <header class="calendar__header">
                    <div class="header__container">
                        <button class="calendar__button calendar__button--previous-admin" aria-label="Anterior"><i class="ri-arrow-left-s-line"></i></button>
                        <h3 class="container__heading" id="calendar-date-admin"></h3>
                        <button class="calendar__button calendar__button--next-admin" aria-label="Siguiente"><i class="ri-arrow-right-s-line"></i></button>
                    </div>
                </header>

                <section class="calendar__weekdays">
                    <div class="calendar__weekday"><h4>Lunes</h4><abbr>Lun</abbr></div>
                    <div class="calendar__weekday"><h4>Martes</h4><abbr>Mar</abbr></div>
                    <div class="calendar__weekday"><h4>Miércoles</h4><abbr>Mie</abbr></div>
                    <div class="calendar__weekday"><h4>Jueves</h4><abbr>Jue</abbr></div>
                    <div class="calendar__weekday"><h4>Viernes</h4><abbr>Vie</abbr></div>
                    <div class="calendar__weekday"><h4>Sábado</h4><abbr>Sab</abbr></div>
                    <div class="calendar__weekday"><h4>Domingo</h4><abbr>Dom</abbr></div>
                </section>

                <ol class="calendar__days">
                    <?php for ($d = 1; $d <= 31; $d++): ?>
                        <li class="calendar__day" data-day="<?php echo $d; ?>">
                            <div class="day__info"><h5><?php echo $d; ?></h5></div>
                        </li>
                    <?php endfor; ?>
                </ol>
            </section>
        </div>

        <div class="col-lg-4">
            <div class="card compact-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Citas Registradas</h5>
                    <button id="btn-new-appointment" class="btn btn-sm btn-success">Nueva</button>
                </div>
                <div class="table-wrapper">
                    <table class="compact-table table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="admin-appointments-table">
                        <?php
                        $stmt = $conn->query("SELECT id_cita, nombre, apellido, correo, fecha, telefono, motivo FROM citas ORDER BY fecha DESC");
                        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                            $id = htmlspecialchars($row['id_cita']);
                            $nombre = htmlspecialchars($row['nombre'] . ' ' . $row['apellido']);
                            $fecha = htmlspecialchars($row['fecha']);
                            echo "<tr data-id=\"$id\"><td>#{$id}</td><td>{$nombre}</td><td>{$fecha}</td><td><button class=\"btn btn-sm btn-primary btn-edit\">Editar</button> <button class=\"btn btn-sm btn-danger btn-delete\">Borrar</button></td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal editar/crear -->
<dialog class="modal" id="admin-appointment-modal">
    <form method="dialog" class="modal__card p-3">
        <header class="modal__header">
            <h3 class="modal__heading">Editar Cita</h3>
            <button type="button" class="modal__close">✕</button>
        </header>
        <div class="modal__list__container p-2">
            <input type="hidden" id="appointment-id">
            <div class="mb-2"><label>Nombre</label><input id="appointment-nombre" class="form-control"></div>
            <div class="mb-2"><label>Apellido</label><input id="appointment-apellido" class="form-control"></div>
            <div class="mb-2"><label>Correo</label><input id="appointment-correo" class="form-control" type="email"></div>
            <div class="mb-2"><label>Fecha</label><input id="appointment-fecha" class="form-control" type="datetime-local"></div>
            <div class="mb-2"><label>Telefono</label><input id="appointment-telefono" class="form-control"></div>
            <div class="mb-2"><label>Motivo</label><textarea id="appointment-motivo" class="form-control" rows="3"></textarea></div>
        </div>
        <footer class="modal__footer d-flex gap-2">
            <button id="btn-save-appointment" class="btn btn-primary">Guardar</button>
            <button id="btn-delete-appointment" class="btn btn-danger">Eliminar</button>
            <button type="button" class="modal__button--close btn btn-secondary">Cancelar</button>
        </footer>
    </form>
</dialog>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="../js/horario-calendario-admin.js"></script>
</body>
</html>