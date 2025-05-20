<?php
// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    $error_message = 'Archivo config/conexion.php no encontrado en model_servicios.php. Ruta absoluta buscada: ' . realpath('../config/conexion.php');
    error_log($error_message);
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => $error_message]);
    exit;
}

require_once '../config/conexion.php';

try {
    $conn = getConnection();

    if ($_POST['accion'] == "ListarServicios") {
        $query = $conn->query("SELECT s.id, s.nombre_servicio, s.color_servicio, s.icono_servicio, s.letra_servicio, s.estado FROM db_servicios s");
        $datos = [];
        while ($row = $query->fetch_assoc()) {
            $datos[] = $row;
        }
        $arrServicio = $datos;
        if (!empty($arrServicio)) {
            for ($i = 0; $i < count($arrServicio); $i++) {
                $activo = "";
                $inactivo = "";
                $editar = "";

                if ($arrServicio[$i]["estado"] == "A") {
                    $arrServicio[$i]["estado"] = '<span class="badge bg-success">Activo</span>';
                    $inac = 2;
                    $activo = '<button class="btn btn-icon btn-sm btn-danger" onClick="inactivarservicio(' . $arrServicio[$i]['id'] . ',' . "$inac" . ')" title="Deshabilitar Servicio"><i class="bx bxs-message-square-x"></i></button>';
                } else {
                    $arrServicio[$i]["estado"] = '<span class="badge bg-danger">Inactivo</span>';
                    $act = 1;
                    $inactivo = '<button class="btn btn-icon btn-sm btn-primary" onClick="inactivarservicio(' . $arrServicio[$i]['id'] . ','. "$act" .')" title="Habilitar Servicio"><i class="bx bxs-message-square-x"></i></button>';
                }

                $editar = '<button class="btn btn-icon btn-sm btn-success" onClick="seleccionarServicio(' . $arrServicio[$i]['id'] . ')" title="Editar Servicio"><i class="bx bxs-edit"></i></button>';
               
                $arrServicio[$i]["color_servicio"] = '<span class="badge bg-'.$arrServicio[$i]["color_servicio"].'">'.$arrServicio[$i]["color_servicio"].'</span>';
                $arrServicio[$i]["icono_servicio"] = '<i class="bx '.$arrServicio[$i]["icono_servicio"].'"></i>';

                $arrServicio[$i]["opciones"] = '<div class="text-center">' . $editar . ' ' . $activo . ' ' . $inactivo . '</div>';
            }
            $arrResponse["data"] = $arrServicio;
        } else {
            $arrResponse = [];
        }
        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    if ($_POST['accion'] == 'ObtenerServicio') {
        $id_servicio = $_POST['datos'];
        $stmt = $conn->prepare("SELECT s.id, s.nombre_servicio, s.color_servicio, s.icono_servicio, s.letra_servicio FROM db_servicios s WHERE s.id = ?");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_servicio);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && $row['id'] > 0) {
            $respuesta = [
                'codigo' => 0,
                'idunico' => $row['id'],
                'nombre_servicio' => $row['nombre_servicio'],
                'color_servicio' => $row['color_servicio'],
                'icono_servicio' => $row['icono_servicio'],
                'letra_servicio' => $row['letra_servicio'],
            ];
        } else {
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'No se Obtuvieron Datos',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'RegistroServicio') {
        $datos = json_decode($_POST['datos']);
        $nombreservicio = strtoupper($datos->nombreservicio);
        $colorservicio = strtolower($datos->colorservicio);
        $iconoservicio = strtolower($datos->iconoservicio);
        $letraservicio = strtoupper($datos->letraservicio);
        if (empty($colorservicio) || empty($nombreservicio) || empty($iconoservicio) || empty($letraservicio)) {
            $respuesta = [
                "codigo" => 2,
                "respuesta" => 'Verificar los Campos Vacios',
            ];
        } else {
            $stmt = $conn->prepare("INSERT INTO db_servicios (nombre_servicio, color_servicio, icono_servicio, letra_servicio, estado, fecha_registro) VALUES (?, ?, ?, ?, 'A', NOW())");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("ssss", $nombreservicio, $colorservicio, $iconoservicio, $letraservicio);
            $stmt->execute();
            $id_registro = $stmt->insert_id;
            $stmt->close();

            if ($id_registro > 0) {
                $respuesta = [
                    'codigo' => 0,
                    'respuesta' => 'Registro Insertado Correctamente'
                ];
            } else {
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'No se Logró Registrar el Servicio',
                ];
            }
        }
        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'ActualizarServicio') {
        $datos = json_decode($_POST['datos']);
        $nombreservicio = strtoupper($datos->nombreservicio);
        $colorservicio = strtolower($datos->colorservicio);
        $iconoservicio = strtolower($datos->iconoservicio);
        $letraservicio = strtoupper($datos->letraservicio);
        $idunicodelservicio = $datos->idunicodelservicio;
        if (empty($idunicodelservicio) || empty($nombreservicio) || empty($colorservicio) || empty($iconoservicio) || empty($letraservicio)) {
            $respuesta = [
                "codigo" => 2,
                "respuesta" => 'Verificar los Campos Vacios',
            ];
        } else {
            $stmt = $conn->prepare("UPDATE db_servicios SET nombre_servicio = ?, color_servicio = ?, icono_servicio = ?, letra_servicio = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("ssssi", $nombreservicio, $colorservicio, $iconoservicio, $letraservicio, $idunicodelservicio);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($affected_rows > 0) {
                $respuesta = [
                    'codigo' => 0,
                    'respuesta' => 'Servicio Modificado Correctamente'
                ];
            } else {
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'No se Logró Modificar el Servicio',
                ];
            }
        }
        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'ActualizarEstado') {
        $idservicio = $_POST['datos'];
        $estado = $_POST['estado'];
        $stmt = $conn->prepare("UPDATE db_servicios SET estado = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param("si", $estado, $idservicio);
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows > 0) {
            $respuesta = [
                'codigo' => 0,
                'respuesta' => 'Estado Actualizado Correctamente'
            ];
        } else {
            $respuesta = [
                'codigo' => 1,
                'respuesta' => 'No se Logró Actualizar el Estado'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    // Si no se especifica una acción válida
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    error_log('Error en model_servicios.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

$conn->close();
?>