<?php
include 'conexion.php';
//asignacion de valores a variables
$correo = $_POST['correo'];
$user = $_POST['user'];
$pass = $_POST['pass'];
$fechaNacimiento = $_POST['fechaNacimiento'];
$nombre = $_POST['nombre'];
$primerApellido = $_POST['primerApellido'];
$segundoApellido = $_POST['segundoApellido'];

//consulta para insertar nuevo usuario a base de datos
$query = "INSERT INTO usuarios(usuario, e_mail, contraseña, fechanac, rol, nombre, primerap, segundoap) 
              VALUES('$user','$correo','$pass','$fechaNacimiento','comun','$nombre','$primerApellido','$segundoApellido'); ";

//Verificacion del correo
$consulta_correo = "SELECT * FROM usuarios WHERE e_mail='$correo' ";
$resultado = $conn->prepare($consulta_correo);
$resultado->execute();
$verificacion_correo = $resultado->fetchAll();

if (count($verificacion_correo) > 0) {
    echo "
            <script>
                alert('Correo ya registrado, intenta con un correo distinto');
                
            </script>
        ";
    exit();
}

//Verificacion de nombre    
$consulta_nombre = "SELECT * FROM usuarios WHERE nombre='$nombre' 
        and primerap='$primerApellido' and segundoap='$segundoApellido' ";

$resultado = $conn->prepare($consulta_nombre);
$resultado->execute();
$verificacion_nombre = $resultado->fetchAll();

if (count($verificacion_nombre) > 0) {
    echo "
            <script>
                alert('Nombre ya registrado, intenta con un nombre diferente');
                window.location='../index.php';
            </script>
        ";
    exit();
}

//Verificacion de usuario    
$consulta_usuario = "SELECT * FROM usuarios WHERE usuario='$user'";

$resultado = $conn->prepare($consulta_usuario);
$resultado->execute();
$verificacion_usuario = $resultado->fetch();

if (count($verificacion_usuario) > 0) {
    echo "
    <script>
        alert('Usuario ya registrado, intenta con un usuario diferente');
        window.location = '../index.php'; 
    </script>
";
    exit();
}

//ejecutar consulta de insercion 
$resultado = $conn->prepare($query);
$resultado->execute();
$usuario= $resultado->fetch();
//comprobar si se realizo con exito
if ($resultado) {
    // Inicia la sesión para almacenar el dato
    session_start();  
    // Almacena el dato en la sesión
    $_SESSION['nombre'] = $usuario['nombre'].' '.$usuario['primerap'];  
    $_SESSION['rol'] = $validar_login['rol'];
    $_SESSION['id_usuario'] = $validar_login['id_usuario'];  
    echo '
            <script>
                alert("Registro exitoso");
                window.location = "Principal.php";
            </script>
            ';
} else {
    echo '
            <script>
                alert("Inténtalo de nuevo, usuario no registrado");
                window.location = "../index.php";                
            </script>
            ';
}

$conn = null;

?>