<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Sanatorio Rosendo García - Inicio de Sesión</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Public Sans', sans-serif;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }
        .logo-srg {
            font-size: 2rem;
            font-weight: bold;
            color: #003087;
            margin-bottom: 20px;
        }
        .input-container {
            margin-bottom: 20px;
        }
        .input-container input {
            font-size: 1.2rem;
            padding: 10px;
            width: 100%;
            border: 2px solid #ccc;
            border-radius: 5px;
        }
        .login-button {
            background-color: #5c6bc0;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 15px;
            font-size: 1.2rem;
            cursor: pointer;
            width: 100%;
        }
        .login-button:hover {
            background-color: #3f51b5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-srg">SRG</div>
        <h2>Sanatorio Rosendo García</h2>
        <p>Bienvenidos a SRG</p>
        <div class="input-container">
            <input type="text" id="usuario" placeholder="Usuario" />
        </div>
        <div class="input-container">
            <input type="password" id="password" placeholder="Contraseña" />
        </div>
        <button class="login-button" onclick="iniciarSesion()">Iniciar Sesión</button>
    </div>

    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script>
        function iniciarSesion() {
            const usuario = document.getElementById('usuario').value;
            const password = document.getElementById('password').value;

            if (!usuario || !password) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor ingrese usuario y contraseña.'
                });
                return;
            }

            Swal.fire({
                title: 'Cargando',
                text: 'Iniciando sesión...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('models/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `accion=LoginUsuario&usuario=${encodeURIComponent(usuario)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.codigo === 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Bienvenido, ' + data.mensaje,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'views/gestionarturno/index.php'; // Redirigir al administrador de turnos
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje
                    });
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión: ' + error.message
                });
            });
        }
    </script>
</body>
</html>