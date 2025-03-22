// Inicializar la interfaz
$('#vista_numero').hide();
$('#llamarturno').show();
$('#atenderturno').hide();
$('#finalizarturno').hide();
DatosUsuario();

// Cargar datos del usuario y estadísticas
function DatosUsuario() {
    const usuario = localStorage.getItem('usuario');
    console.log('Usuario:', usuario);
    $.ajax({
        method: 'POST',
        dataType: 'json',
        data: {
            'accion': 'Obtenerdatosusuario',
            'datos': usuario
        },
        url: '/cursoudemy/models/model_gestionar_turno.php'
    })
    .then(function (response) {
        if (response.codigo === 0) {
            $("#nombreservicio").html(response.nombre_servicio);
            $("#nombremodulo").html(response.nombre_modulo);
            $("#total_turnos").html(response.total_turnos);
            $("#en_espera").html(response.en_espera);
            $("#turnos_atendidos").html(response.atendidos);
        } else {
            Swal.fire({
                title: 'Notificación!',
                position: 'center',
                icon: 'error',
                text: 'No hay datos para mostrar',
                showConfirmButton: false,
                timer: 2000
            });
        }
    })
    .catch(function (error) {
        Swal.fire({
            title: 'Error',
            position: 'center',
            icon: 'error',
            text: 'Error al cargar los datos del usuario: ' + error.message,
            showConfirmButton: false,
            timer: 2000
        });
    });
}

// Llamar un turno
function llamar_Turno() {
    Swal.fire({
        text: "Deseas Llamar El Turno",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        customClass: {
            confirmButton: 'btn btn-primary me-1',
            cancelButton: 'btn btn-danger'
        },
        confirmButtonText: 'Confirmar'
    }).then((result) => {
        if (result.isConfirmed) {
            const usuario = localStorage.getItem('usuario');
            const servicio = localStorage.getItem('servicio');
            const modulo = localStorage.getItem('modulo');
            $.ajax({
                method: "POST",
                dataType: "json",
                data: {
                    "accion": "Llamarturno",
                    "usuario": usuario,
                    "servicio": servicio,
                    "modulo": modulo
                },
                url: "/cursoudemy/models/model_gestionar_turno.php"
            }).then(function (response) {
                if (response.codigo === 0) {
                    $('#vista_numero').show();
                    $('#llamarturno').hide();
                    $('#atenderturno').show();
                    $('#finalizarturno').hide();
                    $("#numerodelturno").html(response.turno);
                    $("#documento").attr('disabled', 'disabled').val(response.documento);
                    $("#numero").attr('disabled', 'disabled').val(response.numero);
                    $("#pnombre").attr('disabled', 'disabled').val(response.pnombre);
                    $("#papellido").attr('disabled', 'disabled').val(response.papellido);
                    $("#sapellido").attr('disabled', 'disabled').val(response.sapellido);
                } else {
                    Swal.fire({
                        title: 'Notificación!',
                        position: 'center',
                        icon: 'info',
                        text: 'No hay Turnos Pendientes',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }).catch(function (error) {
                Swal.fire({
                    title: 'Error',
                    position: 'center',
                    icon: 'error',
                    text: 'Error al llamar el turno: ' + error.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    });
}

// Atender un turno
function atender_Turno() {
    Swal.fire({
        text: "Deseas Atender El Turno",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        customClass: {
            confirmButton: 'btn btn-primary me-1',
            cancelButton: 'btn btn-danger'
        },
        confirmButtonText: 'Confirmar'
    }).then((result) => {
        if (result.isConfirmed) {
            const usuario = localStorage.getItem('usuario');
            const servicio = localStorage.getItem('servicio');
            const modulo = localStorage.getItem('modulo');
            $.ajax({
                method: "POST",
                dataType: "json",
                data: {
                    "accion": "AtenderTurno",
                    "usuario": usuario,
                    "servicio": servicio,
                    "modulo": modulo
                },
                url: "/cursoudemy/models/model_gestionar_turno.php"
            }).then(function (response) {
                if (response.codigo === 0) {
                    $('#vista_numero').show();
                    $('#llamarturno').hide();
                    $('#atenderturno').hide();
                    $('#finalizarturno').show();
                } else {
                    Swal.fire({
                        title: 'Notificación!',
                        position: 'center',
                        icon: 'info',
                        text: 'No se logró atender el turno',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }).catch(function (error) {
                Swal.fire({
                    title: 'Error',
                    position: 'center',
                    icon: 'error',
                    text: 'Error al atender el turno: ' + error.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    });
}

// Finalizar un turno
function finalizar_Turno() {
    DatosUsuario();
    Swal.fire({
        text: "Deseas Finalizar El Turno",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        customClass: {
            confirmButton: 'btn btn-primary me-1',
            cancelButton: 'btn btn-danger'
        },
        confirmButtonText: 'Confirmar'
    }).then((result) => {
        if (result.isConfirmed) {
            const usuario = localStorage.getItem('usuario');
            const servicio = localStorage.getItem('servicio');
            const modulo = localStorage.getItem('modulo');
            $.ajax({
                method: "POST",
                dataType: "json",
                data: {
                    "accion": "Finalizarturno",
                    "usuario": usuario,
                    "servicio": servicio,
                    "modulo": modulo
                },
                url: "/cursoudemy/models/model_gestionar_turno.php"
            }).then(function (response) {
                if (response.codigo === 0) {
                    $('#vista_numero').hide();
                    $('#llamarturno').show();
                    $('#atenderturno').hide();
                    $('#finalizarturno').hide();
                    Swal.fire({
                        title: 'Éxito',
                        position: 'center',
                        icon: 'success',
                        text: 'Turno Finalizado Correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        title: 'Notificación!',
                        position: 'center',
                        icon: 'info',
                        text: 'No hay Turnos Pendientes',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }).catch(function (error) {
                Swal.fire({
                    title: 'Error',
                    position: 'center',
                    icon: 'error',
                    text: 'Error al finalizar el turno: ' + error.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    });
}

// Mostrar el modal de turnos
function modalverturnos() {
    $('#TablaTurnos').DataTable().destroy();
    var table = $("#TablaTurnos").DataTable({
        "aProcessing": true,
        "aServerSide": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla =(",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Sin Datos Por Favor Agregar",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad"
            }
        },
        ajax: {
            'type': 'POST',
            "data": {
                'accion': 'ListarTurnos'
            },
            "url": "/cursoudemy/models/model_gestionar_turno.php"
        },
        columns: [
            { data: "estado_turno" },
            { data: "turno" },
            { data: "nombre_servicio" },
            { data: "numero" },
            { data: "nombre" },
            { data: "tiempo_ingreso" },
            { data: "tiempo_salida" }
        ]
    });
    $('#modalturnos').modal('show');
}