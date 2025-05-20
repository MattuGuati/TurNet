<?php
// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    error_log('Archivo config/conexion.php no encontrado en model_totem.php.');
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Error del servidor: Archivo de configuración no encontrado.']);
    exit;
}

require_once '../config/conexion.php';

try {
    $conn = getConnection();

    if (isset($_POST['accion']) && $_POST['accion'] == "GenerarTurno") {
        $numerodocumento = isset($_POST['numerodocumento']) ? trim($_POST['numerodocumento']) : '';
        error_log("GenerarTurno - numerodocumento: $numerodocumento");

        if (empty($numerodocumento)) {
            error_log("GenerarTurno - Número de documento vacío.");
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Número de documento vacío.']);
            exit;
        }

        // Generar el número de turno (por ejemplo, T001, T002, etc.)
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM db_turnos WHERE DATE_FORMAT(tiempo_ingreso, '%Y-%m-%d') = CURDATE()");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_turnos = $row['total'] + 1; // Incrementar el contador para el nuevo turno
        $stmt->close();

        // Formatear el número de turno (por ejemplo, T001)
        $turno = sprintf("T%03d", $total_turnos);

        // Asignar el módulo "Sin Asignar" (id_modulo 0) por defecto
        $modulo_default = 0;

        // Insertar el turno en la base de datos
        $stmt = $conn->prepare("INSERT INTO db_turnos (documento, turno, estado_turno, modulo, tiempo_ingreso) VALUES (?, ?, 'A', ?, NOW())");
        if (!$stmt) {
            $error_message = 'Error al preparar la consulta: ' . $conn->error;
            error_log($error_message);
            throw new Exception($error_message);
        }
        $stmt->bind_param("ssi", $numerodocumento, $turno, $modulo_default);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows > 0) {
            error_log("GenerarTurno - Turno generado correctamente para documento: $numerodocumento, turno: $turno");
            $respuesta = ['status' => true, 'message' => 'Turno generado correctamente.', 'turno' => $turno];
        } else {
            error_log("GenerarTurno - No se pudo generar el turno para documento: $numerodocumento");
            $respuesta = ['status' => false, 'message' => 'No se pudo generar el turno.'];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    // Si no se especifica una acción válida
    error_log("Acción no válida en model_totem.php: " . (isset($_POST['accion']) ? $_POST['accion'] : 'No definida'));
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    error_log('Error en model_totem.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

$conn->close();
?>