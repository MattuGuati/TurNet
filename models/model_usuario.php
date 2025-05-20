<?php
// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    $error_message = 'Archivo config/conexion.php no encontrado en model_usuario.php. Ruta absoluta buscada: ' . realpath('../config/conexion.php');
    error_log($error_message);
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => $error_message]);
    exit;
}

require_once '../config/conexion.php';

try {
    $conn = getConnection();

    if ($_POST['accion'] == "ListarUsuarios") {
        $query = $conn->query("SELECT u.id_usuario, u.usuario, u.cedula, u.nombres, u.apellidos, u.servicio, s.nombre_servicio, u.modulo, m.nombre_modulo,
                              u.nivel, n.nombre_nivel, u.estado
                              FROM db_usuarios u
                              LEFT JOIN db_modulos m ON u.modulo = m.id_modulo
                              LEFT JOIN db_nivel_acceso n ON u.nivel = n.id_nivel
                              LEFT JOIN db_servicios s ON u.servicio = s.id");
        $datos = [];
        while ($row = $query->fetch_assoc()) {
            $datos[] = $row;
        }
        $arrUsuario = $datos;
        if (!empty($arrUsuario)) {
            for ($i = 0; $i < count($arrUsuario); $i++) {
                $activo = "";
                $inactivo = "";
                $editar = "";

                if ($arrUsuario[$i]["estado"] == "A") {
                    $arrUsuario[$i]["estado"] = '<span class="badge bg-success">Activo</span>';
                    $inac = 2;
                    $activo = '<button class="btn btn-icon btn-sm btn-danger" onClick="InactivarUsuario(' . $arrUsuario[$i]['id_usuario'] . ',' . "$inac" . ')" title="Deshabilitar Usuario"><i class="bx bxs-message-square-x"></i></button>';
                } else {
                    $arrUsuario[$i]["estado"] = '<span class="badge bg-danger">Inactivo</span>';
                    $act = 1;
                    $inactivo = '<button class="btn btn-icon btn-sm btn-primary" onClick="InactivarUsuario(' . $arrUsuario[$i]['id_usuario'] . ','. "$act" .')" title="Habilitar Usuario"><i class="bx bxs-message-square-x"></i></button>';
                }

                $editar = '<button class="btn btn-icon btn-sm btn-success" onClick="seleccionarUsuario(' . $arrUsuario[$i]['id_usuario'] . ')" title="Editar Usuario"><i class="bx bxs-edit"></i></button>';
               
                $arrUsuario[$i]["opciones"] = '<div class="text-center">' . $editar . ' ' . $activo . ' ' . $inactivo . '</div>';
            }
            $arrResponse["data"] = $arrUsuario;
        } else {
            $arrResponse = [];
        }
        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    if ($_POST['accion'] == 'Obtenerusuario') {
        $id_usuario = $_POST['datos'];
        $stmt = $conn->prepare("SELECT u.id_usuario, u.usuario, u.cedula, u.nombres, u.apellidos, u.servicio, u.modulo, u.nivel 
                               FROM db_usuarios u WHERE u.id_usuario = ?");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && $row['id_usuario'] > 0) {
            $respuesta = [
                'codigo' => 0,
                'usuario' => $row['usuario'],
                'documento' => $row['cedula'],
                'nombres' => $row['nombres'],
                'apellidos' => $row['apellidos'],
                'servicio' => $row['servicio'],
                'modulo' => $row['modulo'],
                'nivel' => $row['nivel']
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

    if ($_POST['accion'] == 'RegistroUsuario') {
        $datos = json_decode($_POST['datos']);
        $usuario = $datos->usuario;
        $cedula = $datos->numero;
        $nombres = $datos->nombres;
        $apellidos = $datos->apellidos;
        $password = $datos->password;
        $servicio = $datos->servicio;
        $modulo = $datos->modulo;
        $nivel = $datos->nivel;
        if (empty($usuario) || empty($cedula) || empty($nombres) || empty($apellidos) || empty($password) || empty($servicio) || empty($modulo) || empty($nivel)) {
            $respuesta = [
                "codigo" => 2,
                "respuesta" => 'Verificar los Campos Vacios',
            ];
        } else {
            $opciones = ['cost' => 12];
            $password_hashed = password_hash($password, PASSWORD_BCRYPT, $opciones);
            $stmt = $conn->prepare("INSERT INTO db_usuarios (usuario, cedula, nombres, apellidos, password, servicio, modulo, estado, nivel, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, 'A', ?, NOW())");
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $conn->error);
            }
            $stmt->bind_param("sssssiii", $usuario, $cedula, $nombres, $apellidos, $password_hashed, $servicio, $modulo, $nivel);
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
                    'respuesta' => 'No se Logró Registrar el Cliente',
                ];
            }
        }
        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'ActualizarUsuario') {
        $datos = json_decode($_POST['datos']);
        $usuario = $datos->usuario;
        $cedula = $datos->numero;
        $nombres = $datos->nombres;
        $apellidos = $datos->apellidos;
        $password = $datos->password;
        $servicio = $datos->servicio;
        $modulo = $datos->modulo;
        $nivel = $datos->nivel;

        if (empty($usuario) || empty($cedula) || empty($nombres) || empty($apellidos) || empty($modulo) || empty($servicio) || empty($nivel)) {
            $respuesta = [
                "codigo" => 2,
                "respuesta" => 'Verificar los Campos Vacios',
            ];
        } else {
            $opciones = ['cost' => 12];
            if ($password == "") {
                $stmt = $conn->prepare("UPDATE db_usuarios SET usuario = ?, nombres = ?, apellidos = ?, servicio = ?, modulo = ?, nivel = ? WHERE cedula = ?");
                if (!$stmt) {
                    throw new Exception('Error al preparar la consulta: ' . $conn->error);
                }
                $stmt->bind_param("sssiiis", $usuario, $nombres, $apellidos, $servicio, $modulo, $nivel, $cedula);
                $stmt->execute();
            } else {
                $password_hashed = password_hash($password, PASSWORD_BCRYPT, $opciones);
                $stmt = $conn->prepare("UPDATE db_usuarios SET usuario = ?, password = ?, nombres = ?, apellidos = ?, servicio = ?, modulo = ?, nivel = ? WHERE cedula = ?");
                if (!$stmt) {
                    throw new Exception('Error al preparar la consulta: ' . $conn->error);
                }
                $stmt->bind_param("sssiiis", $usuario, $password_hashed, $nombres, $apellidos, $servicio, $modulo, $nivel, $cedula);
                $stmt->execute();
            }

            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($affected_rows > 0) {
                $respuesta = [
                    'codigo' => 0,
                    'respuesta' => 'Registro Actualizado Correctamente'
                ];
            } else {
                $respuesta = [
                    'codigo' => 1,
                    'respuesta' => 'No se Logró Actualizar el Usuario',
                ];
            }
        }
        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    if ($_POST['accion'] == 'ActualizarEstado') {
        $id_usuario = $_POST['datos'];
        $estado = $_POST['estado'];
        $stmt = $conn->prepare("UPDATE db_usuarios SET estado = ? WHERE id_usuario = ?");
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        $stmt->bind_param("si", $estado, $id_usuario);
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

    if ($_POST['accion'] == 'VerServicios') {
        $query = $conn->query("SELECT id_servicio, nombre_servicio FROM db_servicios");
        $datos = [];
        while ($row = $query->fetch_assoc()) {
            $datos[] = $row;
        }

        $arraData = $datos;
        if (empty($arraData)) {
            $arrResponse = ['status' => false, 'msg' => 'Datos no encontrados'];
        } else {
            $arrResponse = ['status' => true, 'data' => $arraData];
        }
        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    if ($_POST['accion'] == 'VerModulos') {
        $query = $conn->query("SELECT id_modulo, nombre_modulo FROM db_modulos");
        $datos = [];
        while ($row = $query->fetch_assoc()) {
            $datos[] = $row;
        }

        $arraData = $datos;
        if (empty($arraData)) {
            $arrResponse = ['status' => false, 'msg' => 'Datos no encontrados'];
        } else {
            $arrResponse = ['status' => true, 'data' => $arraData];
        }
        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    if ($_POST['accion'] == 'VerNiveles') {
        $query = $conn->query("SELECT id_nivel, nombre_nivel FROM db_nivel_acceso");
        $datos = [];
        while ($row = $query->fetch_assoc()) {
            $datos[] = $row;
        }

        $arraData = $datos;
        if (empty($arraData)) {
            $arrResponse = ['status' => false, 'msg' => 'Datos no encontrados'];
        } else {
            $arrResponse = ['status' => true, 'data' => $arraData];
        }
        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    // Si no se especifica una acción válida
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    error_log('Error en model_usuario.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

$conn->close();
?>