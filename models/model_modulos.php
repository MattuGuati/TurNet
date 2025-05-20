<?php
// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    $error_message = 'Archivo config/conexion.php no encontrado en model_modulos.php. Ruta absoluta buscada: ' . realpath('../config/conexion.php');
    error_log($error_message);
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => $error_message]);
    exit;
}

require_once '../config/conexion.php';

try {
    $conn = getConnection();

    // Acción para listar módulos
    if (isset($_POST['action']) && $_POST['action'] == 'listarModulos') {
        $query = "SELECT m.id_modulo, m.nombre_modulo, m.estado, m.fecha_registro, u.usuario AS usuario_asignado
                 FROM db_modulos m
                 LEFT JOIN db_usuarios u ON m.usuario_id = u.id_usuario";
        $result = $conn->query($query);

        $datos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }
            $result->free();
        }

        $arrModulos = $datos;
        if (!empty($arrModulos)) {
            for ($i = 0; $i < count($arrModulos); $i++) {
                $arrModulos[$i]['estado'] = $arrModulos[$i]['estado'] == 'A' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            }
            $arrResponse = ['status' => true, 'data' => $arrModulos];
        } else {
            $arrResponse = ['status' => false, 'data' => []];
        }

        header('Content-Type: application/json');
        echo json_encode($arrResponse);
        exit;
    }

    // Si no se especifica una acción válida
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    error_log('Error en model_modulos.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

$conn->close();
?>