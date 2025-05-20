function IniciarSesion() {
    var usuarioLogin = document.getElementsByName('usuariologin')[0].value;
    var contrasenaLogin = document.getElementsByName('passwordlogin')[0].value;

    console.log("Intentando iniciar sesión - usuario:", usuarioLogin);

    if (usuarioLogin == "" || usuarioLogin == null || usuarioLogin == undefined ||
        contrasenaLogin == "" || contrasenaLogin == null || contrasenaLogin == undefined) {
        Swal.fire({
            title: '¡Notificación!',
            position: 'center',
            icon: 'info',
            text: 'Por favor ingrese un usuario y una contraseña',
            showConfirmButton: false,
            timer: 1500
        });
    } else {
        Swal.fire({
            title: 'Cargando',
        });
        Swal.showLoading();
        setTimeout(() => {
            $.ajax({
                method: 'POST',
                dataType: 'json',
                data: {
                    'accion': 'LoginUsuario',
                    'usuario': usuarioLogin,
                    'password': contrasenaLogin
                },
                url: '/cursoudemy/models/login.php',
            }).then(function (respuesta) {
                console.log("Respuesta del servidor:", respuesta);
                if (respuesta.codigo == 0) {
                    Swal.fire({
                        title: '¡Notificación!',
                        position: 'center',
                        icon: 'success',
                        text: 'Bienvenido ' + respuesta.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    localStorage.setItem('usuario', respuesta.usuario);
                    localStorage.setItem('servicio', respuesta.servicio);
                    localStorage.setItem('modulo', respuesta.modulo);
                    location.href = 'http://localhost/cursoudemy/views/gestionarturno/';
                } else if (respuesta.codigo == 1) {
                    Swal.fire({
                        title: '¡Notificación!',
                        position: 'center',
                        icon: 'error',
                        text: respuesta.mensaje,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        title: '¡Notificación!',
                        position: 'center',
                        icon: 'error',
                        text: respuesta.mensaje,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }).catch(function (error) {
                console.error("Error al iniciar sesión:", error);
                Swal.fire({
                    title: '¡Notificación!',
                    position: 'center',
                    icon: 'error',
                    text: 'Error al iniciar sesión: ' + error.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        }, 1000);
    }
}