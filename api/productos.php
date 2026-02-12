<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Producto.php';

/**
 * API REST para gestión de productos
 */
class ProductosAPI {
    private $productoModel;
    private $method;
    
    public function __construct() {
        $this->productoModel = new Producto();
        $this->method = $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Procesar la petición
     */
    public function handleRequest() {
        try {
            switch ($this->method) {
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->handlePost();
                    break;
                case 'PUT':
                    $this->handlePut();
                    break;
                case 'DELETE':
                    $this->handleDelete();
                    break;
                default:
                    $this->sendResponse(405, ['error' => 'Método no permitido']);
            }
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'error' => 'Error en el servidor',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Manejar peticiones GET
     */
    private function handleGet() {
        // Obtener parámetros
        $id = $_GET['id'] ?? null;
        $categoria = $_GET['categoria'] ?? null;
        $busqueda = $_GET['busqueda'] ?? null;
        $destacados = $_GET['destacados'] ?? null;
        $limite = $_GET['limite'] ?? 20;
        $offset = $_GET['offset'] ?? 0;
        
        if ($id) {
            // Obtener producto por ID
            $producto = $this->productoModel->obtenerPorId($id);
            
            if ($producto) {
                $this->sendResponse(200, $producto);
            } else {
                $this->sendResponse(404, ['error' => 'Producto no encontrado']);
            }
            
        } elseif ($destacados) {
            // Obtener productos destacados
            $productos = $this->productoModel->obtenerDestacados($limite);
            $this->sendResponse(200, $productos);
            
        } elseif ($categoria) {
            // Obtener productos por categoría
            $productos = $this->productoModel->obtenerPorCategoria($categoria);
            $this->sendResponse(200, $productos);
            
        } elseif ($busqueda) {
            // Buscar productos
            $productos = $this->productoModel->buscar($busqueda);
            $this->sendResponse(200, $productos);
            
        } else {
            // Obtener todos los productos
            $productos = $this->productoModel->obtenerTodos($limite, $offset);
            $this->sendResponse(200, $productos);
        }
    }
    
    /**
     * Manejar peticiones POST (crear producto)
     */
    private function handlePost() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $this->sendResponse(400, ['error' => 'Datos inválidos']);
            return;
        }
        
        // Validar datos requeridos
        $required = ['nombre', 'descripcion', 'precio', 'categoriaId', 'stockDisponible'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->sendResponse(400, [
                    'error' => "El campo {$field} es requerido"
                ]);
                return;
            }
        }
        
        // Asignar valores
        $this->productoModel->nombre = $data['nombre'];
        $this->productoModel->descripcion = $data['descripcion'];
        $this->productoModel->precio = $data['precio'];
        $this->productoModel->categoriaId = $data['categoriaId'];
        $this->productoModel->stockDisponible = $data['stockDisponible'];
        $this->productoModel->imagen = $data['imagen'] ?? '';
        $this->productoModel->destacado = $data['destacado'] ?? 0;
        $this->productoModel->activo = $data['activo'] ?? 1;
        
        // Crear producto
        if ($this->productoModel->crear()) {
            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'id' => $this->productoModel->id
            ]);
        } else {
            $this->sendResponse(500, [
                'error' => 'Error al crear el producto'
            ]);
        }
    }
    
    /**
     * Manejar peticiones PUT (actualizar producto)
     */
    private function handlePut() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['id'])) {
            $this->sendResponse(400, ['error' => 'ID de producto requerido']);
            return;
        }
        
        // Asignar valores
        $this->productoModel->id = $data['id'];
        $this->productoModel->nombre = $data['nombre'];
        $this->productoModel->descripcion = $data['descripcion'];
        $this->productoModel->precio = $data['precio'];
        $this->productoModel->categoriaId = $data['categoriaId'];
        $this->productoModel->stockDisponible = $data['stockDisponible'];
        $this->productoModel->imagen = $data['imagen'];
        $this->productoModel->destacado = $data['destacado'];
        $this->productoModel->activo = $data['activo'];
        
        // Actualizar producto
        if ($this->productoModel->actualizar()) {
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ]);
        } else {
            $this->sendResponse(500, [
                'error' => 'Error al actualizar el producto'
            ]);
        }
    }
    
    /**
     * Manejar peticiones DELETE (eliminar producto)
     */
    private function handleDelete() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendResponse(400, ['error' => 'ID de producto requerido']);
            return;
        }
        
        // Desactivar producto (soft delete)
        $producto = $this->productoModel->obtenerPorId($id);
        
        if (!$producto) {
            $this->sendResponse(404, ['error' => 'Producto no encontrado']);
            return;
        }
        
        $this->productoModel->id = $id;
        $this->productoModel->activo = 0;
        $this->productoModel->nombre = $producto['nombre'];
        $this->productoModel->descripcion = $producto['descripcion'];
        $this->productoModel->precio = $producto['precio'];
        $this->productoModel->categoriaId = $producto['categoriaId'];
        $this->productoModel->stockDisponible = $producto['stockDisponible'];
        $this->productoModel->imagen = $producto['imagen'];
        $this->productoModel->destacado = $producto['destacado'];
        
        if ($this->productoModel->actualizar()) {
            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);
        } else {
            $this->sendResponse(500, [
                'error' => 'Error al eliminar el producto'
            ]);
        }
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// Ejecutar API
$api = new ProductosAPI();
$api->handleRequest();