<?php
// Ajustar la ruta para incluir config/conexion.php desde models/
if (!file_exists('../config/conexion.php')) {
    error_log('Archivo config/conexion.php no encontrado.');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error del servidor: Archivo de configuración no encontrado.']);
    exit;
}

require_once '../config/conexion.php';

// Asegurarse de que no haya salida antes del JSON
ob_start();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'createTurno':
        $documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
        $servicio = isset($_POST['servicio']) ? trim($_POST['servicio']) : '';

        if (empty($documento) || empty($servicio)) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        try {
            $conn = getConnection();

            // Buscar o crear cliente
            $stmt = $conn->prepare("SELECT id, pnombre, papellido FROM db_clientes WHERE documento = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('s', $documento);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $cliente = $result->fetch_assoc();
                $cliente_id = $cliente['id'];
                $pnombre = $cliente['pnombre'];
                $papellido = $cliente['papellido'];
            } else {
                $stmt = $conn->prepare("INSERT INTO db_clientes (documento, numero, pnombre, papellido, sexo, fecha_registro) VALUES (?, ?, ?, ?, ?, CURDATE())");
                if (!$stmt) {
                    throw new Exception('Error al preparar la consulta: ' . $conn->error);
                }
                $numero = $documento;
                $pnombre = 'Desconocido';
                $papellido = 'Desconocido';
                $sexo = 'M';
                $stmt->bind_param('sssss', $documento, $numero, $pnombre, $papellido, $sexo);
                $stmt->execute();
                $cliente_id = $stmt->insert_id;
            }

            // Obtener ID del servicio
            $stmt = $conn->prepare("SELECT id FROM db_servicios WHERE nombre_servicio = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('s', $servicio);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'Servicio no encontrado: ' . $servicio]);
                exit;
            }
            $servicio_row = $result->fetch_assoc();
            $servicio_id = $servicio_row['id'];

            // Generar número de turno
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM db_turnos WHERE DATE(tiempo_ingreso) = CURDATE()");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $numero_turno = sprintf("T%03d", $row['total'] + 1);

            // Seleccionar un módulo disponible (box)
            $stmt = $conn->prepare("SELECT id_modulo, nombre_modulo FROM db_modulos WHERE estado = 'A' ORDER BY RAND() LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'No hay módulos disponibles.']);
                exit;
            }
            $modulo = $result->fetch_assoc();
            $modulo_id = $modulo['id_modulo'];
            $modulo_nombre = $modulo['nombre_modulo'];

            // Crear turno
            $stmt = $conn->prepare("INSERT INTO db_turnos (cliente_id, servicio_id, tipo_servicio, turno, estado_turno, modulo, pnombre, papellido, tiempo_ingreso) VALUES (?, ?, ?, ?, 'A', ?, ?, ?, NOW())");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('iiissss', $cliente_id, $servicio_id, $servicio_id, $numero_turno, $modulo_nombre, $pnombre, $papellido);
            $stmt->execute();

            // Limpiar el buffer de salida y enviar la respuesta
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'numero_turno' => $numero_turno,
                'modulo' => $modulo_nombre
            ]);
        } catch (Exception $e) {
            error_log('Error al crear el turno: ' . $e->getMessage());
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Error al crear el turno: ' . $e->getMessage()]);
        }
        break;

    default:
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}

$conn->close();
?>