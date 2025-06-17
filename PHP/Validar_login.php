<?php
session_start(); // Inicia la sesión para almacenar el dato

include 'conexion.php';

    $pass = $_REQUEST['pass'];
    $user = $_REQUEST['user'];

    try {
        // Ejecutar la consulta
        $consulta = "SELECT * FROM usuarios WHERE usuario='$user' and contraseña='$pass'";
        $resultado = $conn->prepare($consulta);
        $resultado->execute();
        
        
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
    }

    if ( $validar_login = $resultado->fetch()) {        
        // Almacena el dato en la sesión
        $_SESSION['nombre'] = $validar_login['nombre'].' '.$validar_login['primerap'];
        $_SESSION['rol'] = $validar_login['rol'];
        $_SESSION['id_usuario'] = $validar_login['id_usuario'];          
        header("location: Principal.php");
        exit();
    } else {
        echo "
                <script>
                    alert('Usuario o contraseña incorrecta');
                    window.location = '../index.php';
                </script>
            ";
        exit();
    }

?>