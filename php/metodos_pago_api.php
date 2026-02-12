<?php
/**
 * API para gestión de métodos de pago
 * Compatible con config/db_config.php (PDO)
 */

session_start();
require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    $pdo = getDBConnection();
    $usuarioId = $_SESSION['usuario_id'];
    
    // Obtener clienteId
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE usuarioId = ?");
    $stmt->execute([$usuarioId]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit;
    }
    
    $clienteId = $cliente['id'];
    
    switch ($action) {
        case 'list':
            listarMetodos($pdo, $clienteId);
            break;
        case 'add':
            agregarMetodo($pdo, $clienteId);
            break;
        case 'delete':
            eliminarMetodo($pdo, $clienteId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en metodos_pago_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listarMetodos($pdo, $clienteId) {
    $stmt = $pdo->prepare("
        SELECT id, tipo, detalles
        FROM metodo_pago
        WHERE clienteId = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$clienteId]);
    $metodos = $stmt->fetchAll();
    
    // Decodificar JSON de detalles
    foreach ($metodos as &$metodo) {
        $metodo['detalles'] = json_decode($metodo['detalles'], true);
    }
    
    echo json_encode([
        'success' => true,
        'metodos' => $metodos
    ]);
}

function agregarMetodo($pdo, $clienteId) {
    $tipo = trim($_POST['tipo'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $vencimiento = trim($_POST['vencimiento'] ?? '');
    
    if (empty($tipo)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de método de pago requerido']);
        return;
    }
    
    // Construir detalles según el tipo
    $detalles = [
        'tipo' => $tipo,
        'fechaAgregado' => date('Y-m-d H:i:s')
    ];
    
    if ($tipo === 'tarjeta') {
        if (empty($numero) || empty($nombre) || empty($vencimiento)) {
            echo json_encode(['success' => false, 'message' => 'Datos de tarjeta incompletos']);
            return;
        }
        
        // Enmascarar número de tarjeta (guardar solo últimos 4 dígitos)
        $ultimos4 = substr(preg_replace('/\s+/', '', $numero), -4);
        $detalles['numero'] = '****' . $ultimos4;
        $detalles['nombre'] = strtoupper($nombre);
        $detalles['vencimiento'] = $vencimiento;
    } elseif ($tipo === 'contraentrega') {
        $detalles['nombre'] = 'Pago Contraentrega';
    } elseif ($tipo === 'transferencia') {
        $detalles['nombre'] = 'Transferencia Bancaria';
    }
    
    $detallesJson = json_encode($detalles);
    
    $stmt = $pdo->prepare("INSERT INTO metodo_pago (clienteId, tipo, detalles) VALUES (?, ?, ?)");
    $stmt->execute([$clienteId, $tipo, $detallesJson]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Método de pago agregado exitosamente',
        'id' => $pdo->lastInsertId()
    ]);
}

function eliminarMetodo($pdo, $clienteId) {
    $metodoId = intval($_POST['id'] ?? 0);
    
    if ($metodoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    // Verificar que el método pertenece al cliente
    $stmt = $pdo->prepare("DELETE FROM metodo_pago WHERE id = ? AND clienteId = ?");
    $stmt->execute([$metodoId, $clienteId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Método de pago eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método no encontrado']);
    }
}