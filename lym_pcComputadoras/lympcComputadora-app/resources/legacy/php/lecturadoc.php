<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['rol']) || !in_array(strtolower($_SESSION['rol']), ['asistente', 'admin'], true)) {
    header('Location: /php/login.php');
    exit;
}

$metadataFile = storage_path('app/public/uploads/pasante/metadata.json');
$uploads = [];
if (file_exists($metadataFile)) {
    $uploads = json_decode(file_get_contents($metadataFile), true) ?: [];
}

$tipo = $_GET['tipo'] ?? 'todos';
if (in_array($tipo, ['project', 'report'], true)) {
    $uploads = array_values(array_filter($uploads, fn($item) => ($item['type'] ?? '') === $tipo));
}

usort($uploads, fn($a, $b) => strcmp($b['uploaded_at'] ?? '', $a['uploaded_at'] ?? ''));

function tipoDocumento($type) {
    return $type === 'report' ? 'Reporte' : 'Proyecto';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/img/logo.webp">
    <title>Documentos de Pasante - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/dashboard_pasante.css">
    <link rel="stylesheet" href="/css/asistente_tools.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="/php/dashboard_asistente.php" style="max-width: 45%; text-align: center;">L&M PC Computadoras</a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item"><a class="nav-link" href="/php/dashboard_asistente.php">Panel de Asistente</a></li>
                        <li class="nav-item"><a class="nav-link" href="/php/notificaciones.php">Crear Notificaciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="/php/chat.php">Chat con Clientes</a></li>
                    </ul>
                    <div class="d-flex align-items-center mt-3">
                        <span class="text-white me-3">Buen dia, <b><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Asistente'); ?></b></span>
                        <a href="/php/logout.php" class="btn btn-danger btn-sm">Cerrar Sesion</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container pasante-hero main-content">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1>Documentos de Pasante</h1>
                <p>Revisa proyectos, reportes y documentacion registrada por los pasantes durante su estadia.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="summary-badge"><i class="fas fa-folder-open"></i> Archivos: <?php echo count($uploads); ?></span>
                    <span class="summary-badge"><i class="fas fa-user-tie"></i> Asistente</span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <img src="/img/docPasante.webp" alt="Documentos de Pasante" class="hero-image">
            </div>
        </div>

        <section class="tool-card mt-4">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div>
                    <h2 class="tool-title h4">Filtro de documentos</h2>
                    <p class="tool-muted mb-0">Selecciona el tipo de documento que deseas revisar.</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn tool-action" href="/php/lecturadoc.php">Todos</a>
                    <a class="btn tool-secondary" href="/php/lecturadoc.php?tipo=project">Proyectos</a>
                    <a class="btn tool-secondary" href="/php/lecturadoc.php?tipo=report">Reportes</a>
                </div>
            </div>
        </section>

        <section class="table-responsive tool-table">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Titulo</th>
                        <th>Pasante</th>
                        <th>Tipo</th>
                        <th>Repositorio</th>
                        <th>Fecha</th>
                        <th>Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($uploads)): ?>
                    <tr><td colspan="6" class="text-center py-4">No hay documentos cargados todavia.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($uploads as $item): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['title'] ?? 'Sin titulo'); ?></strong>
                            <div class="tool-muted"><?php echo htmlspecialchars($item['description'] ?? 'Sin descripcion'); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($item['user_name'] ?? 'Pasante'); ?></td>
                        <td><span class="status-pill"><?php echo tipoDocumento($item['type'] ?? 'project'); ?></span></td>
                        <td>
                            <?php if (!empty($item['repo_url'])): ?>
                            <a href="<?php echo htmlspecialchars($item['repo_url']); ?>" target="_blank" rel="noopener">Ver repo</a>
                            <?php else: ?>
                            <span class="tool-muted">No registrado</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['uploaded_at'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($item['path'])): ?>
                            <a class="btn btn-sm tool-action" href="<?php echo htmlspecialchars($item['path']); ?>" target="_blank">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="footer mt-5">
        <div class="footer-content container">
            <div>
                <h3>L&M PC Computadoras</h3>
                <p class="footer-note">Panel del asistente para revisar la documentacion de pasantes.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>

