function agregarNumero(numero) {
    const input = document.getElementById('numerodocumento');
    if (input.value.length < 8) { // Limita a 8 dígitos
        input.value += numero;
    }
}

function borrarNumero() {
    const input = document.getElementById('numerodocumento');
    input.value = input.value.slice(0, -1);
}

function generarTurno() {
    const numerodocumento = document.getElementById('numerodocumento').value;
    if (numerodocumento === "") {
        Swal.fire({
            title: '¡Notificación!',
            position: 'center',
            icon: 'info',
            text: 'Por favor ingrese un número de documento',
            showConfirmButton: false,
            timer: 1500,
            backdrop: true,
            allowOutsideClick: false
        });
    } else if (numerodocumento.length !== 8) {
        Swal.fire({
            title: '¡Notificación!',
            position: 'center',
            icon: 'warning',
            text: 'El número de documento debe tener 8 dígitos',
            showConfirmButton: false,
            timer: 1500,
            backdrop: true,
            allowOutsideClick: false
        });
    } else {
        Swal.fire({
            title: 'Cargando',
            position: 'center',
            backdrop: true,
            allowOutsideClick: false
        });
        Swal.showLoading();
        setTimeout(() => {
            $.ajax({
                method: "POST",
                dataType: "json",
                data: {
                    "accion": "GenerarTurno",
                    "numerodocumento": numerodocumento
                },
                url: "/cursoudemy/models/model_totem.php",
            }).then(function (response) {
                if (response.status == true) {
                    Swal.fire({
                        title: '¡Turno Generado!',
                        position: 'center',
                        icon: 'success',
                        text: 'Aguarde y será llamado por DNI desde la TV',
                        showConfirmButton: false,
                        timer: 2000,
                        backdrop: true,
                        allowOutsideClick: false
                    }).then(() => {
                        document.getElementById('numerodocumento').value = ''; // Limpiar el input después de generar el turno
                    });
                } else {
                    Swal.fire({
                        title: '¡Notificación!',
                        position: 'center',
                        icon: 'error',
                        text: 'Error al generar el turno',
                        showConfirmButton: false,
                        timer: 1500,
                        backdrop: true,
                        allowOutsideClick: false
                    });
                }
            }).catch(function (error) {
                Swal.fire({
                    title: '¡Notificación!',
                    position: 'center',
                    icon: 'error',
                    text: 'Error al generar el turno: ' + error.message,
                    showConfirmButton: false,
                    timer: 1500,
                    backdrop: true,
                    allowOutsideClick: false
                });
            });
        }, 1000);
    }
}