<?php
/**
 * API para gestión de direcciones
 * Estructura real de la BD: id, clienteId, calle, instrucciones, apartamento, 
 * ciudad, departamento, codigoPostal, esPredeterminada, alias, fechaCreacion, fechaActualizacion
 */

// Evitar cualquier salida antes del JSON
ob_start();

// Desactivar visualización de errores de PHP (solo loguearlos)
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/check_auth.php';

// Limpiar cualquier salida previa
ob_end_clean();
ob_start();

verificarAutenticacion();
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $conn = getDBConnection();
    $usuario = obtenerUsuarioActual();
    
    if (!$usuario) {
        throw new Exception('Usuario no autenticado');
    }
    
    // Obtener clienteId usando PDO
    $stmt = $conn->prepare("SELECT id FROM cliente WHERE usuarioId = ?");
    $stmt->execute([$usuario['id']]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        throw new Exception('Cliente no encontrado');
    }
    
    $clienteId = $cliente['id'];
    
    switch ($action) {
        case 'listar':
            listarDirecciones($conn, $clienteId);
            break;
        case 'obtener':
            obtenerDireccion($conn, $clienteId);
            break;
        case 'crear':
            crearDireccion($conn, $clienteId);
            break;
        case 'actualizar':
            actualizarDireccion($conn, $clienteId);
            break;
        case 'eliminar':
            eliminarDireccion($conn, $clienteId);
            break;
        case 'predeterminada':
            establecerPredeterminada($conn, $clienteId);
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    // Limpiar cualquier salida previa
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
    exit;
}

function listarDirecciones($conn, $clienteId) {
    $stmt = $conn->prepare("
        SELECT * FROM direccion 
        WHERE clienteId = ? 
        ORDER BY esPredeterminada DESC, fechaCreacion DESC
    ");
    $stmt->execute([$clienteId]);
    
    $direcciones = $stmt->fetchAll();
    
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $direcciones]);
    exit;
}

function obtenerDireccion($conn, $clienteId) {
    $id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT * FROM direccion WHERE id = ? AND clienteId = ?");
    $stmt->execute([$id, $clienteId]);
    $direccion = $stmt->fetch();
    
    if (!$direccion) {
        throw new Exception('Dirección no encontrada');
    }
    
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $direccion]);
    exit;
}

function crearDireccion($conn, $clienteId) {
    // Campos requeridos
    $calle = trim($_POST['calle'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $codigoPostal = trim($_POST['codigoPostal'] ?? '');
    
    if (empty($calle) || empty($ciudad) || empty($departamento) || empty($codigoPostal)) {
        throw new Exception('Faltan campos requeridos');
    }
    
    // Campos opcionales
    $apartamento = trim($_POST['apartamento'] ?? '');
    $instrucciones = trim($_POST['instrucciones'] ?? '');
    $alias = trim($_POST['alias'] ?? '');
    $esPredeterminada = isset($_POST['esPredeterminada']) ? 1 : 0;
    
    // Si se marca como predeterminada, desmarcar las demás
    if ($esPredeterminada) {
        $stmt = $conn->prepare("UPDATE direccion SET esPredeterminada = 0 WHERE clienteId = ?");
        $stmt->execute([$clienteId]);
    }
    
    // Insertar nueva dirección
    $stmt = $conn->prepare("
        INSERT INTO direccion 
        (clienteId, calle, instrucciones, apartamento, ciudad, departamento, codigoPostal, esPredeterminada, alias) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $clienteId,
        $calle,
        $instrucciones,
        $apartamento,
        $ciudad,
        $departamento,
        $codigoPostal,
        $esPredeterminada,
        $alias
    ]);
    
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Dirección agregada', 'id' => $conn->lastInsertId()]);
    exit;
}

function actualizarDireccion($conn, $clienteId) {
    $id = $_POST['id'] ?? 0;
    
    // Verificar que la dirección pertenece al cliente
    $stmt = $conn->prepare("SELECT id FROM direccion WHERE id = ? AND clienteId = ?");
    $stmt->execute([$id, $clienteId]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Dirección no encontrada');
    }
    
    // Campos requeridos
    $calle = trim($_POST['calle'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $codigoPostal = trim($_POST['codigoPostal'] ?? '');
    
    if (empty($calle) || empty($ciudad) || empty($departamento) || empty($codigoPostal)) {
        throw new Exception('Faltan campos requeridos');
    }
    
    // Campos opcionales
    $apartamento = trim($_POST['apartamento'] ?? '');
    $instrucciones = trim($_POST['instrucciones'] ?? '');
    $alias = trim($_POST['alias'] ?? '');
    $esPredeterminada = isset($_POST['esPredeterminada']) ? 1 : 0;
    
    // Si se marca como predeterminada, desmarcar las demás
    if ($esPredeterminada) {
        $stmt = $conn->prepare("UPDATE direccion SET esPredeterminada = 0 WHERE clienteId = ? AND id != ?");
        $stmt->execute([$clienteId, $id]);
    }
    
    // Actualizar dirección
    $stmt = $conn->prepare("
        UPDATE direccion SET 
            calle = ?,
            instrucciones = ?,
            apartamento = ?,
            ciudad = ?,
            departamento = ?,
            codigoPostal = ?,
            esPredeterminada = ?,
            alias = ?
        WHERE id = ? AND clienteId = ?
    ");
    
    $stmt->execute([
        $calle,
        $instrucciones,
        $apartamento,
        $ciudad,
        $departamento,
        $codigoPostal,
        $esPredeterminada,
        $alias,
        $id,
        $clienteId
    ]);
    
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Dirección actualizada']);
    exit;
}

function eliminarDireccion($conn, $clienteId) {
    $id = $_POST['id'] ?? 0;
    
    // Verificar si es predeterminada
    $stmt = $conn->prepare("SELECT esPredeterminada FROM direccion WHERE id = ? AND clienteId = ?");
    $stmt->execute([$id, $clienteId]);
    $direccion = $stmt->fetch();
    
    if (!$direccion) {
        throw new Exception('Dirección no encontrada');
    }
    
    $esPredeterminada = $direccion['esPredeterminada'];
    
    // Eliminar dirección
    $stmt = $conn->prepare("DELETE FROM direccion WHERE id = ? AND clienteId = ?");
    $stmt->execute([$id, $clienteId]);
    
    // Si era predeterminada, establecer otra como predeterminada
    if ($esPredeterminada == 1) {
        $stmt = $conn->prepare("
            UPDATE direccion 
            SET esPredeterminada = 1 
            WHERE clienteId = ? 
            ORDER BY fechaCreacion DESC 
            LIMIT 1
        ");
        $stmt->execute([$clienteId]);
    }
    
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Dirección eliminada']);
    exit;
}

function establecerPredeterminada($conn, $clienteId) {
    $id = $_POST['id'] ?? 0;
    
    // Verificar que la dirección existe
    $stmt = $conn->prepare("SELECT id FROM direccion WHERE id = ? AND clienteId = ?");
    $stmt->execute([$id, $clienteId]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Dirección no encontrada');
    }
    
    // Desmarcar todas las direcciones
    $stmt = $conn->prepare("UPDATE direccion SET esPredeterminada = 0 WHERE clienteId = ?");
    $stmt->execute([$clienteId]);
    
    // Marcar la seleccionada como predeterminada
    $stmt = $conn->prepare("UPDATE direccion SET esPredeterminada = 1 WHERE id = ? AND clienteId = ?");
    $stmt->execute([$id, $clienteId]);
    
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Dirección predeterminada actualizada']);
    exit;
}
