<?php
/**
 * php/notificaciones_api.php - API para gestión de notificaciones
 * 
 * Requiere autenticación de administrador
 * 
 * Acciones:
 * - list: Listar notificaciones
 * - count: Contar no leídas
 * - marcar_leida: Marcar como leída
 * - marcar_todas_leidas: Marcar todas como leídas
 * - eliminar: Eliminar notificación
 * - crear: Crear notificación manual
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/check_auth.php';

// Verificar que sea admin
verificarAdminAPI();

try {
    $pdo = getDBConnection();
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            listarNotificaciones($pdo);
            break;
        case 'count':
            contarNoLeidas($pdo);
            break;
        case 'marcar_leida':
            marcarLeida($pdo);
            break;
        case 'marcar_todas_leidas':
            marcarTodasLeidas($pdo);
            break;
        case 'eliminar':
            eliminarNotificacion($pdo);
            break;
        case 'crear':
            crearNotificacion($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en notificaciones_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function listarNotificaciones($pdo) {
    $limite = intval($_GET['limite'] ?? 50);
    $soloNoLeidas = isset($_GET['no_leidas']) && $_GET['no_leidas'] === '1';
    
    $sql = "SELECT * FROM notificacion";
    if ($soloNoLeidas) {
        $sql .= " WHERE leida = 0";
    }
    $sql .= " ORDER BY fechaCreacion DESC LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limite]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decodificar metadatos JSON
    foreach ($notificaciones as &$notif) {
        if ($notif['metadatos']) {
            $notif['metadatos'] = json_decode($notif['metadatos'], true);
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $notificaciones
    ]);
}

function contarNoLeidas($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM notificacion WHERE leida = 0");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Contar también por tipo
    $stmt = $pdo->query("
        SELECT tipo, COUNT(*) as total 
        FROM notificacion 
        WHERE leida = 0 
        GROUP BY tipo
    ");
    $porTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total' => intval($result['total']),
        'porTipo' => $porTipo
    ]);
}

function marcarLeida($pdo) {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE notificacion SET leida = 1, fechaLeida = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Notificación no encontrada']);
    }
}

function marcarTodasLeidas($pdo) {
    $stmt = $pdo->query("UPDATE notificacion SET leida = 1, fechaLeida = NOW() WHERE leida = 0");
    $cantidad = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Se marcaron $cantidad notificaciones como leídas",
        'cantidad' => $cantidad
    ]);
}

function eliminarNotificacion($pdo) {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM notificacion WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Notificación eliminada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Notificación no encontrada']);
    }
}

function crearNotificacion($pdo) {
    $tipo = trim($_POST['tipo'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $icono = trim($_POST['icono'] ?? 'bi-info-circle');
    $color = trim($_POST['color'] ?? 'primary');
    $enlace = trim($_POST['enlace'] ?? '');
    
    if (empty($tipo) || empty($titulo) || empty($mensaje)) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO notificacion (tipo, titulo, mensaje, icono, color, enlace)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$tipo, $titulo, $mensaje, $icono, $color, $enlace]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Notificación creada',
        'id' => $pdo->lastInsertId()
    ]);
}

// =============================================
// FUNCIONES AUXILIARES PARA GENERAR NOTIFICACIONES
// =============================================

/**
 * Genera notificaciones automáticas según eventos del sistema
 * Esta función puede ser llamada desde otros scripts
 */
function generarNotificacionAutomatica($pdo, $tipo, $datos) {
    $notificaciones = [
        'pedido_nuevo' => [
            'titulo' => 'Nuevo pedido recibido',
            'mensaje' => "Pedido #{$datos['numero']} por $" . number_format($datos['total'], 0) . " - Cliente: {$datos['cliente']}",
            'icono' => 'bi-bag-check',
            'color' => 'success',
            'enlace' => "admin_orders.php?id={$datos['id']}"
        ],
        'stock_bajo' => [
            'titulo' => 'Stock bajo',
            'mensaje' => "{$datos['producto']} tiene solo {$datos['cantidad']} unidades disponibles",
            'icono' => 'bi-exclamation-triangle',
            'color' => 'warning',
            'enlace' => 'admin_products.php'
        ],
        'producto_agotado' => [
            'titulo' => 'Producto agotado',
            'mensaje' => "{$datos['producto']} está agotado",
            'icono' => 'bi-exclamation-circle',
            'color' => 'danger',
            'enlace' => 'admin_products.php'
        ],
        'cliente_nuevo' => [
            'titulo' => 'Nuevo cliente registrado',
            'mensaje' => "Se registró un nuevo cliente: {$datos['nombre']}",
            'icono' => 'bi-person-plus',
            'color' => 'info',
            'enlace' => 'admin_users.php'
        ],
        'suscripcion_nueva' => [
            'titulo' => 'Nueva suscripción',
            'mensaje' => "{$datos['cliente']} se suscribió al plan {$datos['plan']}",
            'icono' => 'bi-calendar-check',
            'color' => 'success',
            'enlace' => 'admin_orders.php'
        ],
        'pedido_cancelado' => [
            'titulo' => 'Pedido cancelado',
            'mensaje' => "Pedido #{$datos['numero']} fue cancelado",
            'icono' => 'bi-x-circle',
            'color' => 'danger',
            'enlace' => "admin_orders.php?id={$datos['id']}"
        ]
    ];
    
    if (!isset($notificaciones[$tipo])) {
        return false;
    }
    
    $notif = $notificaciones[$tipo];
    
    $stmt = $pdo->prepare("
        INSERT INTO notificacion (tipo, titulo, mensaje, icono, color, enlace, metadatos)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $tipoBase = explode('_', $tipo)[0]; // 'pedido_nuevo' -> 'pedido'
    $metadatos = json_encode($datos);
    
    return $stmt->execute([
        $tipoBase,
        $notif['titulo'],
        $notif['mensaje'],
        $notif['icono'],
        $notif['color'],
        $notif['enlace'],
        $metadatos
    ]);
}