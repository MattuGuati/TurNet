<?php
session_start();

// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    $error_message = 'Archivo config/conexion.php no encontrado en model_gestionar_turno.php. Ruta absoluta buscada: ' . realpath('../config/conexion.php');
    error_log($error_message);
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 3, 'mensaje' => $error_message]);
    exit;
}

require_once '../config/conexion.php';

try {
    $conn = getConnection();

    if ($_POST['accion'] == 'Obtenerdatosusuario') {
        $usuario = $_POST['datos'];

        // Obtener estadísticas de turnos
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_turnos, 
                               COALESCE(SUM(t.estado_turno = 'A'), 0) AS en_espera, 
                               COALESCE(SUM(t.estado_turno = 'F'), 0) AS atendidos
                               FROM db_turnos t
                               WHERE DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE()");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_turnos = $row['total_turnos'];
        $en_espera = $row['en_espera'];
        $atendidos = $row['atendidos'];
        $stmt->close();

        // Obtener datos del usuario
        $stmt = $conn->prepare("SELECT u.id_usuario, s.nombre_servicio, m.nombre_modulo
                               FROM db_usuarios u
                               INNER JOIN db_modulos m ON u.modulo = m.id_modulo
                               INNER JOIN db_nivel_acceso n ON u.nivel = n.id_nivel
                               INNER JOIN db_servicios s ON u.servicio = s.id 
                               WHERE u.usuario = ?");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && $row['id_usuario'] > 0) {
            $respuesta = [
                'codigo' => 0,
                'nombre_servicio' => $row['nombre_servicio'],
                'nombre_modulo' => $row['nombre_modulo'],
                'total_turnos' => $total_turnos,
                'en_espera' => $en_espera,
                'atendidos' => $atendidos
            ];
        } else {
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'No se Obtuvieron Datos'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'Llamarturno') {
        $usuario = $_POST["usuario"];
        $modulo = $_POST["modulo"];
        $servicio = $_POST["servicio"];

        // Obtener el turno más antiguo en espera para el servicio
        $stmt = $conn->prepare("SELECT t.id, t.turno, c.documento, c.numero, c.pnombre, c.papellido, c.sapellido
                               FROM db_turnos t
                               INNER JOIN db_clientes c ON t.documento = c.numero
                               WHERE t.tipo_servicio = ? 
                               AND t.estado_turno = 'A' 
                               AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE() 
                               ORDER BY t.tiempo_ingreso ASC 
                               LIMIT 1");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('s', $servicio);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            $id_turno = $row['id'];
            $turno = $row['turno'];
            $documento = $row['documento'];
            $numero = $row['numero'];
            $pnombre = $row['pnombre'];
            $papellido = $row['papellido'];
            $sapellido = $row['sapellido'];

            // Actualizar el turno a "M" (Llamado)
            $stmt = $conn->prepare("UPDATE db_turnos 
                                   SET estado_turno = 'M', usuario_atendio = ?, modulo = ?, tiempo_atender = NOW() 
                                   WHERE turno = ? AND id = ?");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("ssss", $usuario, $modulo, $turno, $id_turno);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($affected_rows > 0) {
                $respuesta = [
                    'codigo' => 0,
                    'id_turno' => $id_turno,
                    'turno' => $turno,
                    'documento' => $documento,
                    'numero' => $numero,
                    'pnombre' => $pnombre,
                    'papellido' => $papellido,
                    'sapellido' => $sapellido
                ];
            } else {
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'error'
                ];
            }
        } else {
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'No hay turnos en espera para este servicio.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'AtenderTurno') {
        $usuario = $_POST["usuario"];
        $modulo = $_POST["modulo"];
        $servicio = $_POST["servicio"];

        // Obtener el turno en estado "M" (Llamado)
        $stmt = $conn->prepare("SELECT t.id, t.turno 
                               FROM db_turnos t
                               WHERE t.tipo_servicio = ? 
                               AND t.estado_turno = 'M' 
                               AND t.modulo = ? 
                               AND t.usuario_atendio = ? 
                               AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE() 
                               ORDER BY t.tiempo_ingreso ASC 
                               LIMIT 1");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('sss', $servicio, $modulo, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            $id_turno = $row['id'];
            $turno = $row['turno'];

            // Actualizar el turno a "S" (Atendiendo)
            $stmt = $conn->prepare("UPDATE db_turnos 
                                   SET estado_turno = 'S', usuario_atendio = ?, modulo = ?, tiempo_atender = NOW() 
                                   WHERE turno = ? AND id = ?");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("ssss", $usuario, $modulo, $turno, $id_turno);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($affected_rows > 0) {
                $respuesta = [
                    'codigo' => 0,
                    'respuesta' => "Exito"
                ];
            } else {
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'error'
                ];
            }
        } else {
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'No hay turnos para atender.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'Finalizarturno') {
        $usuario = $_POST["usuario"];
        $modulo = $_POST["modulo"];
        $servicio = $_POST["servicio"];

        // Finalizar el turno (cambiar estado a "F")
        $stmt = $conn->prepare("UPDATE db_turnos 
                               SET estado_turno = 'F', tiempo_salida = NOW() 
                               WHERE usuario_atendio = ? 
                               AND modulo = ? 
                               AND tipo_servicio = ? 
                               AND estado_turno = 'S'");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param("sss", $usuario, $modulo, $servicio);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows > 0) {
            $respuesta = [
                'codigo' => 0,
                'respuesta' => "Exito"
            ];
        } else {
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'error'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == "ListarTurnos") {
        $query = $conn->query("SELECT t.estado_turno, t.turno, s.nombre_servicio, CONCAT(c.documento, '-', c.numero) AS numero,
                              CONCAT(c.pnombre, ' ', c.papellido, ' ', c.sapellido) AS nombre, t.tiempo_ingreso, t.tiempo_salida
                              FROM db_turnos t
                              INNER JOIN db_clientes c ON t.documento = c.numero
                              INNER JOIN db_servicios s ON t.tipo_servicio = s.id
                              WHERE DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE()");
        $datos = [];
        while ($row = $query->fetch_assoc()) {
            $datos[] = $row;
        }
        $arrTurnos = $datos;
        if (!empty($arrTurnos)) {
            for ($i = 0; $i < count($arrTurnos); $i++) {
                if ($arrTurnos[$i]["estado_turno"] == "A") {
                    $arrTurnos[$i]["estado_turno"] = '<span class="badge bg-success">Activo</span>';
                } else if ($arrTurnos[$i]["estado_turno"] == "M") {
                    $arrTurnos[$i]["estado_turno"] = '<span class="badge bg-primary">Llamado</span>';
                } else {
                    $arrTurnos[$i]["estado_turno"] = '<span class="badge bg-danger">Finalizado</span>';
                }
            }
            $arrResponse["data"] = $arrTurnos;
        } else {
            $arrResponse = [];
        }

        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    // Si no se especifica una acción válida
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 4, 'mensaje' => 'Acción no válida.']);
} catch (Exception $e) {
    error_log('Error en model_gestionar_turno.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 3, 'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
}

$conn->close();
?>