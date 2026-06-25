<?php
/*Logica para destruir la sesion del usuario
Limpiar los datos de navegacion*/
session_start();
session_unset();
session_destroy();

/*redirige al login*/
header("Location: /php/login.php");
exit();

?>

