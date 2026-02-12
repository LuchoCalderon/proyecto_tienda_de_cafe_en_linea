<?php
/**
 * php/admin_stats_api.php - API de estadísticas para dashboard
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/check_auth.php';

verificarAdminAPI();

try {
    $pdo = getDBConnection();
    getDashboardStats($pdo);
    
} catch (Exception $e) {
    error_log("Error en admin_stats_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getDashboardStats($pdo) {
    $stats = [];
    
    // Total productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM producto");
    $stats['totalProductos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pedidos hoy
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedido WHERE DATE(fechaCreacion) = CURDATE()");
    $stats['pedidosHoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total clientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cliente");
    $stats['totalClientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ventas hoy
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM pedido WHERE DATE(fechaCreacion) = CURDATE() AND estado != 'cancelado'");
    $stats['ventasHoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Últimos 10 pedidos
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.`numeroSeguimiento:` as numeroSeguimiento,
            p.total,
            p.estado,
            p.fechaCreacion,
            u.nombre as clienteNombre
        FROM pedido p
        INNER JOIN cliente c ON p.clienteId = c.id
        INNER JOIN usuario u ON c.usuarioId = u.id
        ORDER BY p.fechaCreacion DESC
        LIMIT 10
    ");
    $stats['ultimosPedidos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos con stock bajo
    $stmt = $pdo->query("
        SELECT id, nombre, imagen, stockDisponible
        FROM producto
        WHERE stockDisponible < 10 AND activo = 1
        ORDER BY stockDisponible ASC
        LIMIT 5
    ");
    $stats['stockBajo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pedidos por estado
    $stmt = $pdo->query("
        SELECT estado, COUNT(*) as total
        FROM pedido
        GROUP BY estado
    ");
    $stats['pedidosPorEstado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 5 productos más vendidos
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.nombre,
            p.imagen,
            SUM(ip.cantidad) as total_vendidos
        FROM itempedido ip
        INNER JOIN producto p ON ip.productoId = p.id
        INNER JOIN pedido ped ON ip.pedidoId = ped.id
        WHERE ped.estado != 'cancelado'
        GROUP BY p.id
        ORDER BY total_vendidos DESC
        LIMIT 5
    ");
    $stats['topProductos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}
?>