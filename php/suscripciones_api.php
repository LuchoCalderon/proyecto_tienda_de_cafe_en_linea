<?php
/**
 * php/suscripciones_api.php - API para gestión de suscripciones
 * CORREGIDA - Usa PDO en lugar de mysqli
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

try {
    $pdo = getDBConnection();
    $usuarioId = $_SESSION['usuario_id'];
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    
    // Obtener cliente ID
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE usuarioId = ?");
    $stmt->execute([$usuarioId]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit;
    }
    
    $clienteId = $cliente['id'];
    
    switch ($action) {
        case 'list':
            listarSuscripciones($pdo, $clienteId);
            break;
        case 'create':
            crearSuscripcion($pdo, $clienteId);
            break;
        case 'cancel':
            cancelarSuscripcion($pdo, $clienteId);
            break;
        case 'get_products':
            obtenerProductosSuscripcion($pdo, $clienteId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en suscripciones_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

function listarSuscripciones($pdo, $clienteId) {
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.frecuencia,
            s.fechaInicio,
            s.fechaProximoEnvio,
            s.activa,
            s.precioTotal,
            COUNT(sp.id) as totalProductos
        FROM suscripcion s
        LEFT JOIN suscripcion_producto sp ON s.id = sp.suscripcionId
        WHERE s.clienteId = ?
        GROUP BY s.id
        ORDER BY s.activa DESC, s.fechaInicio DESC
    ");
    $stmt->execute([$clienteId]);
    $suscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada suscripción, obtener los productos
    foreach ($suscripciones as &$sub) {
        $stmt = $pdo->prepare("
            SELECT sp.*, p.nombre, p.imagen, p.precio
            FROM suscripcion_producto sp
            INNER JOIN producto p ON sp.productoId = p.id
            WHERE sp.suscripcionId = ?
        ");
        $stmt->execute([$sub['id']]);
        $sub['productos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'suscripciones' => $suscripciones
    ]);
}

function crearSuscripcion($pdo, $clienteId) {
    $frecuencia = trim($_POST['frecuencia'] ?? 'mensual');
    $productos = json_decode($_POST['productos'] ?? '[]', true);
    
    if (empty($productos)) {
        echo json_encode(['success' => false, 'message' => 'Debes seleccionar al menos un producto']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        // Calcular total
        $total = 0;
        foreach ($productos as $prod) {
            $stmt = $pdo->prepare("SELECT precio FROM producto WHERE id = ?");
            $stmt->execute([$prod['productoId']]);
            $precio = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($precio) {
                $total += $precio['precio'] * $prod['cantidad'];
            }
        }
        
        // Aplicar descuento del 10%
        $total = $total * 0.9;
        
        // Calcular próximo envío según frecuencia
        $diasProximoEnvio = $frecuencia === 'semanal' ? 7 : ($frecuencia === 'quincenal' ? 15 : 30);
        $fechaProximoEnvio = date('Y-m-d', strtotime("+$diasProximoEnvio days"));
        
        // Crear suscripción
        $stmt = $pdo->prepare("
            INSERT INTO suscripcion (clienteId, frecuencia, fechaInicio, fechaProximoEnvio, activa, precioTotal)
            VALUES (?, ?, NOW(), ?, 1, ?)
        ");
        $stmt->execute([$clienteId, $frecuencia, $fechaProximoEnvio, $total]);
        $suscripcionId = $pdo->lastInsertId();
        
        // Agregar productos a la suscripción
        foreach ($productos as $prod) {
            $stmt = $pdo->prepare("
                INSERT INTO suscripcion_producto (suscripcionId, productoId, cantidad)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$suscripcionId, $prod['productoId'], $prod['cantidad']]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Suscripción creada exitosamente',
            'suscripcionId' => $suscripcionId,
            'fechaProximoEnvio' => $fechaProximoEnvio
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function cancelarSuscripcion($pdo, $clienteId) {
    $suscripcionId = intval($_POST['id'] ?? 0);
    
    if ($suscripcionId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    // Verificar que la suscripción pertenece al cliente
    $stmt = $pdo->prepare("UPDATE suscripcion SET activa = 0 WHERE id = ? AND clienteId = ?");
    $stmt->execute([$suscripcionId, $clienteId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Suscripción cancelada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Suscripción no encontrada']);
    }
}

function obtenerProductosSuscripcion($pdo, $clienteId) {
    $suscripcionId = intval($_GET['id'] ?? 0);
    
    if ($suscripcionId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    // Verificar que la suscripción pertenece al cliente
    $stmt = $pdo->prepare("SELECT id FROM suscripcion WHERE id = ? AND clienteId = ?");
    $stmt->execute([$suscripcionId, $clienteId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Suscripción no encontrada']);
        return;
    }
    
    // Obtener productos
    $stmt = $pdo->prepare("
        SELECT sp.*, p.nombre, p.imagen, p.precio
        FROM suscripcion_producto sp
        INNER JOIN producto p ON sp.productoId = p.id
        WHERE sp.suscripcionId = ?
    ");
    $stmt->execute([$suscripcionId]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);
}
?>