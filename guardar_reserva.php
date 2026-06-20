<?php
header('Content-Type: application/json');
include 'conexion.php';

// Recibir datos de la solicitud
$cancha = isset($_POST['cancha']) ? $_POST['cancha'] : '';
$cliente = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
$hora_id = isset($_POST['hora_id']) ? intval($_POST['hora_id']) : 0;
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');

if (empty($cancha) || empty($cliente) || $hora_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

// Convertir identificador de cancha a ID de base de datos
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
    echo json_encode(['success' => false, 'message' => 'No está permitido realizar reservas en horarios pasados.']);
    $conexion->close();
    exit;
}

$hora_inicio = sprintf("%02d:00:00", $hora_id);
$hora_fin = sprintf("%02d:00:00", $hora_id + 1);

// 1. VALIDAR CONFLICTOS DE ESPACIO (Vóley dentro de Fútbol)
// Si queremos reservar Fútbol (1): No debe haber reservas activas de Fútbol (1), Vóley 1 (2) ni Vóley 2 (3).
// Si queremos reservar Vóley 1 (2): No debe haber reservas activas de Fútbol (1) ni Vóley 1 (2).
// Si queremos reservar Vóley 2 (3): No debe haber reservas activas de Fútbol (1) ni Vóley 2 (3).

$conflict_ids = [];
if ($cancha_id === 1) {
    $conflict_ids = [1, 2, 3];
} elseif ($cancha_id === 2) {
    $conflict_ids = [1, 2];
} elseif ($cancha_id === 3) {
    $conflict_ids = [1, 3];
}

$in_clause = implode(',', $conflict_ids);
$sql_conflict = "SELECT id, cancha_id, cliente FROM reservas WHERE fecha = ? AND hora_inicio = ? AND cancha_id IN ($in_clause) AND estado = 'Activa' LIMIT 1";
$stmt_conflict = $conexion->prepare($sql_conflict);
$stmt_conflict->bind_param("ss", $fecha, $hora_inicio);
$stmt_conflict->execute();
$res_conflict = $stmt_conflict->get_result();

if ($res_conflict->num_rows > 0) {
    $conflicto = $res_conflict->fetch_assoc();
    $nombre_bloqueante = "Fútbol";
    if ($conflicto['cancha_id'] == 2) $nombre_bloqueante = "Vóley 1";
    if ($conflicto['cancha_id'] == 3) $nombre_bloqueante = "Vóley 2";
    
    echo json_encode([
        'success' => false, 
        'message' => "El horario ya está ocupado o bloqueado por una reserva activa de: $nombre_bloqueante (Cliente: " . $conflicto['cliente'] . ")."
    ]);
    $stmt_conflict->close();
    $conexion->close();
    exit;
}
$stmt_conflict->close();


// 2. OBTENER PRECIO CONFIGURADO DESDE LA BASE DE DATOS
$config = [
    'precio_futbol_dia' => 40.00,
    'precio_futbol_noche' => 60.00,
    'precio_voley_dia' => 30.00,
    'precio_voley_noche' => 45.00,
    'hora_inicio_noche' => 18
];
$res_config = $conexion->query("SELECT clave, valor FROM configuracion");
if ($res_config) {
    while ($row = $res_config->fetch_assoc()) {
        $config[$row['clave']] = $row['valor'];
    }
}

$hora_limite_noche = intval($config['hora_inicio_noche']);
$es_noche = ($hora_id >= $hora_limite_noche);

if ($cancha_id === 1) {
    $monto = $es_noche ? floatval($config['precio_futbol_noche']) : floatval($config['precio_futbol_dia']);
} else {
    $monto = $es_noche ? floatval($config['precio_voley_noche']) : floatval($config['precio_voley_dia']);
}


// 3. INSERTAR LA RESERVA
$usuario_id = 1; // Por defecto administrador (ID 1)
$sql_insert = "INSERT INTO reservas (cancha_id, usuario_id, cliente, fecha, hora_inicio, hora_fin, monto_pago, estado_pago, estado) 
               VALUES (?, ?, ?, ?, ?, ?, ?, 'Pagado', 'Activa')";

$stmt_insert = $conexion->prepare($sql_insert);
$stmt_insert->bind_param("iissssd", $cancha_id, $usuario_id, $cliente, $fecha, $hora_inicio, $hora_fin, $monto);

if ($stmt_insert->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Reserva guardada con éxito.',
        'monto' => $monto
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos: ' . $conexion->error]);
}

$stmt_insert->close();
$conexion->close();
?>