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
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    // Validaciones
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }
    
    if ($password_nueva !== $password_confirmar) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
        exit;
    }
    
    if (strlen($password_nueva) < 8) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
        exit;
    }
    
    // Verificar contraseña actual
    $stmt = $pdo->prepare("SELECT contraseña FROM usuario WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Verificar si la contraseña actual es correcta
    // Soportamos tanto contraseñas hasheadas como texto plano (para migración)
    $password_valida = false;
    if (password_verify($password_actual, $usuario['contraseña'])) {
        $password_valida = true;
    } elseif ($password_actual === $usuario['contraseña']) {
        // Contraseña en texto plano (migración)
        $password_valida = true;
    }
    
    if (!$password_valida) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
        exit;
    }
    
    // Hashear la nueva contraseña
    $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
    
    // Actualizar contraseña
    $stmt = $pdo->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
    $stmt->execute([$password_hash, $usuario_id]);
    
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
    
} catch (Exception $e) {
    error_log("Error al cambiar contraseña: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al cambiar la contraseña']);
}
?>
