<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../php/login_admin.php");
    exit();
}

$adminName = isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Administrador';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin — L&M PC Computadoras</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/estiloadmin.css">
</head>

<body>
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

    <main class="container header">
        <div class="header-content">
            <div class="header-txt">
                <h1>Bienvenido, <span><?php echo $adminName; ?></span></h1>
                <p>Panel de administración — gestion de citas, usuarios, mensajes y contenido del sitio.</p>
            </div>
        </div>

        <section class="products">
            <div class="container">
                <h2 style="text-align:center; margin-bottom:24px;">Accesos rápidos</h2>
                <div class="box-container">
                    <a href="horarioadmin.php" class="box" style="text-decoration:none;">
                        <img src="../img/calendar.webp" alt="Citas" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Horario</h3>
                            <p>Ver horarios disponibles de los tecnicos y gente especializada en el tema.</p>
                            <span class="precio">Ir a Horario</span>
                        </div>
                    </a>

                    <a href="registro_admin.php" class="box" style="text-decoration:none;">
                        <img src="../img/users.png" alt="Usuarios" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Usuarios</h3>
                            <p>Crea cuentas para los administradores</p>
                            <span class="precio">Gestionar Usuarios</span>
                        </div>
                    </a>

                    <a href="contactoadmin.php" class="box" style="text-decoration:none;">
                        <img src="../img/contact.webp" alt="Contactos" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Contacto</h3>
                            <p>Revisar mensajes enviados desde el formulario de contacto.</p>
                            <span class="precio">Ir a Contacto</span>
                        </div>
                    </a>

                    <a href="gestion_citasU.php" class="box" style="text-decoration:none;">
                        <img src="../img/book.png" alt="Citas U" onerror="this.style.display='none'">
                        <div class="product-txt">
                            <h3>Citas</h3>
                            <p>Listado y administración de citas por usuario.</p>
                            <span class="precio">Ver Citas</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
        <div class="container footer-content">
            <div>
                <h3>L&M PC Computadoras</h3>
                <p style="max-width:320px; color:#bbb;">Panel administrativo - Derechos Reservados</p>
            </div>
        </div>
    </footer>

    <script src="../js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>