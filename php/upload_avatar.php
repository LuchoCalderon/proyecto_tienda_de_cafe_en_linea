<?php
session_start();

// Desactivar visualización de errores
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Establecer headers JSON
header('Content-Type: application/json');

// Limpiar buffer de salida
if (ob_get_length()) ob_end_clean();
ob_start();

require_once __DIR__ . '/../config/db_config.php';

try {
    // Verificar autenticación
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('No autenticado');
    }

    // Verificar que se haya subido un archivo
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió ningún archivo o hubo un error en la subida');
    }

    $file = $_FILES['avatar'];
    
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido. Solo se aceptan JPG, PNG, GIF y WEBP');
    }
    
    // Validar tamaño (máximo 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        throw new Exception('El archivo es demasiado grande. Máximo 5MB');
    }
    
    // Crear directorio si no existe
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'avatar_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $fileName;
    $relativePath = 'uploads/avatars/' . $fileName;
    
    // Obtener conexión a la base de datos
    $pdo = getDBConnection();
    
    // Obtener avatar anterior para eliminarlo
    $stmt = $pdo->prepare("SELECT avatar FROM usuario WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $oldAvatar = $stmt->fetchColumn();
    
    // Mover archivo subido
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Error al guardar el archivo');
    }
    
    // Actualizar base de datos
    $stmt = $pdo->prepare("UPDATE usuario SET avatar = ? WHERE id = ?");
    $stmt->execute([$relativePath, $_SESSION['usuario_id']]);
    
    // Eliminar avatar anterior si existe
    if ($oldAvatar && file_exists(__DIR__ . '/../' . $oldAvatar)) {
        unlink(__DIR__ . '/../' . $oldAvatar);
    }
    
    // Actualizar sesión
    $_SESSION['avatar'] = $relativePath;
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Foto actualizada correctamente',
        'avatar' => $relativePath
    ]);
    exit;
    
} catch (Exception $e) {
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
