<?php
/**
 * php/admin_api.php - API para el panel de administracion
 * 
 * Parametros: GET/POST resource=productos|usuarios|pedidos|categorias|stats
 *             GET/POST action=list|get|create|update|delete|toggle
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/check_auth.php';

// Verificar que sea admin
verificarAdminAPI();

try {
    $pdo = getDBConnection();
    $resource = $_GET['resource'] ?? $_POST['resource'] ?? '';
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    
    switch ($resource) {
        case 'productos':
            handleProductos($pdo, $action);
            break;
        case 'categorias':
            handleCategorias($pdo, $action);
            break;
        case 'usuarios':
            handleUsuarios($pdo, $action);
            break;
        case 'pedidos':
            handlePedidos($pdo, $action);
            break;
        case 'stats':
            handleStats($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Recurso no valido']);
    }
    
} catch (Exception $e) {
    error_log("Error en admin_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

// =============================================
// PRODUCTOS
// =============================================
function handleProductos($pdo, $action) {
    switch ($action) {
        case 'list':
            $busqueda = $_GET['busqueda'] ?? '';
            $categoriaId = $_GET['categoriaId'] ?? '';
            $limite = intval($_GET['limite'] ?? 50);
            $offset = intval($_GET['offset'] ?? 0);
            
            $sql = "SELECT p.*, c.nombre as categoriaNombre FROM producto p LEFT JOIN categoria c ON p.categoriaId = c.id WHERE 1=1";
            $params = [];
            
            if (!empty($busqueda)) {
                $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
            }
            if (!empty($categoriaId)) {
                $sql .= " AND p.categoriaId = ?";
                $params[] = $categoriaId;
            }
            $sql .= " ORDER BY p.id DESC LIMIT $limite OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total count
            $sqlCount = "SELECT COUNT(*) as total FROM producto WHERE 1=1";
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute();
            $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode(['success' => true, 'data' => $productos, 'total' => $total]);
            break;
            
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT p.*, c.nombre as categoriaNombre FROM producto p LEFT JOIN categoria c ON p.categoriaId = c.id WHERE p.id = ?");
            $stmt->execute([$id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($producto) {
                echo json_encode(['success' => true, 'data' => $producto]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
            break;
            
        case 'create':
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $categoriaId = intval($_POST['categoriaId'] ?? 0);
            $stock = intval($_POST['stockDisponible'] ?? 0);
            $destacado = intval($_POST['destacado'] ?? 0);
            $activo = intval($_POST['activo'] ?? 1);
            $imagen = '';
            
            if (empty($nombre) || $precio <= 0) {
                echo json_encode(['success' => false, 'message' => 'Nombre y precio son obligatorios']);
                return;
            }
            
            // Manejar imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = uploadProductImage($_FILES['imagen']);
            } else {
                $imagen = trim($_POST['imagen'] ?? '');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO producto (categoriaId, nombre, descripcion, precio, imagen, stockDisponible, destacado, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$categoriaId, $nombre, $descripcion, $precio, $imagen, $stock, $destacado, $activo]);
            
            echo json_encode(['success' => true, 'message' => 'Producto creado', 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $categoriaId = intval($_POST['categoriaId'] ?? 0);
            $stock = intval($_POST['stockDisponible'] ?? 0);
            $destacado = intval($_POST['destacado'] ?? 0);
            $activo = intval($_POST['activo'] ?? 1);
            
            if ($id <= 0 || empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
                return;
            }
            
            // Manejar imagen
            $imagenSQL = "";
            $params = [$categoriaId, $nombre, $descripcion, $precio, $stock, $destacado, $activo];
            
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = uploadProductImage($_FILES['imagen']);
                $imagenSQL = ", imagen = ?";
                $params[] = $imagen;
            }
            
            $params[] = $id;
            
            $stmt = $pdo->prepare("
                UPDATE producto SET categoriaId = ?, nombre = ?, descripcion = ?, precio = ?, stockDisponible = ?, destacado = ?, activo = ? $imagenSQL WHERE id = ?
            ");
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Producto actualizado']);
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            // Soft delete
            $stmt = $pdo->prepare("UPDATE producto SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Producto desactivado']);
            break;
            
        case 'toggle':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE producto SET activo = NOT activo WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
            break;
    }
}

function uploadProductImage($file) {
    $uploadDir = __DIR__ . '/../images/productos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'producto_' . time() . '_' . rand(100, 999) . '.' . $extension;
    $targetPath = $uploadDir . $fileName;
    
    move_uploaded_file($file['tmp_name'], $targetPath);
    return 'images/productos/' . $fileName;
}

// =============================================
// CATEGORIAS
// =============================================
function handleCategorias($pdo, $action) {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM producto WHERE categoriaId = c.id) as totalProductos FROM categoria c ORDER BY c.nombre");
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $categorias]);
            break;
            
        case 'create':
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $imagen = trim($_POST['imagen'] ?? '');
            
            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
                return;
            }
            
            $stmt = $pdo->prepare("INSERT INTO categoria (nombre, descripcion, imagen) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $imagen]);
            echo json_encode(['success' => true, 'message' => 'Categoria creada', 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            
            $stmt = $pdo->prepare("UPDATE categoria SET nombre = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $id]);
            echo json_encode(['success' => true, 'message' => 'Categoria actualizada']);
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            // Verificar que no tenga productos
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM producto WHERE categoriaId = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($count > 0) {
                echo json_encode(['success' => false, 'message' => 'No puedes eliminar una categoria con productos asociados']);
                return;
            }
            
            $stmt = $pdo->prepare("DELETE FROM categoria WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Categoria eliminada']);
            break;
    }
}

// =============================================
// USUARIOS
// =============================================
function handleUsuarios($pdo, $action) {
    switch ($action) {
        case 'list':
            $busqueda = $_GET['busqueda'] ?? '';
            
            $sql = "
                SELECT 
                    u.id, u.nombre, u.email, u.telefono, u.activo, u.fecha_registro, u.avatar,
                    CASE 
                        WHEN a.id IS NOT NULL THEN 'administrador'
                        WHEN c.id IS NOT NULL THEN 'cliente'
                        ELSE 'sin_rol'
                    END as rol,
                    c.puntosLealtad,
                    c.fecha_ultima_Compra,
                    (SELECT COUNT(*) FROM pedido p2 WHERE p2.clienteId = c.id) as totalPedidos
                FROM usuario u
                LEFT JOIN administrador a ON u.id = a.usuarioId
                LEFT JOIN cliente c ON u.id = c.usuarioId
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($busqueda)) {
                $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
            }
            
            $sql .= " ORDER BY u.fecha_registro DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $usuarios]);
            break;
            
        case 'toggle':
            $id = intval($_POST['id'] ?? 0);
            
            // No permitir desactivar al propio admin
            if ($id == $_SESSION['usuario_id']) {
                echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta']);
                return;
            }
            
            $stmt = $pdo->prepare("UPDATE usuario SET activo = NOT activo WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Estado del usuario actualizado']);
            break;
            
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT u.*, c.puntosLealtad, c.fecha_ultima_Compra 
                FROM usuario u 
                LEFT JOIN cliente c ON u.id = c.usuarioId 
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                unset($user['contraseña']); // No enviar la contrasena
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            }
            break;
    }
}

// =============================================
// PEDIDOS
// =============================================
function handlePedidos($pdo, $action) {
    switch ($action) {
        case 'list':
            $estado = $_GET['estado'] ?? '';
            $busqueda = $_GET['busqueda'] ?? '';
            
            $sql = "
                SELECT 
                    p.id,
                    p.total,
                    p.estado,
                    p.fechaCreacion,
                    p.`numeroSeguimiento:` as numeroSeguimiento,
                    p.fecha_entrega_estimada,
                    u.nombre as clienteNombre,
                    u.email as clienteEmail,
                    d.calle, d.ciudad, d.departamento,
                    (SELECT COUNT(*) FROM itempedido ip WHERE ip.pedidoId = p.id) as totalItems
                FROM pedido p
                INNER JOIN cliente c ON p.clienteId = c.id
                INNER JOIN usuario u ON c.usuarioId = u.id
                LEFT JOIN direccion d ON p.direccionId = d.id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($estado)) {
                $sql .= " AND p.estado = ?";
                $params[] = $estado;
            }
            if (!empty($busqueda)) {
                $sql .= " AND (u.nombre LIKE ? OR p.`numeroSeguimiento:` LIKE ?)";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
            }
            
            $sql .= " ORDER BY p.fechaCreacion DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $pedidos]);
            break;
            
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            
            // Datos del pedido
            $stmt = $pdo->prepare("
                SELECT p.*, u.nombre as clienteNombre, u.email as clienteEmail, u.telefono as clienteTelefono,
                       d.calle, d.apartamento, d.ciudad, d.departamento, d.codigoPostal,
                       mp.tipo as metodoPagoTipo
                FROM pedido p
                INNER JOIN cliente c ON p.clienteId = c.id
                INNER JOIN usuario u ON c.usuarioId = u.id
                LEFT JOIN direccion d ON p.direccionId = d.id
                LEFT JOIN metodo_pago mp ON p.metodoPagoId = mp.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pedido) {
                echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
                return;
            }
            
            // Items del pedido
            $stmt = $pdo->prepare("
                SELECT ip.*, pr.nombre as productoNombre, pr.imagen as productoImagen
                FROM itempedido ip
                INNER JOIN producto pr ON ip.productoId = pr.id
                WHERE ip.pedidoId = ?
            ");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $pedido['items'] = $items;
            echo json_encode(['success' => true, 'data' => $pedido]);
            break;
            
        case 'update_estado':
            $id = intval($_POST['id'] ?? 0);
            $estado = trim($_POST['estado'] ?? '');
            
            $estadosValidos = ['pendiente', 'confirmado', 'en_proceso', 'enviado', 'entregado', 'cancelado'];
            if (!in_array($estado, $estadosValidos)) {
                echo json_encode(['success' => false, 'message' => 'Estado no valido']);
                return;
            }
            
            $stmt = $pdo->prepare("UPDATE pedido SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $id]);
            
            // Si se cancela, devolver stock
            if ($estado === 'cancelado') {
                $stmt = $pdo->prepare("SELECT productoId, cantidad FROM itempedido WHERE pedidoId = ?");
                $stmt->execute([$id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($items as $item) {
                    $stmt = $pdo->prepare("UPDATE producto SET stockDisponible = stockDisponible + ? WHERE id = ?");
                    $stmt->execute([$item['cantidad'], $item['productoId']]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Estado del pedido actualizado']);
            break;
    }
}

// =============================================
// ESTADISTICAS / DASHBOARD
// =============================================
function handleStats($pdo) {
    $action = $_GET['action'] ?? 'basic';
    
    if ($action === 'dashboard') {
        // Ventas del día
        $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as ventasHoy FROM pedido WHERE DATE(fechaCreacion) = CURDATE()");
        $ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC)['ventasHoy'];
        
        // Pedidos del día
        $stmt = $pdo->query("SELECT COUNT(*) as pedidosHoy FROM pedido WHERE DATE(fechaCreacion) = CURDATE()");
        $pedidosHoy = $stmt->fetch(PDO::FETCH_ASSOC)['pedidosHoy'];
        
        // Total productos activos
        $stmt = $pdo->query("SELECT COUNT(*) as totalProductos FROM producto WHERE activo = 1");
        $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['totalProductos'];
        
        // Total clientes
        $stmt = $pdo->query("SELECT COUNT(*) as totalClientes FROM cliente");
        $totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['totalClientes'];
        
        // Pedidos por estado
        $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM pedido GROUP BY estado");
        $pedidosPorEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Últimos pedidos
        $stmt = $pdo->query("
            SELECT p.id, p.total, p.estado, p.fechaCreacion, p.`numeroSeguimiento:` as numeroSeguimiento,
                   u.nombre as clienteNombre, u.email as clienteEmail
            FROM pedido p
            INNER JOIN cliente c ON p.clienteId = c.id
            INNER JOIN usuario u ON c.usuarioId = u.id
            ORDER BY p.fechaCreacion DESC
            LIMIT 10
        ");
        $ultimosPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Productos con bajo stock (menos de 10)
        $stmt = $pdo->query("
            SELECT id, nombre, stockDisponible, imagen 
            FROM producto 
            WHERE activo = 1 AND stockDisponible < 10 
            ORDER BY stockDisponible ASC 
            LIMIT 5
        ");
        $stockBajo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top 5 productos más vendidos
        $stmt = $pdo->query("
            SELECT p.id, p.nombre, p.imagen, SUM(ip.cantidad) as total_vendidos
            FROM producto p
            INNER JOIN itempedido ip ON p.id = ip.productoId
            INNER JOIN pedido ped ON ip.pedidoId = ped.id
            WHERE ped.estado != 'cancelado'
            GROUP BY p.id
            ORDER BY total_vendidos DESC
            LIMIT 5
        ");
        $topProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'ventasHoy' => floatval($ventasHoy),
                'pedidosHoy' => intval($pedidosHoy),
                'totalProductos' => intval($totalProductos),
                'totalClientes' => intval($totalClientes),
                'pedidosPorEstado' => $pedidosPorEstado,
                'ultimosPedidos' => $ultimosPedidos,
                'stockBajo' => $stockBajo,
                'topProductos' => $topProductos
            ]
        ]);
    } else {
        // Stats básicas (compatibilidad con código anterior)
        $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as ventasHoy FROM pedido WHERE DATE(fechaCreacion) = CURDATE()");
        $ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC)['ventasHoy'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as pedidosHoy FROM pedido WHERE DATE(fechaCreacion) = CURDATE()");
        $pedidosHoy = $stmt->fetch(PDO::FETCH_ASSOC)['pedidosHoy'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as nuevosUsuarios FROM usuario WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $nuevosUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['nuevosUsuarios'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as totalProductos FROM producto WHERE activo = 1");
        $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['totalProductos'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as totalClientes FROM cliente");
        $totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['totalClientes'];
        
        $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM pedido GROUP BY estado");
        $pedidosPorEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->query("
            SELECT p.id, p.total, p.estado, p.fechaCreacion, p.`numeroSeguimiento:` as numeroSeguimiento,
                   u.nombre as clienteNombre
            FROM pedido p
            INNER JOIN cliente c ON p.clienteId = c.id
            INNER JOIN usuario u ON c.usuarioId = u.id
            ORDER BY p.fechaCreacion DESC
            LIMIT 10
        ");
        $pedidosRecientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->query("SELECT id, nombre, stockDisponible FROM producto WHERE activo = 1 AND stockDisponible < 10 ORDER BY stockDisponible ASC LIMIT 5");
        $bajoStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'ventasHoy' => floatval($ventasHoy),
                'pedidosHoy' => intval($pedidosHoy),
                'nuevosUsuarios' => intval($nuevosUsuarios),
                'totalProductos' => intval($totalProductos),
                'totalClientes' => intval($totalClientes),
                'pedidosPorEstado' => $pedidosPorEstado,
                'pedidosRecientes' => $pedidosRecientes,
                'bajoStock' => $bajoStock
            ]
        ]);
    }
}
?>
