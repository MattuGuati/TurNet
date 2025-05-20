<!DOCTYPE html>
<html lang="es" class="light-style" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>SRG - Sanatorio Rosendo García</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/sweetalert2.min.css" />
    <style>
        body {
            background-color: #87CEEB;
        }
        .contenedor-login {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .tarjeta-login {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .tarjeta-login h1 {
            text-align: center;
            margin-bottom: 1rem;
        }
        .tarjeta-login .form-control {
            margin-bottom: 1rem;
        }
        .tarjeta-login .btn {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="contenedor-login">
        <div class="tarjeta-login">
            <h1>SRG</h1>
            <h3 class="text-center">Sanatorio Rosendo García</h3>
            <h5 class="text-center">Bienvenidos a SRG</h5>
            <input type="text" name="usuariologin" class="form-control" placeholder="Usuario" value="box01" />
            <input type="password" name="passwordlogin" class="form-control" placeholder="Contraseña" value="12345678" />
            <button type="button" class="btn btn-primary" id="boton-iniciar-sesion">Iniciar Sesión</button>
        </div>
    </div>

    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="controllers/loginController.js"></script>
    <script>
        // Depuración: Verificar que jQuery y la función IniciarSesion estén disponibles
        document.addEventListener('DOMContentLoaded', function() {
            console.log("jQuery cargado:", typeof jQuery !== 'undefined');
            console.log("Función IniciarSesion definida:", typeof IniciarSesion !== 'undefined');

            // Depuración: Verificar que el evento del botón se registre
            const botonIniciarSesion = document.getElementById('boton-iniciar-sesion');
            if (botonIniciarSesion) {
                console.log("Botón 'Iniciar Sesión' encontrado");
                botonIniciarSesion.addEventListener('click', function() {
                    console.log("Botón 'Iniciar Sesión' clicado");
                    IniciarSesion();
                });
            } else {
                console.error("Botón 'Iniciar Sesión' no encontrado");
            }
        });
    </script>
</body>
</html>