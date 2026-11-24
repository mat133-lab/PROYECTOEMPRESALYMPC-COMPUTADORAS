<?php
/*Logica para destruir la sesion del usuario
Limpiar los datos de navegacion*/
session_start();
session_unset();
session_destroy();

/* Regresar a la pagina publica despues de cerrar sesion. */
header("Location: ../php/vistabeta.php");
exit();

?>

