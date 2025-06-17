<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/style_perfil.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/Style.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="https://necolas.github.io/normalize.css/8.0.1/normalize.css">
</head>

<body>
    <?php
    // Inicia la sesión
    include 'conexion.php'; //conexion a BD    
    session_start();
    require_once "menu.php"; //navbar
    if (isset($_SESSION['id_usuario'])) {
        $id_usuario = $_SESSION['id_usuario'];
        $consultaProductos = "SELECT * FROM usuarios where id_usuario= $id_usuario";
        // Ejecutar la consulta para obtener los productos        
        $resultado = $conn->prepare($consultaProductos);
        $resultado->execute();
        $usuario = $resultado->fetch();
    } else {
        // Si no está configurada, redirige al login
        header('Location: ../index.php');
        exit(); // Asegúra de que el script se detenga después de redirigir
    }
    ?>
    <section class="seccion-perfil-usuario">
        <div class="perfil-usuario-header">
            <div class="perfil-usuario-portada">
                <div class="perfil-usuario-avatar">
                    <img src="../assets/imgs/user.png" alt="img-avatar">
                    <!-- para cambiar la imagen de perfil
                    <button type="button" class="boton-avatar">
                        <i class="far fa-image"></i>
                    </button>
                    -->
                </div>
                <!-- para cambiar la imagen de portada o fondo
                <button type="button" class="boton-portada">
                    <i class="far fa-image"></i> Cambiar fondo
                </button>
                -->
            </div>
        </div>
        <div class="perfil-usuario-body">

            <?php
            //validar que opcion se eligio
            if (isset($_GET['editar'])) {
                echo "
        <form action='' method='POST'>
            <div class='perfil-usuario-footer'>
                <ul class='lista-datos'>
                    <li><i class='icono fas fa-user'>Nombre: 
                        <input type='text' name='nombre' value='" . $usuario['nombre'] . "' minlength='3' required></i></li>
                    <li><i class='icono fas fa-user'>Apellido paterno: 
                        <input type='text' name='primerap' value='" . $usuario['primerap'] . "' minlength='3' required></i></li>
                    <li><i class='icono fas fa-user'>Apellido materno:
                        <input type='text' name='segundoap' value='" . $usuario['segundoap'] . "' minlength='3' required></i></li>
                    <li><i class='icono fas fa-user'>Usuario: 
                        <input type='text' name='usuario' value='" . $usuario['usuario'] . "' minlength='3' required></i></li>
                    <li><i class='icono fas fa-calendar-alt'>Fecha de nacimiento: 
                        <input type='date' name='fecha' value='" . $usuario['fechanac'] . "' minlength='3' required></i></li>
                    <li><i class='icono fas fa-mobile'>Correo electronico: 
                        <input type='email' name='correo' value='" . $usuario['e_mail'] . "' minlength='3' required></i></li>
                    <li><i class='icono fas fa-user-secret'>Rol: " . $usuario['rol'] . "</i></li>
                        
                        <button type='submit' name='guardar' class='btn'>Guardar</button>
                </ul>
                
            </div>
        </form>
        ";
                // Verificar si el formulario fue enviado
                if (isset($_POST['guardar'])) {
                    // Obtener los datos del formulario
                    $nombre = $_POST['nombre'];
                    $primerap = $_POST['primerap'];
                    $segundoap = $_POST['segundoap'];
                    $fecha = $_POST['fecha'];
                    $correo = $_POST['correo'];

                    // Preparar la consulta SQL para la actualización
                    $sql = "UPDATE usuarios SET nombre = :nombre, primerap = :primerap, segundoap = :segundoap, 
                fechanac = :fecha, e_mail = :correo WHERE id_usuario = :id_usuario";

                    // Preparar la sentencia
                    $stmt = $conn->prepare($sql);

                    // Vincular los parámetros de forma segura
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':primerap', $primerap);
                    $stmt->bindParam(':segundoap', $segundoap);
                    $stmt->bindParam(':fecha', $fecha);
                    $stmt->bindParam(':correo', $correo);
                    $stmt->bindParam(':id_usuario', $id_usuario);

                    // Ejecutar la sentencia
                    $stmt->execute();

                    // Verificar si la actualización fue exitosa
                    if ($stmt->rowCount() > 0) {
                        echo "<script>alert('Los datos se actualizaron correctamente.');</script>";                           
                    } else {
                        echo "<script>alert('No se encontraron cambios o el usuario no existe.');</script>";
                    }                    
                }
            } else {
                ?>
                <div class="perfil-usuario-bio">
                    <h3 class="titulo">
                        <?php echo $usuario['nombre'] . ' ' . $usuario['primerap'] . ' ' . $usuario['segundoap']; ?>
                    </h3>
                </div>
                <div class="perfil-usuario-footer">
                    <ul class="lista-datos">
                        <li><i class="icono fas fa-user"></i>Usuario: <?php echo $usuario['usuario']; ?></li>
                        <li><i class="icono fas fa-calendar-alt"></i>Fecha de nacimiento:
                            <?php echo $usuario['fechanac']; ?>
                        </li>
                        <li><i class="icono fas fa-mobile"></i>Correo electronico: <?php echo $usuario['e_mail']; ?></li>
                        <li><i class="icono fas fa-user-secret"></i>Rol: <?php echo $usuario['rol']; ?></li>
                    </ul>
                </div>

            </div>
        </section>
        <!--====  End   ====-->
        <?php
            }//llave condicion opcion
            ?>

    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>

    <!-- bootstrap affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

</body>

</html>