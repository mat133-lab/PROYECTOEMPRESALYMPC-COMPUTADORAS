<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'tecnico') {
    header('Location: ../php/login.php');
    exit();
}

$usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Técnico');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <title>Administrar Contactos - Técnico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard_tecnico.php" style="font-weight: 700;">L&M PC Computadoras</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Panel Técnico</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard_tecnico.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="horarioadmin.php">Horario</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_citas.php">Citas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="contacto_tecnico.php">Administrar Contactos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="perfilUsu.php">Perfil</a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="btn btn-danger btn-sm" href="../php/logout.php">Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="container" style="padding-top: 100px;">
        <h1 class="text-center mb-4">Administrar Contactos</h1>
        <p class="text-center">Hola <strong><?php echo $usuario; ?></strong>, aquí puedes ver y gestionar los mensajes de contacto de los clientes.</p>

        <div class="card shadow-sm p-3 mt-4">
            <div class="card-header">
                <h5>Buzón de Mensajes de Clientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th># ID</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Correo</th>
                                <th>Compañía</th>
                                <th>Mensaje</th>
                                <th>Archivos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $query = $conn->query("SELECT * FROM contacto ORDER BY id_mensaje DESC");
                                if ($query->rowCount() > 0) {
                                    foreach($query as $row){
                                        
                                        // Generar botones de descarga para RUC y Cédula
                                        $btnRuc = !empty($row['archivo_ruc']) ? "<a href='{$row['archivo_ruc']}' target='_blank' class='btn btn-sm btn-info mb-1' style='font-size:0.75rem;'>RUC</a>" : "";
                                        $btnCedula = !empty($row['archivo_cedula']) ? "<a href='{$row['archivo_cedula']}' target='_blank' class='btn btn-sm btn-secondary' style='font-size:0.75rem;'>Cédula</a>" : "";
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
                <div class="mt-3 text-end">
                    <button onclick="window.print()" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Exportar PDF</button>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>