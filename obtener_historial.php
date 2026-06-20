<?php
header('Content-Type: application/json');
include 'conexion.php';

// Consultar todas las reservas con el nombre de cancha
$sql = "SELECT r.id, r.cliente, r.fecha, r.hora_inicio, r.hora_fin, r.monto_pago, r.estado, c.nombre_cancha 
        FROM reservas r
        JOIN canchas c ON r.cancha_id = c.id
        ORDER BY r.fecha DESC, r.hora_inicio DESC 
        LIMIT 100";

$resultado = $conexion->query($sql);
$historial = [];

if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $historial[] = $row;
    }
}

echo json_encode($historial);
$conexion->close();
?>
