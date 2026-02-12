<?php
// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores directamente, los manejamos nosotros

header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Intentar incluir la configuración de BD
try {
    require_once __DIR__ . '/../config/db_config.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cargar configuración: ' . $e->getMessage()]);
    exit;
}

try {
    // Obtener datos del formulario
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $recordar = isset($_POST['rememberMe']);
    
    // Validaciones básicas
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, ingresa un correo electrónico válido']);
        exit;
    }
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, ingresa tu contraseña']);
        exit;
    }
    
    // Conectar a la base de datos
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.nombre, 
            u.email, 
            u.contraseña, 
            u.telefono,
            u.activo,
            u.fecha_registro,
            CASE 
                WHEN a.id IS NOT NULL THEN 'administrador'
                WHEN c.id IS NOT NULL THEN 'usuario'
                ELSE NULL
            END as rol,
            a.id as admin_id,
            c.id as cliente_id,
            c.puntosLealtad
        FROM usuario u
        LEFT JOIN administrador a ON u.id = a.usuarioId AND a.activo = 1
        LEFT JOIN cliente c ON u.id = c.usuarioId
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    // Verificar si el usuario existe y la contraseña es correcta
    if (!$usuario || !password_verify($password, $usuario['contraseña'])) {
        echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos']);
        exit;
    }
    
    // Verificar si el usuario está activo
    if (!$usuario['activo']) {
        echo json_encode(['success' => false, 'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.']);
        exit;
    }
    
    // Verificar si tiene rol asignado
    if (empty($usuario['rol'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario sin rol asignado. Contacta al administrador.']);
        exit;
    }
    
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_telefono'] = $usuario['telefono'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    $_SESSION['fecha_registro'] = $usuario['fecha_registro'];
    $_SESSION['login_time'] = time();
    
    // Si es cliente, guardar puntos de lealtad
    if ($usuario['rol'] === 'usuario') {
        $_SESSION['cliente_id'] = $usuario['cliente_id'];
        $_SESSION['puntos_lealtad'] = $usuario['puntosLealtad'];
    }
    
    // Si es administrador, guardar admin_id
    if ($usuario['rol'] === 'administrador') {
        $_SESSION['admin_id'] = $usuario['admin_id'];
    }
    
    // Si seleccionó "recordarme", extender la sesión
    if ($recordar) {
        // Extender la sesión a 30 días
        ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
        session_set_cookie_params(30 * 24 * 60 * 60);
    }
    
    // Usar rutas relativas desde la raíz del proyecto (sin ../)
    $redirect = ($usuario['rol'] === 'administrador') ? 'administrador.php' : 'perfilUsuario.php';
    
    echo json_encode([
        'success' => true,
        'message' => '¡Bienvenido ' . $usuario['nombre'] . '!',
        'redirect' => $redirect,
        'rol' => $usuario['rol']
    ]);
    
} catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al iniciar sesión. Por favor, intenta de nuevo.']);
} catch (Exception $e) {
    error_log("Error general en login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error inesperado. Por favor, intenta de nuevo.']);
}
?>
