<?php
header('Content-Type: application/json');
include 'conexion.php';

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
$rol = isset($_POST['rol']) ? $_POST['rol'] : '';

if (empty($usuario) || empty($contrasena) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios para registrar un usuario.']);
    exit;
}

// Validar que el rol sea uno de los permitidos
if ($rol !== 'Administrador' && $rol !== 'Empleado') {
    echo json_encode(['success' => false, 'message' => 'Rol no válido.']);
    exit;
}

// Verificar si el usuario ya existe para evitar errores de clave duplicada
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ? LIMIT 1");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está registrado.']);
    $stmt->close();
    $conexion->close();
    exit;
}
$stmt->close();

// Insertar el nuevo usuario en la base de datos
$stmt = $conexion->prepare("INSERT INTO usuarios (usuario, contrasena, rol) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $usuario, $contrasena, $rol);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario registrado con éxito.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario en la base de datos: ' . $conexion->error]);
}

$stmt->close();
$conexion->close();
?>