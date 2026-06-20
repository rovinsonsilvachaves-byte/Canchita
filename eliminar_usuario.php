<?php
header('Content-Type: application/json');
include 'conexion.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id === 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado.']);
    exit;
}

// Obtener detalles del usuario para evitar eliminar al admin principal
$stmt = $conexion->prepare("SELECT usuario FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $userRow = $resultado->fetch_assoc();
    if ($userRow['usuario'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar el usuario administrador principal.']);
        $stmt->close();
        $conexion->close();
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'El usuario a eliminar no existe.']);
    $stmt->close();
    $conexion->close();
    exit;
}
$stmt->close();

// Eliminar el usuario
$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado con éxito.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario de la base de datos: ' . $conexion->error]);
}

$stmt->close();
$conexion->close();
?>
