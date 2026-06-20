<?php
header('Content-Type: application/json');
include 'conexion.php';

// Obtener la fecha seleccionada, por defecto hoy
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Consulta para traer TODAS las reservas activas del día específico para todas las canchas
// Esto permite calcular los bloqueos cruzados en el frontend
$sql = "SELECT id, cancha_id, HOUR(hora_inicio) AS hora_id, cliente FROM reservas WHERE fecha = ? AND estado = 'Activa'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $fecha);
$stmt->execute();
$resultado = $stmt->get_result();

$reservas = [];
while ($row = $resultado->fetch_assoc()) {
    $reservas[] = $row;
}

echo json_encode($reservas);

$stmt->close();
$conexion->close();
?>