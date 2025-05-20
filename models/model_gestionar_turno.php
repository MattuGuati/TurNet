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
        $stmt = $conn->prepare("SELECT u.id_usuario, s.nombre_servicio, m.nombre_modulo, m.id_modulo
                               FROM db_usuarios u
                               LEFT JOIN db_modulos m ON u.modulo = m.id_modulo
                               LEFT JOIN db_nivel_acceso n ON u.nivel = n.id_nivel
                               LEFT JOIN db_servicios s ON u.servicio = s.id 
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
            // Determinar si el usuario es un recepcionista (Box 1-6)
            $esRecepcionista = in_array($row['id_modulo'], [6, 7, 8, 9, 10, 11]); // Box 01-06 (ajustado según db_modulos)
            $respuesta = [
                'codigo' => 0,
                'nombre_servicio' => $row['nombre_servicio'],
                'nombre_modulo' => $row['nombre_modulo'],
                'id_modulo' => $row['id_modulo'], // Agregamos el id_modulo para usarlo en Llamarturno
                'total_turnos' => $total_turnos,
                'en_espera' => $en_espera,
                'atendidos' => $atendidos,
                'es_recepcionista' => $esRecepcionista
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
        $servicio = isset($_POST["servicio"]) ? $_POST["servicio"] : '';

        // Depuración: Registrar los parámetros recibidos
        error_log("Llamarturno - usuario: $usuario, modulo: $modulo, servicio: " . ($servicio === '' ? 'empty' : $servicio));

        // Obtener el id_modulo correspondiente al nombre del módulo
        $stmt = $conn->prepare("SELECT id_modulo FROM db_modulos WHERE nombre_modulo = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('s', $modulo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            error_log("Llamarturno - Módulo no encontrado: $modulo");
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'Módulo no encontrado: ' . $modulo
            ];
            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
        $modulo_id = $row['id_modulo'];

        // Depuración: Registrar el id_modulo
        error_log("Llamarturno - modulo_id: $modulo_id");

        // Si servicio es una cadena vacía, null, o 'null' (como string), buscar cualquier turno en espera
        if (empty($servicio) || $servicio === 'null') {
            $stmt = $conn->prepare("SELECT t.id, t.turno, t.documento
                                   FROM db_turnos t
                                   WHERE t.estado_turno = 'A' 
                                   AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE() 
                                   ORDER BY t.tiempo_ingreso ASC 
                                   LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->execute();
            error_log("Llamarturno - Buscando cualquier turno en espera (servicio vacío)");
        } else {
            // Obtener el ID del servicio basado en el nombre del servicio
            $stmt = $conn->prepare("SELECT id FROM db_servicios WHERE nombre_servicio = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('s', $servicio);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row) {
                error_log("Llamarturno - Servicio no encontrado: $servicio");
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'Servicio no encontrado: ' . $servicio
                ];
                header('Content-Type: application/json');
                echo json_encode($respuesta);
                exit;
            }
            $servicio_id = $row['id'];

            // Depuración: Registrar el ID del servicio
            error_log("Llamarturno - servicio_id: $servicio_id");

            // Obtener el turno más antiguo en espera para el servicio
            $stmt = $conn->prepare("SELECT t.id, t.turno, t.documento
                                   FROM db_turnos t
                                   WHERE t.tipo_servicio = ? 
                                   AND t.estado_turno = 'A' 
                                   AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE() 
                                   ORDER BY t.tiempo_ingreso ASC 
                                   LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('i', $servicio_id);
            $stmt->execute();
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            $id_turno = $row['id'];
            $turno = $row['turno'];
            $documento = $row['documento'];

            // Depuración: Registrar el turno encontrado
            error_log("Llamarturno - Turno encontrado: id=$id_turno, turno=$turno, documento=$documento");

            // Actualizar el turno a "M" (Llamado) y asignar el módulo
            $stmt = $conn->prepare("UPDATE db_turnos 
                                   SET estado_turno = 'M', usuario_atendio = ?, modulo = ?, tiempo_atender = NOW() 
                                   WHERE turno = ? AND id = ?");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("siss", $usuario, $modulo_id, $turno, $id_turno);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            // Depuración: Registrar si el turno se actualizó correctamente
            error_log("Llamarturno - Filas afectadas al actualizar a estado M: $affected_rows");

            if ($affected_rows > 0) {
                $respuesta = [
                    'codigo' => 0,
                    'id_turno' => $id_turno,
                    'turno' => $documento, // Devolvemos el DNI en lugar del número de turno
                    'documento' => $documento,
                    'numero' => $documento // Usamos el DNI como "numero" para compatibilidad
                ];
            } else {
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'error al actualizar el turno a estado M'
                ];
            }
        } else {
            error_log("Llamarturno - No se encontraron turnos");
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'No hay turnos pendientes.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'AtenderTurno') {
        $usuario = $_POST["usuario"];
        $modulo = $_POST["modulo"];
        $servicio = isset($_POST["servicio"]) ? $_POST["servicio"] : '';

        // Obtener el id_modulo correspondiente al nombre del módulo
        $stmt = $conn->prepare("SELECT id_modulo FROM db_modulos WHERE nombre_modulo = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('s', $modulo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            error_log("AtenderTurno - Módulo no encontrado: $modulo");
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'Módulo no encontrado: ' . $modulo
            ];
            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
        $modulo_id = $row['id_modulo'];

        // Depuración: Registrar el id_modulo
        error_log("AtenderTurno - modulo_id: $modulo_id");

        // Si servicio es una cadena vacía o null, buscar cualquier turno en estado "M" para el usuario y módulo
        if (empty($servicio) || $servicio === 'null') {
            $stmt = $conn->prepare("SELECT t.id, t.turno 
                                   FROM db_turnos t
                                   WHERE t.estado_turno = 'M' 
                                   AND t.modulo = ? 
                                   AND t.usuario_atendio = ? 
                                   AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE() 
                                   ORDER BY t.tiempo_ingreso ASC 
                                   LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('is', $modulo_id, $usuario);
            $stmt->execute();
        } else {
            // Obtener el ID del servicio basado en el nombre del servicio
            $stmt = $conn->prepare("SELECT id FROM db_servicios WHERE nombre_servicio = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('s', $servicio);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row) {
                error_log("AtenderTurno - Servicio no encontrado: $servicio");
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'Servicio no encontrado: ' . $servicio
                ];
                header('Content-Type: application/json');
                echo json_encode($respuesta);
                exit;
            }
            $servicio_id = $row['id'];

            // Obtener el turno en estado "M" (Llamado) para el usuario y módulo
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
            $stmt->bind_param('iis', $servicio_id, $modulo_id, $usuario);
            $stmt->execute();
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            $id_turno = $row['id'];
            $turno = $row['turno'];

            // Depuración: Registrar el turno encontrado para atender
            error_log("AtenderTurno - Turno encontrado: id=$id_turno, turno=$turno");

            // Actualizar el turno a "S" (Atendiendo)
            $stmt = $conn->prepare("UPDATE db_turnos 
                                   SET estado_turno = 'S', usuario_atendio = ?, modulo = ?, tiempo_atender = NOW() 
                                   WHERE turno = ? AND id = ?");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("siss", $usuario, $modulo_id, $turno, $id_turno);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            // Depuración: Registrar si el turno se actualizó correctamente
            error_log("AtenderTurno - Filas afectadas al actualizar a estado S: $affected_rows");

            if ($affected_rows > 0) {
                $respuesta = [
                    'codigo' => 0,
                    'respuesta' => "Exito"
                ];
            } else {
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'error al actualizar el turno a estado S'
                ];
            }
        } else {
            // Depuración: Registrar los turnos disponibles en estado "M"
            $stmt = $conn->prepare("SELECT t.id, t.turno, t.estado_turno, t.modulo, t.usuario_atendio, t.tiempo_ingreso 
                                   FROM db_turnos t 
                                   WHERE t.estado_turno = 'M' 
                                   AND DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE()");
            $stmt->execute();
            $result = $stmt->get_result();
            $turnos_m = [];
            while ($row = $result->fetch_assoc()) {
                $turnos_m[] = $row;
            }
            $stmt->close();
            error_log("AtenderTurno - No se encontraron turnos en estado M para usuario: $usuario, modulo_id: $modulo_id. Turnos disponibles: " . json_encode($turnos_m));

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
        $servicio = isset($_POST["servicio"]) ? $_POST["servicio"] : '';

        // Obtener el id_modulo correspondiente al nombre del módulo
        $stmt = $conn->prepare("SELECT id_modulo FROM db_modulos WHERE nombre_modulo = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('s', $modulo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            error_log("Finalizarturno - Módulo no encontrado: $modulo");
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'Módulo no encontrado: ' . $modulo
            ];
            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
        $modulo_id = $row['id_modulo'];

        // Depuración: Registrar el id_modulo
        error_log("Finalizarturno - modulo_id: $modulo_id");

        // Si servicio es una cadena vacía o null, finalizar cualquier turno en estado "S" para el usuario y módulo
        if (empty($servicio) || $servicio === 'null') {
            $stmt = $conn->prepare("UPDATE db_turnos 
                                   SET estado_turno = 'F', tiempo_salida = NOW() 
                                   WHERE usuario_atendio = ? 
                                   AND modulo = ? 
                                   AND estado_turno = 'S'");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("si", $usuario, $modulo_id);
            $stmt->execute();
        } else {
            // Obtener el ID del servicio basado en el nombre del servicio
            $stmt = $conn->prepare("SELECT id FROM db_servicios WHERE nombre_servicio = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param('s', $servicio);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row) {
                error_log("Finalizarturno - Servicio no encontrado: $servicio");
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'Servicio no encontrado: ' . $servicio
                ];
                header('Content-Type: application/json');
                echo json_encode($respuesta);
                exit;
            }
            $servicio_id = $row['id'];

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
            $stmt->bind_param("sii", $usuario, $modulo_id, $servicio_id);
            $stmt->execute();
        }

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
        $query = $conn->query("SELECT t.estado_turno, t.turno, s.nombre_servicio, t.documento AS numero,
                              t.documento AS nombre, t.tiempo_ingreso, t.tiempo_salida, m.nombre_modulo
                              FROM db_turnos t
                              LEFT JOIN db_servicios s ON t.tipo_servicio = s.id
                              LEFT JOIN db_modulos m ON t.modulo = m.id_modulo
                              WHERE DATE_FORMAT(t.tiempo_ingreso, '%Y-%m-%d') = CURDATE()");
        $datos = [];
        while ($row = $query->fetch_assoc()) {
            $datos[] = $row;
        }
        $arrTurnos = $datos;
        if (!empty($arrTurnos)) {
            for ($i = 0; $i < count($arrTurnos); $i++) {
                if ($arrTurnos[$i]["estado_turno"] == "A") {
                    $arrTurnos[$i]["estado_turno"] = '<span class="badge bg-success">ACTIVO</span>';
                } else if ($arrTurnos[$i]["estado_turno"] == "M") {
                    $arrTurnos[$i]["estado_turno"] = '<span class="badge bg-primary">LLAMADO</span>';
                } else {
                    $arrTurnos[$i]["estado_turno"] = '<span class="badge bg-danger">FINALIZADO</span>';
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