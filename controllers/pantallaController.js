// Conjunto para rastrear los turnos que ya han reproducido el sonido
let turnosConSonido = new Set();

$(document).ready(function() {
    setInterval(function() {
        Fechayhoraactual();
    }, 1000);
    setInterval(function() {
        TurosGestion();
    }, 2000); // Actualiza cada 2 segundos para detectar turnos más rápido
});

function Fechayhoraactual() {
    let fecha, horas, minutos, segundos, diaSemana, dia, mes, anio;
    fecha = new Date();
    horas = fecha.getHours();
    minutos = fecha.getMinutes();
    segundos = fecha.getSeconds();
    diaSemana = fecha.getDay();
    dia = fecha.getDate();
    mes = fecha.getMonth();
    anio = fecha.getFullYear();
    let semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    let diasemana = semana[diaSemana];
    let meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    let mesnombre = meses[mes];
    $("#anioymes").html(diasemana + ' ' + dia + ' ' + 'de' + ' ' + mesnombre + ' ' + 'del' + ' ' + anio);
    let ampm;
    if (horas >= 12) {
        horas = horas - 12;
        ampm = "PM";
    } else {
        ampm = "AM";
    }
    if (horas == 0) { horas = 12; }
    if (minutos < 10) { minutos = "0" + minutos; }
    if (segundos < 10) { segundos = "0" + segundos; }
    $("#tiempohora").html(horas + ' : ' + minutos + ' : ' + segundos + ' ' + ampm);
}

function TurosGestion() {
    $.ajax({
        method: "POST",
        dataType: "json",
        data: {
            "accion": "Verturnos",
        },
        url: "/cursoudemy/models/model_pantalla.php",
    }).then(function (response) {
        console.log("Respuesta de model_pantalla.php:", response); // Depuración
        const tabla = document.querySelector("#tablaTurnos tbody");
        if (response.status == true) {
            // Limpiar la tabla
            while (tabla.firstChild) {
                tabla.removeChild(tabla.firstChild);
            }

            // Agregar los turnos a la tabla
            for (var i = 0; i < response.data.length; i++) {
                const row = document.createElement("tr");
                const turnoKey = `${response.data[i].turno}-${response.data[i].modulo}`; // Clave única para el turno

                if (response.data[i].estado == "LLAMADO") {
                    row.className = "movimiento"; // Aplicar la clase movimiento para "LLAMADO"
                    console.log("Aplicando clase 'movimiento' al turno:", response.data[i]); // Depuración
                    // Reproducir el sonido si el turno no ha sido notificado antes
                    if (!turnosConSonido.has(turnoKey)) {
                        const audio = new Audio('assets/sonidos/timbre.mp3');
                        audio.play().catch(error => {
                            console.error("Error al reproducir el sonido:", error);
                        });
                        turnosConSonido.add(turnoKey); // Marcar el turno como notificado
                    }
                } else {
                    row.className = "bg-primary"; // Aplicar la clase bg-primary para otros estados
                    turnosConSonido.delete(turnoKey); // Permitir que el sonido se reproduzca nuevamente si el turno vuelve a "LLAMADO"
                }
                row.innerHTML = `
                    <td class="text-center"><h1 class="fw-bold text-underline tamanoletra text-white">${response.data[i].turno}</h1></td>
                    <td class="text-center"><h1 class="fw-bold text-underline tamanoletra text-white">${response.data[i].estado}</h1></td>
                    <td class="text-center"><h1 class="fw-bold text-underline tamanoletra text-white">${response.data[i].modulo}</h1></td>
                `;
                tabla.appendChild(row);
            }
        } else {
            console.log("No se encontraron turnos:", response);
        }
    }).catch(function (error) {
        console.error("Error al obtener turnos:", error);
    });
}