<?php
require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $pdo = getDBConnection();
    $usuario_id = $_SESSION['usuario_id'];
    
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    
    // Validaciones
    if (empty($nombre) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'El nombre y correo son obligatorios']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido']);
        exit;
    }
    
    // Verificar si el email ya existe (para otro usuario)
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND id != ?");
    $stmt->execute([$email, $usuario_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este correo electrónico ya está registrado']);
        exit;
    }
    
    // Actualizar datos del usuario
    $stmt = $pdo->prepare("UPDATE usuario SET nombre = ?, email = ?, telefono = ? WHERE id = ?");
    $stmt->execute([$nombre, $email, $telefono, $usuario_id]);
    
    // Actualizar la sesión
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['usuario_telefono'] = $telefono;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Datos actualizados correctamente',
        'data' => [
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error al actualizar perfil: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos: ' . $e->getMessage()]);
}
?>

