<?php
header('Content-Type: application/json');
include 'conexion.php';

// Obtener todas las claves enviadas por POST
$claves_validas = [
    'precio_futbol_dia',
    'precio_futbol_noche',
    'precio_voley_dia',
    'precio_voley_noche',
    'hora_inicio_noche'
];

$success = true;
$message = 'Ajustes guardados con éxito.';

foreach ($claves_validas as $clave) {
    if (isset($_POST[$clave])) {
        $valor = trim($_POST[$clave]);
        
        // Evitar inyección y actualizar o insertar
        $stmt = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->bind_param("sss", $clave, $valor, $valor);
        
        if (!$stmt->execute()) {
            $success = false;
            $message = 'Error al guardar el ajuste: ' . $clave;
            $stmt->close();
            break;
        }
        $stmt->close();
    }
}

echo json_encode([
    'success' => $success,
    'message' => $message
]);

$conexion->close();
?>
