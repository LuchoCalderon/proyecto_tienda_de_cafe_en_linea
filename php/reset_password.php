<?php
/**
 * php/reset_password.php - Backend para restablecer la contrasena
 * 
 * Acciones:
 * - GET  ?action=verify&token=xxx  : Verifica si el token es valido
 * - POST { token, password, password_confirm } : Actualiza la contrasena
 */

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/db_config.php';
    $pdo = getDBConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // ==========================================
        // VERIFICAR TOKEN
        // ==========================================
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token no proporcionado']);
            exit;
        }
        
        $tokenHash = hash('sha256', $token);
        
        $stmt = $pdo->prepare("
            SELECT id, email, expires_at 
            FROM password_resets 
            WHERE token = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reset) {
            echo json_encode(['success' => true, 'message' => 'Token valido', 'email' => $reset['email']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'El enlace ha expirado o ya fue utilizado. Solicita uno nuevo.']);
        }
        
    } elseif ($method === 'POST') {
        // ==========================================
        // RESTABLECER CONTRASENA
        // ==========================================
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Validaciones
        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token no proporcionado']);
            exit;
        }
        
        if (empty($password) || empty($password_confirm)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            exit;
        }
        
        if ($password !== $password_confirm) {
            echo json_encode(['success' => false, 'message' => 'Las contrasenas no coinciden']);
            exit;
        }
        
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'La contrasena debe tener al menos 8 caracteres']);
            exit;
        }
        
        $tokenHash = hash('sha256', $token);
        
        // Verificar token
        $stmt = $pdo->prepare("
            SELECT id, email 
            FROM password_resets 
            WHERE token = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            echo json_encode(['success' => false, 'message' => 'El enlace ha expirado o ya fue utilizado. Solicita uno nuevo.']);
            exit;
        }
        
        // Iniciar transaccion
        $pdo->beginTransaction();
        
        try {
            // Actualizar contrasena del usuario
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuario SET contraseña = ? WHERE email = ?");
            $stmt->execute([$passwordHash, $reset['email']]);
            
            // Marcar token como usado
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->execute([$reset['id']]);
            
            // Invalidar todos los demas tokens del mismo email
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0");
            $stmt->execute([$reset['email']]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Contrasena actualizada correctamente. Ya puedes iniciar sesion.',
                'redirect' => 'login.php'
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    }
    
} catch (Exception $e) {
    error_log("Error en reset_password: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud']);
}
?>
