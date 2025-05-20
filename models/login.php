<?php
session_start();

// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    $mensajeError = 'Archivo config/conexion.php no encontrado en login.php. Ruta absoluta buscada: ' . realpath('../config/conexion.php');
    error_log($mensajeError);
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 3, 'mensaje' => $mensajeError]);
    exit;
}

require_once '../config/conexion.php';

if (!isset($_POST['accion']) || $_POST['accion'] != 'LoginUsuario') {
    error_log("Acción no válida en login.php: " . (isset($_POST['accion']) ? $_POST['accion'] : 'No definida'));
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 4, 'mensaje' => 'Acción no válida.']);
    exit;
}

try {
    $conn = getConnection();

    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $contrasena = isset($_POST['password']) ? trim($_POST['password']) : '';

    error_log("Intento de inicio de sesión - usuario: $usuario");

    if (empty($usuario) || empty($contrasena)) {
        error_log("Usuario o contraseña vacíos - usuario: $usuario");
        header('Content-Type: application/json');
        echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario o contraseña vacíos.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT u.usuario, u.nombres, u.apellidos, u.password, u.modulo, u.nivel, u.servicio
                           FROM db_usuarios u
                           LEFT JOIN db_modulos m ON u.modulo = m.id_modulo
                           LEFT JOIN db_servicios s ON u.servicio = s.id
                           LEFT JOIN db_nivel_acceso n ON u.nivel = n.id_nivel
                           WHERE u.usuario = ?");
    if (!$stmt) {
        $mensajeError = 'Error al preparar la consulta: ' . $conn->error;
        error_log($mensajeError);
        throw new Exception($mensajeError);
    }
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        error_log("Usuario encontrado - usuario: $usuario, contraseña en DB: " . $user['password']);
        if (password_verify($contrasena, $user['password'])) {
            error_log("Contraseña verificada correctamente - usuario: $usuario");
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['nombre'] = $user['nombres'] . ' ' . $user['apellidos'];
            $_SESSION['modulo'] = $user['modulo'];
            $_SESSION['nivel'] = $user['nivel'];
            $_SESSION['servicio'] = $user['servicio'];
            $respuesta = [
                'codigo' => 0,
                'mensaje' => $user['nombres'] . ' ' . $user['apellidos'],
                'usuario' => $user['usuario'],
                'servicio' => $user['servicio'],
                'modulo' => $user['modulo']
            ];
        } else {
            error_log("Contraseña incorrecta - usuario: $usuario");
            $respuesta = [
                'codigo' => 1,
                'mensaje' => 'Usuario o contraseña incorrectos.'
            ];
        }
    } else {
        error_log("Usuario no encontrado - usuario: $usuario");
        $respuesta = [
            'codigo' => 2,
            'mensaje' => 'No se logró acceder al sistema.'
        ];
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($respuesta);
} catch (Exception $e) {
    error_log('Error en login.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 3, 'mensaje' => 'Error al iniciar sesión: ' . $e->getMessage()]);
}
?>