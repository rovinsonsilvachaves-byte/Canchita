<?php
header('Content-Type: application/json');
include 'conexion.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$rol = isset($_POST['rol']) ? $_POST['rol'] : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

if ($id === 0 || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Datos insuficientes para editar el usuario.']);
    exit;
}

if (!empty($contrasena)) {
    // Si se escribió contraseña
    $stmt = $conexion->prepare("UPDATE usuarios SET rol = ?, contrasena = ? WHERE id = ?");
    $stmt->bind_param("ssi", $rol, $contrasena, $id);
} else {
    // Si no se escribió contraseña, solo se actualiza el rol
    $stmt = $conexion->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->bind_param("si", $rol, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario en la base de datos.']);
}

$stmt->close();
$conexion->close();
?>
