<?php
header('Content-Type: application/json');
include 'conexion.php';

$fecha_hoy = date('Y-m-d');
$mes_hoy = date('m');
$anio_hoy = date('Y');

// Ganancias del día (solo reservas activas hoy)
$sql_dia = "SELECT COALESCE(SUM(monto_pago), 0) AS total_dia FROM reservas WHERE fecha = ? AND estado = 'Activa'";
$stmt_dia = $conexion->prepare($sql_dia);
$stmt_dia->bind_param("s", $fecha_hoy);
$stmt_dia->execute();
$res_dia = $stmt_dia->get_result()->fetch_assoc();
$total_dia = floatval($res_dia['total_dia']);
$stmt_dia->close();

// Ganancias del mes actual (solo reservas activas este mes)
$sql_mes = "SELECT COALESCE(SUM(monto_pago), 0) AS total_mes FROM reservas WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND estado = 'Activa'";
$stmt_mes = $conexion->prepare($sql_mes);
$stmt_mes->bind_param("ss", $mes_hoy, $anio_hoy);
$stmt_mes->execute();
$res_mes = $stmt_mes->get_result()->fetch_assoc();
$total_mes = floatval($res_mes['total_mes']);
$stmt_mes->close();

// Retornar en JSON con los campos diaria y mensual
echo json_encode([
    'diaria' => $total_dia,
    'mensual' => $total_mes
]);

$conexion->close();
?>
