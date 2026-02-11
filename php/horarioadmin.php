<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Administrador - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/horariostyleadmin.css">
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

    <nav class="navbar navbar-dark bg-warning fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="../php/dashboardadmin.php">L&M PC Computadoras</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menú</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Cuenta y Configuración
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="../php/perfiladmin.php">Perfil</a></li>
                                <li><a class="dropdown-item" href="#">Configuración</a></li>
                                <li><a class="dropdown-item" href="#">Términos y Condiciones</a></li>
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
                        // Generamos 35 espacios para asegurar una cuadrícula rectangular (5 filas x 7 cols)
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
                                    $fechaHora = $fechaObj->format('H:i'); // hora y minutos
                                    
                                    echo "<tr data-id=\"$id\">
                                            <td>
                                                <div class='cita-item-nombre'>$nombre</div>
                                                <div class='cita-item-fecha'>
                                                   $fechaDia <span class='ms-2'> $fechaHora</span>
                                                </div>
                                            </td>
                                            <td class='text-end'>
                                                <button class='btn-icon btn-edit-custom btn-edit' data-id='$id' title='Editar'>✏️</button>
                                                <button class='btn-icon btn-delete-custom btn-delete' data-id='$id' title='Borrar'>🗑️</button>
                                            </td>
                                          </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <dialog class="modal" id="admin-appointment-modal">
        <form method="dialog" class="modal__card p-4 rounded-3 shadow">
            <header class="modal__header d-flex justify-content-between align-items-center mb-3">
                <h3 class="modal__heading m-0 fw-bold text-dark">Gestionar Cita</h3>
                <button type="button" class="btn-close modal__close"></button>
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
                        <label class="form-label small fw-bold text-muted">Fecha y Hora</label>
                        <input id="appointment-fecha" class="form-control form-control-sm" type="datetime-local">
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
                <button type="button" class="modal__button--close btn btn-light text-muted">Cancelar</button>
                <button id="btn-delete-appointment" class="btn btn-outline-danger">Eliminar</button>
                <button id="btn-save-appointment" class="btn btn-warning text-white fw-bold">Guardar Cambios</button>
            </footer>
        </form>
    </dialog>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../js/horario-calendario-admin.js"></script>
</body>

</html>