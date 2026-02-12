<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $nombre_completo = trim($_POST['nombres'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirmPassword'] ?? '';
    
    // Validaciones básicas
    $errores = [];
    
    if (empty($nombre_completo) || strlen($nombre_completo) < 3) {
        $errores[] = 'El nombre completo debe tener al menos 3 caracteres';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido';
    }
    
    if (empty($telefono) || !preg_match('/^[0-9]{7,15}$/', $telefono)) {
        $errores[] = 'El teléfono debe tener entre 7 y 15 dígitos';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if ($password !== $confirm_password) {
        $errores[] = 'Las contraseñas no coinciden';
    }
    
    if (!empty($errores)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errores)]);
        exit;
    }
    
    // Conectar a la base de datos
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado']);
        exit;
    }
    
    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $pdo->beginTransaction();
    
    try {
        // Insertar en tabla usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuario (nombre, email, contraseña, telefono, activo) 
            VALUES (?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $nombre_completo,
            $email,
            $password_hash,
            $telefono
        ]);
        
        // Obtener el ID del usuario recién creado
        $usuario_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("
            INSERT INTO cliente (usuarioId, fecha_ultima_Compra, puntosLealtad) 
            VALUES (?, NOW(), 0)
        ");
        
        $stmt->execute([$usuario_id]);
        
        // Confirmar transacción
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => '¡Registro exitoso! Ahora puedes iniciar sesión.',
            'redirect' => './login.php'
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción si algo falla
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Error en registro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al registrar usuario. Por favor, intenta de nuevo.']);
} catch (Exception $e) {
    error_log("Error general en registro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error inesperado. Por favor, intenta de nuevo.']);
}
?>
