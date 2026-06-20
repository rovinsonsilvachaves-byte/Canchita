<?php
header('Content-Type: application/json');
include 'conexion.php';

// Obtener todos los usuarios de la base de datos
$sql = "SELECT id, usuario, rol FROM usuarios ORDER BY id ASC";
$resultado = $conexion->query($sql);

$usuarios = [];

if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

echo json_encode($usuarios);

$conexion->close();
?>
