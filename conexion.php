<?php
$host = "localhost";
$user = "root";
$pass = ""; // En XAMPP por defecto la contraseña viene vacía
$db   = "gestion_canchas";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar para recibir caracteres con tildes y eñes correctamente
$conexion->set_charset("utf8mb4");
?>