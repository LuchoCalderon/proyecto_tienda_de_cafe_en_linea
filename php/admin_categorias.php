<?php
/**
 * php/admin_categorias_api.php - API para gestión de categorías con imágenes
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/check_auth.php';

verificarAdminAPI();

try {
    $pdo = getDBConnection();
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            listarCategorias($pdo);
            break;
        case 'get':
            obtenerCategoria($pdo);
            break;
        case 'create':
            crearCategoria($pdo);
            break;
        case 'update':
            actualizarCategoria($pdo);
            break;
        case 'delete':
            eliminarCategoria($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en admin_categorias_api: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function listarCategorias($pdo) {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as totalProductos
        FROM categoria c
        LEFT JOIN producto p ON c.id = p.categoriaId
        GROUP BY c.id
        ORDER BY c.nombre
    ");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $categorias
    ]);
}

function obtenerCategoria($pdo) {
    $id = intval($_GET['id'] ?? 0);
    
    $stmt = $pdo->prepare("SELECT * FROM categoria WHERE id = ?");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($categoria) {
        echo json_encode(['success' => true, 'data' => $categoria]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
    }
}

function crearCategoria($pdo) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        return;
    }
    
    // Procesar imagen si fue subida
    $rutaImagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $rutaImagen = subirImagen($_FILES['imagen']);
        if (!$rutaImagen) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
            return;
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO categoria (nombre, descripcion, imagen) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $rutaImagen]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Categoría creada exitosamente',
        'id' => $pdo->lastInsertId()
    ]);
}

function actualizarCategoria($pdo) {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        return;
    }
    
    // Obtener imagen actual
    $stmt = $pdo->prepare("SELECT imagen FROM categoria WHERE id = ?");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $rutaImagen = $categoria['imagen'] ?? null;
    
    // Procesar nueva imagen si fue subida
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nuevaRuta = subirImagen($_FILES['imagen']);
        if ($nuevaRuta) {
            // Eliminar imagen anterior
            if ($rutaImagen && file_exists(__DIR__ . '/../' . $rutaImagen)) {
                unlink(__DIR__ . '/../' . $rutaImagen);
            }
            $rutaImagen = $nuevaRuta;
        }
    }
    
    $stmt = $pdo->prepare("UPDATE categoria SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?");
    $stmt->execute([$nombre, $descripcion, $rutaImagen, $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Categoría actualizada exitosamente'
    ]);
}

function eliminarCategoria($pdo) {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    // Verificar que no tenga productos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM producto WHERE categoriaId = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar una categoría con productos asignados']);
        return;
    }
    
    // Obtener imagen para eliminarla
    $stmt = $pdo->prepare("SELECT imagen FROM categoria WHERE id = ?");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Eliminar categoría
    $stmt = $pdo->prepare("DELETE FROM categoria WHERE id = ?");
    $stmt->execute([$id]);
    
    // Eliminar imagen
    if ($categoria && $categoria['imagen'] && file_exists(__DIR__ . '/../' . $categoria['imagen'])) {
        unlink(__DIR__ . '/../' . $categoria['imagen']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Categoría eliminada exitosamente'
    ]);
}

function subirImagen($archivo) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $tamanoMaximo = 5 * 1024 * 1024; // 5 MB
    
    // Validar tamaño
    if ($archivo['size'] > $tamanoMaximo) {
        return false;
    }
    
    // Validar extensión
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensionesPermitidas)) {
        return false;
    }
    
    // Crear directorio si no existe
    $directorioDestino = __DIR__ . '/../images/categorias/';
    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0755, true);
    }
    
    // Generar nombre único
    $nombreArchivo = 'cat_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $rutaCompleta = $directorioDestino . $nombreArchivo;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return 'images/categorias/' . $nombreArchivo;
    }
    
    return false;
}
?>