<?php
header('Content-Type: application/json');
include 'conexion.php';

// Recibir cancha, hora_id y fecha
$cancha = isset($_POST['cancha']) ? $_POST['cancha'] : '';
$hora_id = isset($_POST['hora_id']) ? intval($_POST['hora_id']) : 0;
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');

if (empty($cancha) || $hora_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para liberar la reserva.']);
    exit;
}

// Convertir cancha a ID
$cancha_id = 1; // Por defecto Fútbol
if ($cancha === 'voley1') $cancha_id = 2;
if ($cancha === 'voley2') $cancha_id = 3;

// Validar que no sea un horario pasado
$fecha_hoy = date('Y-m-d');
$hora_actual = intval(date('H'));
$es_pasado = false;

if ($fecha < $fecha_hoy) {
    $es_pasado = true;
} elseif ($fecha === $fecha_hoy && $hora_id < $hora_actual) {
    $es_pasado = true;
}

if ($es_pasado) {
    echo json_encode(['success' => false, 'message' => 'Seguridad: No está permitido liberar o cancelar reservas de horarios pasados.']);
    $conexion->close();
    exit;
}

$hora_inicio = sprintf("%02d:00:00", $hora_id);

// En lugar de eliminar físicamente (DELETE), actualizamos el estado a 'Cancelada'
// Esto permite conservar el historial de lo que sucedió en el sistema
$sql = "UPDATE reservas SET estado = 'Cancelada' WHERE cancha_id = ? AND fecha = ? AND hora_inicio = ? AND estado = 'Activa'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iss", $cancha_id, $fecha, $hora_inicio);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Reserva liberada (cancelada) con éxito y registrada en el historial.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró ninguna reserva activa en ese horario y fecha.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado de la reserva: ' . $conexion->error]);
}

$stmt->close();
$conexion->close();
?>
