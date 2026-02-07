<?php
session_start();
require_once '../includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario
$usuario = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Usuario';
$rol = isset($_SESSION['rol']) ? htmlspecialchars($_SESSION['rol']) : 'usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" integrity="sha512-XcIsjKMcuVe0Ucj/xgIXQnytNwBttJbNjltBV18IOnru2lDPe9KRRyvCXw6Y5H415vbBLRm8+q6fmLUU7DfO6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="../css/stylehorario.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
    <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
            <!-- CARRITO / CANASTA -->
            <div class="submenu me-3">
                <img src="../img/canasta.webp" id="img-libro" alt="Canasta">

                <div id="libro">
                    <table id="lista-libro">
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
                        <tbody></tbody>
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
                                <li>
                                    <a class="dropdown-item categoria-link" href="../secciones/constructores.php"
                                        data-categoria="estructura">
                                        Pc Dell
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item categoria-link" href="#" data-categoria="techos">
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
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="madera">ASUS</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pisos">LENOVO</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="armarios">HP
                                        OMNIBOOK </a></li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="armarios">MSI</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="armarios">DELL</a>
                                </li>

                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Duplicadora
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="#"
                                        data-categoria="electricidad">CD</a></li>
                                <li><a class="dropdown-item categoria-link" href="#"
                                        data-categoria="iluminacion">DVD</a></li>
                                <li><a class="dropdown-item categoria-link" href="#"
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

                        <!-- BAÑO -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Servicio Tecnico
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="../php/horario.php" data-categoria="bano">Horarios</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/contacto.php" data-categoria="bano">Contacto</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="../php/gestion_citas.php"
                                        data-categoria="bano">Citas</a></li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="bano">Ubicacion</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Impresoras con Tinta Continua
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">EPSON</a>
                                </li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">CANON</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Tintas
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">Tinta de
                                        100 ML</a></li>
                                <li><a class="dropdown-item categoria-link" href="#" data-categoria="pintura">Tinta de
                                        1000 ML</a></li>
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

    <div class="wrapper">

        <main class="container main">
            <section class="calendar">
                <header class="calendar__header">
                    <div class="header__container">
                        <button class="calendar__button calendar__button--previous" aria-label="Ir al Anterior Mes"><i class="ri-arrow-left-s-line"></i></button>
                        <h3 class="container__heading" id="calendar-date"></h3>
                        <button class="calendar__button calendar__button--next" aria-label="Ir al Siguiente Mes"><i class="ri-arrow-right-s-line"></i></button>
                    </div>
                </header>
                
                <!-- Días de la semana -->
                <section class="calendar__weekdays">
                    <div class="calendar__weekday"><h4>Lunes</h4><abbr>Lun</abbr></div>
                    <div class="calendar__weekday"><h4>Martes</h4><abbr>Mar</abbr></div>
                    <div class="calendar__weekday"><h4>Miércoles</h4><abbr>Mié</abbr></div>
                    <div class="calendar__weekday"><h4>Jueves</h4><abbr>Jue</abbr></div>
                    <div class="calendar__weekday"><h4>Viernes</h4><abbr>Vie</abbr></div>
                    <div class="calendar__weekday"><h4>Sábado</h4><abbr>Sáb</abbr></div>
                    <div class="calendar__weekday"><h4>Domingo</h4><abbr>Dom</abbr></div>
                </section>
            
                <!-- Días del mes -->
                <ol class="calendar__days">
                    <li class="calendar__day" data-day="1"><div class="day__info"><h5>1</h5></div></li>
                    <li class="calendar__day" data-day="2"><div class="day__info"><h5>2</h5></div></li>
                    <li class="calendar__day" data-day="3"><div class="day__info"><h5>3</h5></div></li>
                    <li class="calendar__day" data-day="4"><div class="day__info"><h5>4</h5></div></li>
                    <li class="calendar__day" data-day="5"><div class="day__info"><h5>5</h5></div></li>
                    <li class="calendar__day" data-day="6"><div class="day__info"><h5>6</h5></div></li>
                    <li class="calendar__day" data-day="7"><div class="day__info"><h5>7</h5></div></li>
                    <li class="calendar__day" data-day="8"><div class="day__info"><h5>8</h5></div></li>
                    <li class="calendar__day" data-day="9"><div class="day__info"><h5>9</h5></div></li>
                    <li class="calendar__day" data-day="10"><div class="day__info"><h5>10</h5></div></li>
                    <li class="calendar__day" data-day="11"><div class="day__info"><h5>11</h5></div></li>
                    <li class="calendar__day" data-day="12"><div class="day__info"><h5>12</h5></div></li>
                    <li class="calendar__day" data-day="13"><div class="day__info"><h5>13</h5></div></li>
                    <li class="calendar__day" data-day="14"><div class="day__info"><h5>14</h5></div></li>
                    <li class="calendar__day" data-day="15"><div class="day__info"><h5>15</h5></div></li>
                    <li class="calendar__day" data-day="16"><div class="day__info"><h5>16</h5></div></li>
                    <li class="calendar__day" data-day="17"><div class="day__info"><h5>17</h5></div></li>
                    <li class="calendar__day" data-day="18"><div class="day__info"><h5>18</h5></div></li>
                    <li class="calendar__day" data-day="19"><div class="day__info"><h5>19</h5></div></li>
                    <li class="calendar__day" data-day="20"><div class="day__info"><h5>20</h5></div></li>
                    <li class="calendar__day" data-day="21"><div class="day__info"><h5>21</h5></div></li>
                    <li class="calendar__day" data-day="22"><div class="day__info"><h5>22</h5></div></li>
                    <li class="calendar__day" data-day="23"><div class="day__info"><h5>23</h5></div></li>
                    <li class="calendar__day" data-day="24"><div class="day__info"><h5>24</h5></div></li>
                    <li class="calendar__day" data-day="25"><div class="day__info"><h5>25</h5></div></li>
                    <li class="calendar__day" data-day="26"><div class="day__info"><h5>26</h5></div></li>
                    <li class="calendar__day" data-day="27"><div class="day__info"><h5>27</h5></div></li>
                    <li class="calendar__day" data-day="28"><div class="day__info"><h5>28</h5></div></li>
                    <li class="calendar__day" data-day="29"><div class="day__info"><h5>29</h5></div></li>
                    <li class="calendar__day" data-day="30"><div class="day__info"><h5>30</h5></div></li>
                    <li class="calendar__day" data-day="31"><div class="day__info"><h5>31</h5></div></li>
                </ol>
            </section>
        </main>
    </div>

    <dialog class="modal" id="appointments-modal" role="dialog" aria-labelledby="Modal vista de Citas" aria-describedby="Citas para un día en específico">
        <div class="modal__container">
            <section class="modal__card">
            <header class="modal__header">
                <h3 class="modal__heading"></h3>
                <button type="button" class="modal__close" aria-label="Cerrar Modal"><i class="ri-close-line"></i></button>
            </header>
            <div class="modal__list__container">
                <ul class="modal__list"></ul>
            </div>
            <footer class="modal__footer">
                <button type="button" class="modal__button modal__button--close" aria-label="Cancelar y Cerrar Modal">Cancelar</button>
            </footer>
        </section>
        </div>
    </dialog>
    
    <!-- Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    <!-- Calendar Module -->
    <script src="../js/horario-calendario.js" type="module"></script>
</body>
</html>