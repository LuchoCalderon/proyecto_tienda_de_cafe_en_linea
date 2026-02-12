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
    
    // Obtener confirmación de contraseña
    $password = $_POST['password'] ?? '';
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Debes ingresar tu contraseña para confirmar']);
        exit;
    }
    
    // Verificar contraseña
    $stmt = $pdo->prepare("SELECT contraseña FROM usuario WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Verificar si la contraseña es correcta
    $password_valida = false;
    if (password_verify($password, $usuario['contraseña'])) {
        $password_valida = true;
    } elseif ($password === $usuario['contraseña']) {
        $password_valida = true;
    }
    
    if (!$password_valida) {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        exit;
    }
    
    // Iniciar transacción para eliminar todo de forma segura
    $pdo->beginTransaction();
    
    try {
        // Eliminar registros relacionados (cliente)
        $stmt = $pdo->prepare("DELETE FROM cliente WHERE usuarioId = ?");
        $stmt->execute([$usuario_id]);
        
        // Eliminar registros relacionados (administrador si existe)
        $stmt = $pdo->prepare("DELETE FROM administrador WHERE usuarioId = ?");
        $stmt->execute([$usuario_id]);
        
        // Eliminar el usuario
        $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");
        $stmt->execute([$usuario_id]);
        
        $pdo->commit();
        
        // Destruir la sesión
        session_destroy();
        
        echo json_encode(['success' => true, 'message' => 'Cuenta eliminada correctamente']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error al eliminar cuenta: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la cuenta: ' . $e->getMessage()]);
}
?>
