<?php
/**
 * Script para eliminar la cuenta del usuario
 */

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
    file_put_contents($logFile, "[$timestamp] [DELETE_ACCOUNT] $message\n", FILE_APPEND);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    logDebug('Inicio de eliminación de cuenta');

    // Obtener datos del formulario
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmacion = isset($_POST['confirmacion']) ? $_POST['confirmacion'] : '';

    // Validar datos
    if (empty($password)) {
        throw new Exception('Debes ingresar tu contraseña para confirmar');
    }

    if ($confirmacion !== 'ELIMINAR') {
        throw new Exception('Debes escribir "ELIMINAR" para confirmar la acción');
    }

    $usuario = obtenerUsuarioActual();
    
    if (!$usuario) {
        throw new Exception('No se pudo obtener la información del usuario');
    }

    // Verificar contraseña
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT password FROM usuario WHERE id = ?");
    $stmt->execute([$usuario['id']]);
    $userData = $stmt->fetch();

    if (!$userData) {
        throw new Exception('Usuario no encontrado');
    }

    if (!password_verify($password, $userData['password'])) {
        logDebug('Contraseña incorrecta al intentar eliminar cuenta - Usuario ID: ' . $usuario['id']);
        throw new Exception('La contraseña es incorrecta');
    }

    logDebug('Iniciando eliminación de cuenta - Usuario ID: ' . $usuario['id'] . ', Nombre: ' . $usuario['nombre']);

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
        // Eliminar avatar si existe
        if (!empty($usuario['avatar']) && file_exists($usuario['avatar']) && $usuario['avatar'] !== 'uploads/avatars/default-avatar.png') {
            if (unlink($usuario['avatar'])) {
                logDebug('Avatar eliminado: ' . $usuario['avatar']);
            }
        }

        // Eliminar registros relacionados en orden (debido a las foreign keys)
        
        // 1. Eliminar de tabla cliente (si existe)
        $stmt = $pdo->prepare("DELETE FROM cliente WHERE usuarioId = ?");
        $stmt->execute([$usuario['id']]);
        logDebug('Registro de cliente eliminado (si existía)');

        // 2. Eliminar de tabla administrador (si existe)
        $stmt = $pdo->prepare("DELETE FROM administrador WHERE usuarioId = ?");
        $stmt->execute([$usuario['id']]);
        logDebug('Registro de administrador eliminado (si existía)');

        // NOTA: Aquí deberías eliminar otros registros relacionados según tu BD
        // Por ejemplo:
        // - Pedidos: Podrías marcarlos como "usuario eliminado" en lugar de borrarlos
        // - Comentarios: Podrías anonimizarlos
        // - Etc.

        // Ejemplo para pedidos (opcional - ajusta según tu estructura):
        // $stmt = $pdo->prepare("UPDATE pedidos SET usuarioId = NULL, estado = 'usuario_eliminado' WHERE usuarioId = ?");
        // $stmt->execute([$usuario['id']]);

        // 3. Finalmente, eliminar el usuario
        $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        logDebug('Usuario eliminado de la base de datos');

        // Confirmar transacción
        $pdo->commit();
        logDebug('Cuenta eliminada exitosamente - Usuario ID: ' . $usuario['id']);

        // Destruir sesión
        session_destroy();

        echo json_encode([
            'success' => true,
            'message' => 'Tu cuenta ha sido eliminada correctamente',
            'redirect' => 'index.php'
        ]);

    } catch (Exception $e) {
        // Revertir cambios si hay error
        $pdo->rollBack();
        throw new Exception('Error al eliminar la cuenta: ' . $e->getMessage());
    }

} catch (Exception $e) {
    logDebug('ERROR: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>