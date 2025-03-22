<?php
function getConnection() {
    $host = 'localhost';
    $dbname = 'db_digiturno';
    $username = 'root';
    $password = '';
    $mysqli = new mysqli($host, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        error_log('Conexión fallida: ' . $mysqli->connect_error);
        throw new Exception('Conexión fallida: ' . $mysqli->connect_error);
    }

    return $mysqli;
}
?>