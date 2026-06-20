<?php
header('Content-Type: application/json');
include 'conexion.php';

$sql = "SELECT clave, valor FROM configuracion";
$resultado = $conexion->query($sql);

$config = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $config[$row['clave']] = $row['valor'];
    }
}

// Valores por defecto por si falta alguno
$defaults = [
    'precio_futbol_dia' => '40.00',
    'precio_futbol_noche' => '60.00',
    'precio_voley_dia' => '30.00',
    'precio_voley_noche' => '45.00',
    'hora_inicio_noche' => '18'
];

foreach ($defaults as $key => $val) {
    if (!isset($config[$key])) {
        $config[$key] = $val;
    }
}

echo json_encode($config);
$conexion->close();
?>
