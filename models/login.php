<?php
session_start();

// Incluir el archivo de conexión
if (!file_exists('../config/conexion.php')) {
    $error_message = 'Archivo config/conexion.php no encontrado en login.php. Ruta absoluta buscada: ' . realpath('../config/conexion.php');
    error_log($error_message);
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 3, 'mensaje' => $error_message]);
    exit;
}

require_once '../config/conexion.php';

if (!isset($_POST['accion']) || $_POST['accion'] != 'LoginUsuario') {
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 4, 'mensaje' => 'Acción no válida.']);
    exit;
}

try {
    $conn = getConnection();

    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($usuario) || empty($password)) {
        header('Content-Type: application/json');
        echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario o contraseña vacíos.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT u.usuario, u.nombres, u.apellidos, u.password, u.modulo, u.nivel, u.servicio
                           FROM db_usuarios u
                           INNER JOIN db_modulos m ON u.modulo = m.id_modulo
                           INNER JOIN db_servicios s ON u.servicio = s.id
                           WHERE u.usuario = ?");
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
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
            $respuesta = [
                'codigo' => 1,
                'mensaje' => 'Usuario o contraseña incorrectos.'
            ];
        }
    } else {
        $respuesta = [
            'codigo' => 2,
            'mensaje' => 'No se logró acceder al sistema.'
        ];
    }

    $stmt->close();
    $conn->close();

    // Guardar datos en localStorage
    if ($respuesta['codigo'] === 0) {
        echo "<script>
                localStorage.setItem('usuario', '" . $respuesta['usuario'] . "');
                localStorage.setItem('servicio', '" . $respuesta['servicio'] . "');
                localStorage.setItem('modulo', '" . $respuesta['modulo'] . "');
              </script>";
    }

    header('Content-Type: application/json');
    echo json_encode($respuesta);
} catch (Exception $e) {
    error_log('Error en login.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['codigo' => 3, 'mensaje' => 'Error al iniciar sesión: ' . $e->getMessage()]);
}
?>