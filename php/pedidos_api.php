<?php
/**
 * php/pedidos_api.php - API para gestión de pedidos del usuario
 * 
 * Requiere autenticación
 * 
 * Acciones:
 * - list: Listar pedidos del usuario
 * - get: Obtener detalle de un pedido específico
 * - cancel: Cancelar un pedido (solo si está pendiente)
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
            listarPedidos($pdo, $clienteId);
            break;
        case 'get':
            obtenerDetallePedido($pdo, $clienteId);
            break;
        case 'cancel':
            cancelarPedido($pdo, $clienteId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en pedidos_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

function listarPedidos($pdo, $clienteId) {
    $estado = $_GET['estado'] ?? '';
    $limite = intval($_GET['limite'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    $sql = "
        SELECT 
            p.id,
            p.`numeroSeguimiento:` as numeroSeguimiento,
            p.total,
            p.estado,
            p.fechaCreacion,
            p.fecha_entrega_estimada,
            d.calle,
            d.ciudad,
            d.departamento,
            COUNT(ip.id) as totalItems
        FROM pedido p
        LEFT JOIN direccion d ON p.direccionId = d.id
        LEFT JOIN itempedido ip ON p.id = ip.pedidoId
        WHERE p.clienteId = ?
    ";
    
    $params = [$clienteId];
    
    if (!empty($estado)) {
        $sql .= " AND p.estado = ?";
        $params[] = $estado;
    }
    
    $sql .= " GROUP BY p.id ORDER BY p.fechaCreacion DESC LIMIT $limite OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada pedido, obtener los primeros 3 productos
    foreach ($pedidos as &$pedido) {
        $stmt = $pdo->prepare("
            SELECT ip.*, pr.nombre, pr.imagen
            FROM itempedido ip
            INNER JOIN producto pr ON ip.productoId = pr.id
            WHERE ip.pedidoId = ?
            LIMIT 3
        ");
        $stmt->execute([$pedido['id']]);
        $pedido['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'pedidos' => $pedidos
    ]);
}

function obtenerDetallePedido($pdo, $clienteId) {
    $pedidoId = intval($_GET['id'] ?? 0);
    
    if ($pedidoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
        return;
    }
    
    // Obtener datos del pedido
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            p.`numeroSeguimiento:` as numeroSeguimiento,
            u.nombre as usuarioNombre,
            u.email as usuarioEmail,
            u.telefono as usuarioTelefono,
            d.calle,
            d.apartamento,
            d.ciudad,
            d.departamento,
            d.codigoPostal,
            d.instrucciones,
            mp.tipo as metodoPagoTipo
        FROM pedido p
        INNER JOIN cliente c ON p.clienteId = c.id
        INNER JOIN usuario u ON c.usuarioId = u.id
        LEFT JOIN direccion d ON p.direccionId = d.id
        LEFT JOIN metodo_pago mp ON p.metodoPagoId = mp.id
        WHERE p.id = ? AND p.clienteId = ?
    ");
    $stmt->execute([$pedidoId, $clienteId]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        return;
    }
    
    // Obtener items del pedido
    $stmt = $pdo->prepare("
        SELECT ip.*, pr.nombre, pr.imagen, pr.descripcion
        FROM itempedido ip
        INNER JOIN producto pr ON ip.productoId = pr.id
        WHERE ip.pedidoId = ?
    ");
    $stmt->execute([$pedidoId]);
    $pedido['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular subtotal
    $subtotal = 0;
    foreach ($pedido['items'] as $item) {
        $subtotal += $item['subtotal'];
    }
    $pedido['subtotal'] = $subtotal;
    $pedido['costoEnvio'] = $pedido['total'] - $subtotal;
    
    echo json_encode([
        'success' => true,
        'pedido' => $pedido
    ]);
}

function cancelarPedido($pdo, $clienteId) {
    $pedidoId = intval($_POST['id'] ?? 0);
    
    if ($pedidoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        // Verificar que el pedido pertenece al cliente y está pendiente
        $stmt = $pdo->prepare("SELECT estado FROM pedido WHERE id = ? AND clienteId = ?");
        $stmt->execute([$pedidoId, $clienteId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception('Pedido no encontrado');
        }
        
        if ($pedido['estado'] !== 'pendiente') {
            throw new Exception('Solo se pueden cancelar pedidos pendientes');
        }
        
        // Devolver stock de los productos
        $stmt = $pdo->prepare("SELECT productoId, cantidad FROM itempedido WHERE pedidoId = ?");
        $stmt->execute([$pedidoId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE producto SET stockDisponible = stockDisponible + ? WHERE id = ?");
            $stmt->execute([$item['cantidad'], $item['productoId']]);
        }
        
        // Actualizar estado del pedido
        $stmt = $pdo->prepare("UPDATE pedido SET estado = 'cancelado' WHERE id = ?");
        $stmt->execute([$pedidoId]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido cancelado exitosamente'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>