<?php
session_start();
require_once '../includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../php/login.php");
    exit();
}
// Verificar si es administrador
if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'admin') {
    header("Location: ../php/contacto.php");
    exit();
} 

if(isset($_POST['guardar_soporte'])){
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $compania = trim($_POST['compania']);
    $mensaje = trim($_POST['mensaje']);
    $cedula = trim($_POST['cedula']);

    if(!empty($nombre) && !empty($correo) && !empty($compania)){
        $ruta_ruc = null;
    $ruta_cedula = null;
    $directorio_subida = "../docs/";

    // Subir RUC
    if (isset($_FILES['archivo_ruc']) && $_FILES['archivo_ruc']['error'] === UPLOAD_ERR_OK){
        $destino_ruc = $directorio_subida . time() . "_ruc_" . basename($_FILES['archivo_ruc']['name']);
        if (move_uploaded_file($_FILES['archivo_ruc']['tmp_name'], $destino_ruc)) $ruta_ruc = $destino_ruc;
    }

    // Subir Cédula
    if (isset($_FILES['archivo_cedula']) && $_FILES['archivo_cedula']['error'] === UPLOAD_ERR_OK){
        $destino_cedula = $directorio_subida . time() . "_cedula_prov_" . basename($_FILES['archivo_cedula']['name']);
        if (move_uploaded_file($_FILES['archivo_cedula']['tmp_name'], $destino_cedula)) $ruta_cedula = $destino_cedula;
    }  
        $stmt = $conn->prepare("INSERT INTO soporte (Nombre, Correo, cedula, archivo_ruc, archivo_cedula, Compania, Mensaje) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $correo, $cedula, $ruta_ruc, $ruta_cedula, $compania, $mensaje]);
        header("Location: ../php/soporteadmin.php"); // Recargar para limpiar el POST
    exit();
    }    
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <title>Gestion de Soporte - Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/stylecitas.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
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
                                <li><a class="dropdown-item categoria-link" href="../php/pc.php"
                                        data-categoria="estructura">Pc Dell</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/hpdell.php"
                                        data-categoria="techos">Hp Dell</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Laptops
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/asus.php"
                                        data-categoria="madera">ASUS</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/lenovo.php"
                                        data-categoria="pisos">LENOVO</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/omnibook.php"
                                        data-categoria="armarios">HP OMNIBOOK </a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/msi.php"
                                        data-categoria="armarios">MSI</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/dell.php"
                                        data-categoria="armarios">DELL</a></li>
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
                                        data-categoria="bano">Horarios</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/contacto.php"
                                        data-categoria="bano">Contacto</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/gestion_citas.php"
                                        data-categoria="bano">Citas</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/ubicacion.php"
                                        data-categoria="bano">Ubicacion</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Impresoras con Tinta Continua
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/epson.php"
                                        data-categoria="pintura">EPSON</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/canon.php"
                                        data-categoria="pintura">CANON</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Tintas
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/tinta100.php"
                                        data-categoria="pintura">Tinta de 100 ML</a></li>
                                <li><a class="dropdown-item categoria-link" href="../php/tinta1000.php"
                                        data-categoria="pintura">Tinta de 1000 ML</a></li>
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


    <div class="main-content">
        <h1>
            Directorio de Soporte
        </h1>
        <div class="text-center">
            <p>
                Hola <strong><?php echo $_SESSION['usuario']; ?></strong>, aqui puedes ver el proveedor de soporte de
                cada producto
            </p>
        </div>
        <div class="form-section">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Empresa / Proveedor</label>
                    <input type="text" name="compania" required>
                </div>
                <div class="form-group">
                    <label>Nombre del Contacto</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group mb-3">
                    <label>Numero de Cedula</label>
                    <input type="text" class="form-control" name="cedula" id="cedula" maxlength="10">
                </div>

                <div class="form-group mb-3">
                    <label>Correo del Proveedor</label>
                    <input type="email" class="form-control" name="correo" required>
                </div>

                <div id="div_documentos" class="border p-3 mb-3 bg-white">
                    <div class="mb-3">
                        <label for="archivo_ruc" class="form-label">Subir RUC</label>
                        <input class="form-control" type="file" name="archivo_ruc" id="archivo_ruc"
                            accept=".pdf, .jpg, .png, .webp" required>
                    </div>

                    <div class="mb-3">
                        <label for="archivo_cedula" class="form-label">Subir Copia de Cédula Representate</label>
                        <input class="form-control" type="file" name="archivo_cedula" id="archivo_cedula"
                            accept=".pdf, .jpg, .png, .webp" required>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <p>Notas / Detalles de Convenio</p>
                    <textarea class="form-control" name="mensaje" rows="3"></textarea>
                </div>

                <div class="form-group full mt-3">
                    <button type="submit" name="guardar_soporte" class="btn btn-primary">
                        <i class="fas fa-save"></i>Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>

        <?php
        // Solo mostrar tabla si el usuario es admin, técnico, encargado o pasante
        $rolesConAcceso = ['admin', 'tecnico', 'encargado', 'pasante'];
        $puedeVerTabla = isset($_SESSION['rol']) && in_array($_SESSION['rol'], $rolesConAcceso);
        
        if ($puedeVerTabla) {
        ?>
        <h1>Marcas Registradas en el Sistema</h1>
        <div class="card compact-card" style="overflow-x: auto;">
            <div class="table-wrapper">
                <table class="compact-table table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Marca / Empresa</th>
                            <th>Contacto</th>
                            <th>Identificacion</th>
                            <th>Correo</th>
                            <th>Notas</th>
                            <th>Archivos (Docs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = $conn->query("SELECT * FROM soporte ORDER BY id_soporte DESC");
                        if($query -> rowCount() > 0){
                        foreach($query as $row){
                            
                            // Lógica para mostrar botones de descarga si el archivo existe
                            $btnRuc = !empty($row['archivo_ruc']) ? "<a href='{$row['archivo_ruc']}' target='_blank' class='btn btn-sm btn-info mb-1 d-block' style='font-size:0.75rem;'>RUC</a>" : "";
                            $btnCedula = !empty($row['archivo_cedula']) ? "<a href='{$row['archivo_cedula']}' target='_blank' class='btn btn-sm btn-secondary d-block' style='font-size:0.75rem;'>Cédula</a>" : "";
                            $archivos = ($btnRuc == "" && $btnCedula == "") ? "<span class='text-muted' style='font-size:0.8rem;'>Ninguno</span>" : $btnRuc . $btnCedula;

                            echo "<tr>
                                        <td>#{$row['id_soporte']}</td>
                                        <td><span class='badge bg-warning text-dark'>" . htmlspecialchars($row['Compania']) . "</span></td>
                                        <td>" . htmlspecialchars($row['Nombre']) . "</td>
                                        <td>" . htmlspecialchars($row['cedula'] ?? 'N/A') . "</td>
                                        <td>" . htmlspecialchars($row['Correo']) . "</td>
                                        <td>" . htmlspecialchars($row['Mensaje']) . "</td>
                                        <td>{$archivos}</td>
                                    </tr>";
                            }
                        }else {
                            echo "<tr><td colspan='7' class='text-center'>No hay proveedores registrados</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:0.75rem; text-align:right;">
                <button onclick="window.print()" class="btn btn-primary">Exportar PDF</button>
            </div>
        </div>
        <?php
        }
        ?>
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
    <script src="../js/soporte.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>