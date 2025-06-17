<?php

// Inicia la sesión
session_start();

// Elimina todas las variables de sesión
session_unset();

// Destruye la sesión
session_destroy();

// Redirige al usuario al index.php
header('Location: ../index.php');
exit();
?>