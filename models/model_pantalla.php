<?php
// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    error_log('Archivo config/conexion.php no encontrado en model_pantalla.php.');
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Error del servidor: Archivo de configuración no encontrado.']);
    exit;
}

require_once '../config/conexion.php';

try {
    $conn = getConnection();

    if (isset($_POST['accion']) && $_POST['accion'] == "Verturnos") {
        $query = "SELECT t.turno, t.modulo, 
                 CASE 
                     WHEN t.estado_turno = 'A' THEN 'EN ESPERA'
                     WHEN t.estado_turno = 'M' THEN 'LLAMADO'
                     WHEN t.estado_turno = 'S' THEN 'MODULO'
                     WHEN t.estado_turno = 'F' THEN 'ATENDIDO'
                     ELSE 'No hay Turnos' 
                 END AS estado,
                 c.pnombre, c.papellido
                 FROM db_turnos t 
                 LEFT JOIN db_clientes c ON t.documento = c.numero
                 WHERE t.estado_turno IN ('A', 'M', 'S', 'F') 
                 AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE() 
                 ORDER BY t.tiempo_ingreso DESC LIMIT 4";
        $result = $conn->query($query);

        $datos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['nombre'] = ($row['pnombre'] && $row['papellido']) ? $row['pnombre'] . ' ' . $row['papellido'] : 'Desconocido';
                unset($row['pnombre']);
                unset($row['papellido']);
                $datos[] = $row;
            }
            $result->free();
        }

        $arrTurno = $datos;
        if (!empty($arrTurno)) {
            for ($i = 0; $i < count($arrTurno); $i++) {
                // Aquí puedes agregar lógica adicional si es necesario
            }
            $arrResponse = ['status' => true, 'data' => $arrTurno];
        } else {
            $arrResponse = ['status' => false, 'data' => []];
        }

        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] == "ver_turno") {
        $tipo_llamado = 'PASE';
        $stmt = $conn->prepare("SELECT t.estado_turno, c.pnombre, c.papellido, t.turno, t.modulo 
                               FROM db_turnos t
                               LEFT JOIN db_clientes c ON t.documento = c.numero
                               WHERE t.estado_turno = ? 
                               AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE()");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('s', $tipo_llamado);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $respuesta = [
                'pnombre' => $row['pnombre'] ?: 'Desconocido',
                'papellido' => $row['papellido'] ?: '',
                'turno' => $row['turno'],
                'modulo' => $row['modulo'],
                'su_turno_es' => $row['estado_turno']
            ];
        } else {
            $respuesta = ['respuesta' => 'error'];
        }

        $stmt->close();
        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    // Si no se especifica una acción válida
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    error_log('Error en model_pantalla.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

$conn->close();
?>