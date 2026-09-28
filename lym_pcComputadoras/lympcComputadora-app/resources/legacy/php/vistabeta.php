<?php
session_start();

/*
// Redirigir a cada usuario autenticado a su dashboard correspondiente.
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /php/dashboardadmin.php');
    exit;
}

$rol = strtolower((string) ($_SESSION['rol'] ?? ''));

if ($rol === 'tecnico') {
    header('Location: /php/dashboard_tecnico.php');
    exit;
}

if ($rol === 'pasante') {
    header('Location: /php/dashboard_pasante.php');
    exit;
}

if ($rol === 'asistente') {
    header('Location: /php/dashboard_asistente.php');
    exit;
}

if (isset($_SESSION['usuario'])) {
    header('Location: /php/dashboard.php');
    exit;
}
*/
include_once '../includes/db.php';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/img/logo.webp">
    <title>L&M PC Computadoras - Mejores Productos Tecnologicos para Nuestros Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/vistabeta.css">
</head>

<body class="storefront-page">
    <nav class="navbar navbar-dark fixed-top beta-nav">
        <div class="container-fluid position-relative">

            <a class="navbar-brand m-0 text-truncate" href="#inicio">L&amp;M PC Computadoras</a>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <form class="d-none d-lg-flex m-0" role="search" onsubmit="event.preventDefault();">
                    <input class="form-control me-2" type="search" placeholder="Buscar..." aria-label="Search"
                        id="search-input" />
                    <button class="btn btn-success" type="button">Buscar</button>
                </form>
            </div>           
        </div>
    </nav>
    <main id="product-container" class="grid-container">
        <header class="header beta-hero" id="inicio">
            <div class="header-content container beta-layout">
                <div class="header-txt beta-hero-copy">
                    <h1>Encuentra tu próximo equipo.</h1>
                    <p>Componentes, accesorios y equipos para estudiar, trabajar o jugar. Revisa lo que tenemos y ven a conocer tus opciones en persona.</p>
                    <div class="hero-actions">
                        <a href="#lista-1" class="btn-2" style="text-decoration:none; font-weight: 700;">Ver el catálogo <i class="fas fa-arrow-down" aria-hidden="true"></i></a>
                        <a href="https://maps.app.goo.gl/Hr7jt9W4ejWCdhmN7" class="hero-secondary" target="_blank" rel="noopener noreferrer"><i class="fas fa-location-dot" aria-hidden="true"></i> Visitar tienda física</a>
                    </div>
                    <div class="hero-note"><i class="fas fa-comments" aria-hidden="true"></i> ¿No sabes cuál elegir? Te orientamos en el local.</div>
                </div>
                <div class="beta-poster">
                    <div class="beta-poster-label"><span>Folleto de tienda</span><span>Conoce L&amp;M</span></div>
                    <img src="/img/promocion2.png" alt="Afiche de L&amp;M PC: computadoras, impresoras, tintas y servicio técnico">
                </div>
            </div>

        </header>
        <section class="info beta-visit" aria-labelledby="visit-title">
            <div class="info-content container beta-visit-content">
                <div class="info-img">
                    <img src="/img/tienda.png" alt>
                </div>
                <div class="info-txt">
                    <h2 id="visit-title">Ven a conocer la tienda.</h2>
                    <p>
                        Ven a conocer los productos, pregunta por disponibilidad y recibe atención directa para encontrar lo que necesitas.
                    </p>
                    <div class="info-actions">
                        <a href="https://maps.app.goo.gl/Hr7jt9W4ejWCdhmN7" class="btn-2" target="_blank" rel="noopener noreferrer" style="font-weight: 700; text-decoration:none;"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Cómo llegar</a>
                        <a href="https://www.facebook.com/LyM010?locale=es_LA" class="btn-3" target="_blank" rel="noopener noreferrer" style="text-decoration:none;"><i class="fab fa-facebook-f" aria-hidden="true"></i> Consultar</a>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="products container beta-products" aria-labelledby="products-title">
            <span class="section-kicker">02 / SELECCION DE PRODUCTOS</span>
            <h2 id="products-title">Empieza por lo que te interesa.</h2>
            <p class="products-intro">Explora los productos destacados, compara opciones y consulta en la tienda por el que mejor se ajuste a tu proyecto.</p>
            <div id="no-results" style="display: none; text-align: center; margin-bottom: 20px; width: 100%;">
                <p>No se Encontro el producto</p>
            </div>

            <div class="box-container" id="lista-1">
                <?php
            $stmt = $conn->prepare("SELECT p.*, s.Nombre AS nombre_contacto, s.Compania 
                                    FROM productos p 
                                    LEFT JOIN soporte s ON p.id_soporte = s.id_soporte 
                                    ORDER BY p.id_producto ASC");
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Por cada producto en tu base de datos, creamos un lugar para guardarlo
            foreach ($productos as $row):
                
            ?>
                <a href="/php/login.php" class="box" data-id="<?= $row['id_producto'] ?>" aria-label="Consultar <?= htmlspecialchars($row['nombre']) ?>" style="text-decoration: none;">
                    <img src="/img/<?= htmlspecialchars($row['imagen']) ?>" alt="<?= htmlspecialchars($row['nombre']) ?>"
                        style="width: 100%; max-height: 200px; object-fit: contain; padding-top: 10px;">
                        
                    <div class="product-txt d-flex flex-column h-100 w-100">
                        <h3 class="product-name" style="font-size: 18px; font-weight: 600;"><?= htmlspecialchars($row['nombre']) ?></h3>
                        
                        <p style="margin-bottom: 5px;"><span class="product-units"
                                data-id="<?= $row['id_producto'] ?>"><?= $row['unidades'] ?></span>
                            Unidades</p>
                        <p class="text-muted" style="font-size: 14px; margin-bottom: 5px;"><?= htmlspecialchars($row['serie']) ?></p>

                        <?php if (!empty($row['nombre_contacto'])): ?>
                            <p style="font-size: 13px; color: #198754; margin-bottom: 10px;">
                                <i class="fas fa-headset"></i> Soporte: <b><?= htmlspecialchars($row['nombre_contacto']) ?></b> (<?= htmlspecialchars($row['Compania']) ?>)
                            </p>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="btn-2" id="load-more" style="text-decoration:none; font-weight: 700; cursor: pointer;">Ver más productos <i class="fas fa-arrow-down" aria-hidden="true"></i></div>
        </section>

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

        <?php include '../includes/chat_widget.php'; ?>

        <script src="/js/dashboard.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
        </script>
</body>

</html>

