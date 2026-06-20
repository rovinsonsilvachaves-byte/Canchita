<?php
header('Content-Type: application/json');
include 'conexion.php';

// Obtener los datos enviados desde el formulario
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

if (empty($usuario) || empty($contrasena)) {
    echo json_encode(['success' => false, 'message' => 'Por favor rellene todos los campos.']);
    exit;
}

// Consulta segura usando Sentencias Preparadas para evitar hackeos (Inyección SQL)
$stmt = $conexion->prepare("SELECT id, usuario, contrasena, rol FROM usuarios WHERE usuario = ? LIMIT 1");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $userRow = $resultado->fetch_assoc();
    
    // Verificación de la contraseña (en texto plano por ahora, como lo tienes configurado)
    if ($contrasena === $userRow['contrasena']) {
        // Login correcto: Devolvemos los datos del usuario
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userRow['id'],
                'usuario' => $userRow['usuario'],
                'rol' => $userRow['rol']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'El usuario no existe.']);
}

$stmt->close();
$conexion->close();
?>