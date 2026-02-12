<?php
/**
 * check_auth.php - Middleware de autenticacion
 * 
 * Funciones disponibles:
 * - verificarAutenticacion() : Verifica que el usuario tenga sesion activa
 * - verificarAdmin()         : Verifica que sea administrador
 * - obtenerUsuarioActual()   : Retorna datos del usuario desde la BD
 */

// Iniciar sesion si no esta activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica que el usuario este autenticado
 * Si no tiene sesion, redirige a login.php
 */
function verificarAutenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?expired=1');
        exit;
    }
    
    // Verificar tiempo de sesion (8 horas maximo)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 8 * 60 * 60)) {
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
}

/**
 * Verifica que el usuario sea administrador
 * Si no es admin, redirige a pantalla de error o home
 */
function verificarAdmin() {
    // Primero verifica que este autenticado
    verificarAutenticacion();
    
    // Luego verifica el rol
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
        header('Location: pantalla_error.php?error=acceso_denegado');
        exit;
    }
}

/**
 * Obtiene los datos completos del usuario actual desde la BD
 * Retorna un array asociativo con los datos del usuario
 */
function obtenerUsuarioActual() {
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }
    
    try {
        require_once __DIR__ . '/../config/db_config.php';
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.nombre,
                u.email,
                u.telefono,
                u.activo,
                u.fecha_registro,
                u.avatar,
                CASE 
                    WHEN a.id IS NOT NULL THEN 'administrador'
                    WHEN c.id IS NOT NULL THEN 'usuario'
                    ELSE NULL
                END as rol,
                c.id as cliente_id,
                c.puntosLealtad,
                c.fecha_ultima_Compra,
                a.id as admin_id,
                a.permisos as admin_permisos
            FROM usuario u
            LEFT JOIN administrador a ON u.id = a.usuarioId AND a.activo = 1
            LEFT JOIN cliente c ON u.id = c.usuarioId
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $usuario ?: null;
        
    } catch (Exception $e) {
        error_log("Error en obtenerUsuarioActual: " . $e->getMessage());
        return null;
    }
}

/**
 * Verifica autenticacion para endpoints API (retorna JSON en vez de redireccionar)
 */
function verificarAuthAPI() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado. Inicia sesion.']);
        exit;
    }
}

/**
 * Verifica que sea admin para endpoints API
 */
function verificarAdminAPI() {
    verificarAuthAPI();
    
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'administrador') {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de administrador.']);
        exit;
    }
}
?>
