<?php
/**
 * php/catalogo_api.php - API publica para el catalogo de productos
 * 
 * No requiere autenticacion (es publico)
 * 
 * Parametros GET:
 * - busqueda     : Texto para buscar en nombre/descripcion
 * - categoriaId  : Filtrar por categoria
 * - precioMin    : Precio minimo
 * - precioMax    : Precio maximo
 * - orden        : precio_asc, precio_desc, nombre_asc, nombre_desc, recientes
 * - destacados   : 1 para solo destacados
 * - pagina       : Numero de pagina (1,2,3...)
 * - porPagina    : Items por pagina (default 12)
 */

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/db_config.php';
    $pdo = getDBConnection();
    
    $action = $_GET['action'] ?? 'productos';
    
    if ($action === 'categorias') {
        // Listar categorias con conteo de productos
        $stmt = $pdo->query("
            SELECT c.id, c.nombre, c.descripcion, c.imagen,
                   COUNT(p.id) as totalProductos
            FROM categoria c
            LEFT JOIN producto p ON c.id = p.categoriaId AND p.activo = 1
            GROUP BY c.id
            ORDER BY c.nombre ASC
        ");
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $categorias]);
        exit;
    }
    
    // === FILTROS ===
    $busqueda = trim($_GET['busqueda'] ?? '');
    $categoriaId = intval($_GET['categoriaId'] ?? 0);
    $precioMin = floatval($_GET['precioMin'] ?? 0);
    $precioMax = floatval($_GET['precioMax'] ?? 0);
    $orden = $_GET['orden'] ?? 'recientes';
    $destacados = intval($_GET['destacados'] ?? 0);
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $porPagina = min(50, max(1, intval($_GET['porPagina'] ?? 12)));
    $offset = ($pagina - 1) * $porPagina;
    
    // Construir query
    $sql = "SELECT p.*, c.nombre as categoriaNombre FROM producto p LEFT JOIN categoria c ON p.categoriaId = c.id WHERE p.activo = 1";
    $sqlCount = "SELECT COUNT(*) as total FROM producto p WHERE p.activo = 1";
    $params = [];
    $paramsCount = [];
    
    // Busqueda
    if (!empty($busqueda)) {
        $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
        $sqlCount .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
        $params[] = "%$busqueda%";
        $params[] = "%$busqueda%";
        $paramsCount[] = "%$busqueda%";
        $paramsCount[] = "%$busqueda%";
    }
    
    // Categoria
    if ($categoriaId > 0) {
        $sql .= " AND p.categoriaId = ?";
        $sqlCount .= " AND p.categoriaId = ?";
        $params[] = $categoriaId;
        $paramsCount[] = $categoriaId;
    }
    
    // Rango de precio
    if ($precioMin > 0) {
        $sql .= " AND p.precio >= ?";
        $sqlCount .= " AND p.precio >= ?";
        $params[] = $precioMin;
        $paramsCount[] = $precioMin;
    }
    if ($precioMax > 0) {
        $sql .= " AND p.precio <= ?";
        $sqlCount .= " AND p.precio <= ?";
        $params[] = $precioMax;
        $paramsCount[] = $precioMax;
    }
    
    // Solo destacados
    if ($destacados) {
        $sql .= " AND p.destacado = 1";
        $sqlCount .= " AND p.destacado = 1";
    }
    
    // Ordenamiento
    switch ($orden) {
        case 'precio_asc':
            $sql .= " ORDER BY p.precio ASC";
            break;
        case 'precio_desc':
            $sql .= " ORDER BY p.precio DESC";
            break;
        case 'nombre_asc':
            $sql .= " ORDER BY p.nombre ASC";
            break;
        case 'nombre_desc':
            $sql .= " ORDER BY p.nombre DESC";
            break;
        default:
            $sql .= " ORDER BY p.id DESC";
    }
    
    // Paginacion
    $sql .= " LIMIT $porPagina OFFSET $offset";
    
    // Ejecutar consultas
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($paramsCount);
    $totalResultados = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPaginas = ceil($totalResultados / $porPagina);
    
    echo json_encode([
        'success' => true,
        'data' => $productos,
        'paginacion' => [
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'total' => intval($totalResultados),
            'totalPaginas' => intval($totalPaginas)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en catalogo_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al cargar productos']);
}
?>
