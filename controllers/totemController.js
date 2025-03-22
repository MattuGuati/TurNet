function addNumber(num) {
    const documento = document.getElementById('documento');
    documento.value += num;
}

function clearNumber() {
    const documento = document.getElementById('documento');
    documento.value = '';
}

function showServices() {
    const documento = document.getElementById('documento').value;
    if (documento.trim() === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor ingrese un número de documento válido.'
        });
        return;
    }
    document.querySelector('.keyboard').style.display = 'none';
    document.querySelector('.action-buttons').style.display = 'none';
    document.querySelector('.input-container').style.display = 'none';
    document.querySelector('h2').innerText = 'Seleccione un servicio';
    document.getElementById('servicesList').style.display = 'flex';
}

function selectService(service) {
    const documento = document.getElementById('documento').value;
    fetch('models/model_totem.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=createTurno&documento=${documento}&servicio=${service}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Turno creado',
                text: `Su turno es ${data.numero_turno}. Por favor diríjase al módulo ${data.modulo}.`,
                timer: 5000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Ocurrió un error al crear el turno.'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión: ' + error.message
        });
    });
}