<?php
if (file_exists('config/conexion.php')) {
    echo "El archivo config/conexion.php existe.";
    require_once 'config/conexion.php';
    $conn = getConnection();
    echo "Conexión exitosa.";
} else {
    echo "El archivo config/conexion.php NO existe.";
}
?>