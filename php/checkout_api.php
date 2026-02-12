<?php
/**
 * php/checkout_api.php - API para proceso de checkout y creación de pedidos
 * 
 * Requiere autenticación
 * 
 * Acciones:
 * - get_addresses: Obtener direcciones del cliente
 * - get_payment_methods: Obtener métodos de pago del cliente
 * - validate_cart: Validar que el carrito tenga stock
 * - create_order: Crear el pedido
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
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
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
        case 'get_addresses':
            obtenerDirecciones($pdo, $clienteId);
            break;
        case 'get_payment_methods':
            obtenerMetodosPago($pdo, $clienteId);
            break;
        case 'validate_cart':
            validarCarrito($pdo, $clienteId);
            break;
        case 'create_order':
            crearPedido($pdo, $clienteId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en checkout_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

// =============================================
// FUNCIONES
// =============================================

function obtenerDirecciones($pdo, $clienteId) {
    $stmt = $pdo->prepare("
        SELECT id, alias, calle, apartamento, ciudad, departamento, codigoPostal, 
               instrucciones, esPredeterminada
        FROM direccion 
        WHERE clienteId = ? 
        ORDER BY esPredeterminada DESC, id DESC
    ");
    $stmt->execute([$clienteId]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'direcciones' => $direcciones
    ]);
}

function obtenerMetodosPago($pdo, $clienteId) {
    $stmt = $pdo->prepare("
        SELECT id, tipo, detalles 
        FROM metodo_pago 
        WHERE clienteId = ?
    ");
    $stmt->execute([$clienteId]);
    $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decodificar JSON de detalles
    foreach ($metodos as &$metodo) {
        $metodo['detalles'] = json_decode($metodo['detalles'], true);
    }
    
    echo json_encode([
        'success' => true,
        'metodos' => $metodos
    ]);
}

function validarCarrito($pdo, $clienteId) {
    // Obtener carrito activo
    $stmt = $pdo->prepare("SELECT id FROM carrito_compra WHERE clienteId = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$clienteId]);
    $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$carrito) {
        echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
        return;
    }
    
    // Validar stock de cada producto
    $stmt = $pdo->prepare("
        SELECT ic.id, ic.productoId, ic.cantidad, p.nombre, p.stockDisponible
        FROM itemcarrito ic
        INNER JOIN producto p ON ic.productoId = p.id
        WHERE ic.carritoId = ?
    ");
    $stmt->execute([$carrito['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $errores = [];
    foreach ($items as $item) {
        if ($item['stockDisponible'] < $item['cantidad']) {
            $errores[] = [
                'producto' => $item['nombre'],
                'solicitado' => $item['cantidad'],
                'disponible' => $item['stockDisponible']
            ];
        }
    }
    
    if (count($errores) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuficiente para algunos productos',
            'errores' => $errores
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Carrito validado correctamente'
        ]);
    }
}

function crearPedido($pdo, $clienteId) {
    $direccionId = intval($_POST['direccionId'] ?? 0);
    $metodoPagoTipo = trim($_POST['metodoPago'] ?? '');
    $notas = trim($_POST['notas'] ?? '');
    
    if ($direccionId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Selecciona una dirección de envío']);
        return;
    }
    
    if (empty($metodoPagoTipo)) {
        echo json_encode(['success' => false, 'message' => 'Selecciona un método de pago']);
        return;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // Obtener carrito activo
        $stmt = $pdo->prepare("SELECT id FROM carrito_compra WHERE clienteId = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$clienteId]);
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$carrito) {
            throw new Exception('Carrito vacío');
        }
        
        $carritoId = $carrito['id'];
        
        // Obtener items del carrito
        $stmt = $pdo->prepare("
            SELECT ic.productoId, ic.cantidad, ic.precioUnitario, p.stockDisponible
            FROM itemcarrito ic
            INNER JOIN producto p ON ic.productoId = p.id
            WHERE ic.carritoId = ?
        ");
        $stmt->execute([$carritoId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($items) == 0) {
            throw new Exception('Carrito vacío');
        }
        
        // Validar stock nuevamente
        foreach ($items as $item) {
            if ($item['stockDisponible'] < $item['cantidad']) {
                throw new Exception('Stock insuficiente para: ' . $item['productoId']);
            }
        }
        
        // Calcular total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['cantidad'] * $item['precioUnitario'];
        }
        
        // Agregar costo de envío si es necesario
        $costoEnvio = $total >= 50000 ? 0 : 5000;
        $total += $costoEnvio;
        
        // Crear o obtener método de pago
        $stmt = $pdo->prepare("SELECT id FROM metodo_pago WHERE clienteId = ? AND tipo = ? LIMIT 1");
        $stmt->execute([$clienteId, $metodoPagoTipo]);
        $metodoPago = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$metodoPago) {
            // Crear método de pago temporal
            $detalles = json_encode(['tipo' => $metodoPagoTipo, 'temporal' => true]);
            $stmt = $pdo->prepare("INSERT INTO metodo_pago (clienteId, tipo, detalles) VALUES (?, ?, ?)");
            $stmt->execute([$clienteId, $metodoPagoTipo, $detalles]);
            $metodoPagoId = $pdo->lastInsertId();
        } else {
            $metodoPagoId = $metodoPago['id'];
        }
        
        // Generar número de seguimiento
        $numeroSeguimiento = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Fecha estimada de entrega (5 días hábiles)
        $fechaEntregaEstimada = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        // Crear pedido
        $stmt = $pdo->prepare("
            INSERT INTO pedido (clienteId, metodoPagoId, direccionId, total, estado, fechaCreacion, `numeroSeguimiento:`, fecha_entrega_estimada)
            VALUES (?, ?, ?, ?, 'pendiente', NOW(), ?, ?)
        ");
        $stmt->execute([$clienteId, $metodoPagoId, $direccionId, $total, $numeroSeguimiento, $fechaEntregaEstimada]);
        $pedidoId = $pdo->lastInsertId();
        
        // Crear items del pedido y actualizar stock
        foreach ($items as $item) {
            $subtotal = $item['cantidad'] * $item['precioUnitario'];
            
            // Insertar item del pedido
            $stmt = $pdo->prepare("
                INSERT INTO itempedido (pedidoId, productoId, cantidad, precioUnitario, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $pedidoId,
                $item['productoId'],
                $item['cantidad'],
                $item['precioUnitario'],
                $subtotal
            ]);
            
            // Reducir stock
            $stmt = $pdo->prepare("UPDATE producto SET stockDisponible = stockDisponible - ? WHERE id = ?");
            $stmt->execute([$item['cantidad'], $item['productoId']]);
        }
        
        // Vaciar carrito (eliminar items)
        $stmt = $pdo->prepare("DELETE FROM itemcarrito WHERE carritoId = ?");
        $stmt->execute([$carritoId]);
        
        // Desactivar carrito
        $stmt = $pdo->prepare("UPDATE carrito_compra SET activo = 0 WHERE id = ?");
        $stmt->execute([$carritoId]);
        
        // Commit de la transacción
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido creado exitosamente',
            'pedidoId' => $pedidoId,
            'numeroSeguimiento' => $numeroSeguimiento,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>