<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="assets/css/style_Login.css">
</head>

<body>
    <?php
    include_once("PHP/conexion.php");
    ?>
    <!--Login-->
    <div class="form_box" id="form_box">
        <form action="PHP/Validar_login.php" method="POST">
            <h2>Iniciar sesion</h2>
            <p>Usuario</p>
            <input type="text" placeholder="Usuario" name="user" id="user" required>
            <p>Contraseña</p>
            <input type="password" placeholder="Contraseña" name="pass" id="pass" required>

            <input type="submit" value="Log In" id="login">
            <a href="##">Forget Password</a>
            <input type="button" value="Registrarse" onclick="cambio()">
        </form>
    </div>

    <!--Register-->
    <div class="form_register" id="form_register" style="display: none;">
        <form action="PHP/Registro.php" method="POST">
            <h2>Registrarse</h2>
            <p>Usuario</p>
            <input type="text" placeholder="Usuario" name="user" id="user" required>
            <p>Contraseña</p>
            <input type="password" placeholder="Contraseña" name="pass" id="pass" required>
            <p>Nombre</p>
            <input type="text" placeholder="Nombre" name="nombre" id="nombre" required>
            <p>Primer Apellido</p>
            <input type="text" placeholder="Apellido paterno" name="primerApellido" id="primerApellido" required>
            <p>Segundo Apellido</p>
            <input type="text" placeholder="Apellido materno" name="segundoApellido" id="segundoApellido" required>
            <p>Correo</p>
            <input type="email" placeholder="Correo electronico" name="correo" id="correo" required>
            <p>Fecha de nacimiento</p>
            <input type="date" placeholder="Fecha de nacimiento" name="fechaNacimiento" id="fechaNacimiento" required>
            <input type="submit" value="Registrarse" id="register">
        </form>
    </div>
    <script>
        function cambio() {
            var inicio = document.getElementById("form_box");
            var registro = document.getElementById("form_register");
            if (inicio.style.display === "none") {
                inicio.style.display = "block";
                registro.style.display = "none";
            } else {
                inicio.style.display = "none";
                registro.style.display = "block";
            }
        }

    </script>
</body>

</html>