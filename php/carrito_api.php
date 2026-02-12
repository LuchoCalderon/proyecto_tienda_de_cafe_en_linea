<?php
/**
 * php/carrito_api.php - API para manejo del carrito de compras
 * 
 * Requiere autenticación
 * 
 * Acciones:
 * - add: Agregar producto al carrito
 * - update: Actualizar cantidad
 * - remove: Eliminar producto
 * - list: Listar productos del carrito
 * - count: Obtener cantidad de items
 * - clear: Vaciar carrito
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para usar el carrito']);
    exit;
}

try {
    $pdo = getDBConnection();
    $usuarioId = $_SESSION['usuario_id'];
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    
    // Obtener o crear carrito para el cliente
    $clienteId = obtenerClienteId($pdo, $usuarioId);
    if (!$clienteId) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener datos del cliente']);
        exit;
    }
    
    $carritoId = obtenerOCrearCarrito($pdo, $clienteId);
    
    switch ($action) {
        case 'add':
            agregarProducto($pdo, $carritoId);
            break;
        case 'update':
            actualizarCantidad($pdo, $carritoId);
            break;
        case 'remove':
            eliminarProducto($pdo, $carritoId);
            break;
        case 'list':
            listarCarrito($pdo, $carritoId);
            break;
        case 'count':
            contarItems($pdo, $carritoId);
            break;
        case 'clear':
            vaciarCarrito($pdo, $carritoId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en carrito_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

// =============================================
// FUNCIONES AUXILIARES
// =============================================

function obtenerClienteId($pdo, $usuarioId) {
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE usuarioId = ?");
    $stmt->execute([$usuarioId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

function obtenerOCrearCarrito($pdo, $clienteId) {
    // Buscar carrito activo
    $stmt = $pdo->prepare("SELECT id FROM carrito_compra WHERE clienteId = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$clienteId]);
    $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($carrito) {
        return $carrito['id'];
    }
    
    // Crear nuevo carrito
    $stmt = $pdo->prepare("INSERT INTO carrito_compra (clienteId, activo) VALUES (?, 1)");
    $stmt->execute([$clienteId]);
    return $pdo->lastInsertId();
}

function agregarProducto($pdo, $carritoId) {
    $productoId = intval($_POST['productoId'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 1);
    
    if ($productoId <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }
    
    // Verificar stock disponible
    $stmt = $pdo->prepare("SELECT nombre, precio, stockDisponible FROM producto WHERE id = ? AND activo = 1");
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        return;
    }
    
    if ($producto['stockDisponible'] < $cantidad) {
        echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Disponible: ' . $producto['stockDisponible']]);
        return;
    }
    
    // Verificar si el producto ya está en el carrito
    $stmt = $pdo->prepare("SELECT id, cantidad FROM itemcarrito WHERE carritoId = ? AND productoId = ?");
    $stmt->execute([$carritoId, $productoId]);
    $itemExistente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($itemExistente) {
        // Actualizar cantidad
        $nuevaCantidad = $itemExistente['cantidad'] + $cantidad;
        
        if ($producto['stockDisponible'] < $nuevaCantidad) {
            echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Disponible: ' . $producto['stockDisponible']]);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE itemcarrito SET cantidad = ? WHERE id = ?");
        $stmt->execute([$nuevaCantidad, $itemExistente['id']]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cantidad actualizada en el carrito',
            'cantidad' => $nuevaCantidad
        ]);
    } else {
        // Agregar nuevo item
        $stmt = $pdo->prepare("INSERT INTO itemcarrito (carritoId, productoId, cantidad, precioUnitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$carritoId, $productoId, $cantidad, $producto['precio']]);
        
        echo json_encode([
            'success' => true, 
            'message' => $producto['nombre'] . ' agregado al carrito',
            'itemId' => $pdo->lastInsertId()
        ]);
    }
}

function actualizarCantidad($pdo, $carritoId) {
    $itemId = intval($_POST['itemId'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 1);
    
    if ($itemId <= 0 || $cantidad < 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }
    
    // Si la cantidad es 0, eliminar el item
    if ($cantidad == 0) {
        $stmt = $pdo->prepare("DELETE FROM itemcarrito WHERE id = ? AND carritoId = ?");
        $stmt->execute([$itemId, $carritoId]);
        echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
        return;
    }
    
    // Verificar stock
    $stmt = $pdo->prepare("
        SELECT ic.productoId, p.stockDisponible, p.nombre 
        FROM itemcarrito ic 
        INNER JOIN producto p ON ic.productoId = p.id 
        WHERE ic.id = ? AND ic.carritoId = ?
    ");
    $stmt->execute([$itemId, $carritoId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
        return;
    }
    
    if ($item['stockDisponible'] < $cantidad) {
        echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Disponible: ' . $item['stockDisponible']]);
        return;
    }
    
    // Actualizar cantidad
    $stmt = $pdo->prepare("UPDATE itemcarrito SET cantidad = ? WHERE id = ? AND carritoId = ?");
    $stmt->execute([$cantidad, $itemId, $carritoId]);
    
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
}

function eliminarProducto($pdo, $carritoId) {
    $itemId = intval($_POST['itemId'] ?? $_GET['itemId'] ?? 0);
    
    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de item inválido']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM itemcarrito WHERE id = ? AND carritoId = ?");
    $stmt->execute([$itemId, $carritoId]);
    
    echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
}

function listarCarrito($pdo, $carritoId) {
    $stmt = $pdo->prepare("
        SELECT 
            ic.id,
            ic.productoId,
            ic.cantidad,
            ic.precioUnitario,
            p.nombre,
            p.descripcion,
            p.imagen,
            p.stockDisponible,
            (ic.cantidad * ic.precioUnitario) as subtotal
        FROM itemcarrito ic
        INNER JOIN producto p ON ic.productoId = p.id
        WHERE ic.carritoId = ? AND p.activo = 1
        ORDER BY ic.id DESC
    ");
    $stmt->execute([$carritoId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = 0;
    foreach ($items as &$item) {
        $item['precioUnitario'] = floatval($item['precioUnitario']);
        $item['subtotal'] = floatval($item['subtotal']);
        $total += $item['subtotal'];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'total' => $total,
        'count' => count($items)
    ]);
}

function contarItems($pdo, $carritoId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM itemcarrito WHERE carritoId = ?");
    $stmt->execute([$carritoId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => intval($result['count'])
    ]);
}

function vaciarCarrito($pdo, $carritoId) {
    $stmt = $pdo->prepare("DELETE FROM itemcarrito WHERE carritoId = ?");
    $stmt->execute([$carritoId]);
    
    echo json_encode(['success' => true, 'message' => 'Carrito vaciado']);
}
?>