<?php
// Habilitar errores para debugging (comentar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'php/check_auth.php';
verificarAutenticacion();

header('Content-Type: application/json');

// Log de debugging
function logDebug($message) {
    $logFile = 'uploads/avatars/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    logDebug('Inicio de subida de avatar');

    if (!isset($_FILES['avatar'])) {
        throw new Exception('No se recibió ningún archivo');
    }

    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
        ];
        $error = $errorMessages[$_FILES['avatar']['error']] ?? 'Error desconocido al subir el archivo';
        throw new Exception($error);
    }

    $file = $_FILES['avatar'];
    logDebug('Archivo recibido: ' . $file['name'] . ' - Tamaño: ' . $file['size']);

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Validar tipo de archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    logDebug('Tipo MIME detectado: ' . $mimeType);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido. Solo se aceptan imágenes (JPG, PNG, GIF, WEBP)');
    }

    // Validar tamaño
    if ($file['size'] > $maxSize) {
        throw new Exception('El archivo es demasiado grande. Tamaño máximo: 5MB');
    }

    // Crear directorio de avatares si no existe
    $uploadDir = 'uploads/avatars/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de avatares');
        }
        logDebug('Directorio creado: ' . $uploadDir);
    }

    // Generar nombre único para el archivo
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $usuario = obtenerUsuarioActual();
    
    if (!$usuario) {
        throw new Exception('No se pudo obtener la información del usuario');
    }

    $fileName = 'avatar_' . $usuario['id'] . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    logDebug('Intentando guardar en: ' . $filePath);

    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Error al guardar el archivo. Verifica los permisos del directorio.');
    }

    logDebug('Archivo guardado exitosamente');

    // Eliminar avatar anterior si existe
    if (!empty($usuario['avatar']) && file_exists($usuario['avatar']) && $usuario['avatar'] !== 'uploads/avatars/default-avatar.png') {
        if (unlink($usuario['avatar'])) {
            logDebug('Avatar anterior eliminado: ' . $usuario['avatar']);
        }
    }

    // Actualizar base de datos usando PDO
    $pdo = getDBConnection();

    if (!$pdo) {
        throw new Exception('Error al conectar con la base de datos');
    }

    // IMPORTANTE: La tabla es 'usuario', no 'usuarios'
    $stmt = $pdo->prepare("UPDATE usuario SET avatar = ? WHERE id = ?");
    
    if (!$stmt->execute([$filePath, $usuario['id']])) {
        // Si falla la actualización en BD, eliminar el archivo subido
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        throw new Exception('Error al actualizar la base de datos');
    }

    logDebug('Base de datos actualizada correctamente');

    $_SESSION['avatar'] = $filePath;

    echo json_encode([
        'success' => true, 
        'message' => 'Foto de perfil actualizada correctamente',
        'avatar_url' => $filePath . '?v=' . time()
    ]);

} catch (Exception $e) {
    logDebug('ERROR: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>