<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Cliente - Modelo para gestión de clientes
 */
class Cliente {
    private $db;
    private $table = 'cliente';
    
    public $id;
    public $usuarioId;
    public $fecha_ultima_Compra;
    public $puntosLealtad;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear un nuevo cliente
     * @return bool
     */
    public function crear() {
        try {
            $query = "INSERT INTO {$this->table} 
                     (usuarioId, puntosLealtad) 
                     VALUES (:usuarioId, :puntosLealtad)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuarioId', $this->usuarioId, PDO::PARAM_INT);
            $stmt->bindParam(':puntosLealtad', $this->puntosLealtad, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->id = $this->db->lastInsertId();
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error al crear cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener cliente por ID de usuario
     * @param int $usuarioId
     * @return array|false
     */
    public function obtenerPorUsuarioId($usuarioId) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE usuarioId = :usuarioId LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al obtener cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener cliente por ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT c.*, u.nombre, u.email, u.telefono 
                     FROM {$this->table} c
                     INNER JOIN usuario u ON c.usuarioId = u.id
                     WHERE c.id = :id LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al obtener cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar puntos de lealtad
     * @param int $clienteId
     * @param int $puntos
     * @param string $operacion ('sumar' o 'restar')
     * @return bool
     */
    public function actualizarPuntos($clienteId, $puntos, $operacion = 'sumar') {
        try {
            $operador = ($operacion === 'sumar') ? '+' : '-';
            
            $query = "UPDATE {$this->table} 
                     SET puntosLealtad = puntosLealtad {$operador} :puntos 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':puntos', $puntos, PDO::PARAM_INT);
            $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error al actualizar puntos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar fecha de última compra
     * @param int $clienteId
     * @return bool
     */
    public function actualizarUltimaCompra($clienteId) {
        try {
            $query = "UPDATE {$this->table} 
                     SET fecha_ultima_Compra = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error al actualizar última compra: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los clientes
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function obtenerTodos($limite = 20, $offset = 0) {
        try {
            $query = "SELECT c.*, u.nombre, u.email, u.telefono, u.activo
                     FROM {$this->table} c
                     INNER JOIN usuario u ON c.usuarioId = u.id
                     ORDER BY c.id DESC
                     LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener clientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas del cliente
     * @param int $clienteId
     * @return array
     */
    public function obtenerEstadisticas($clienteId) {
        try {
            $query = "SELECT 
                        COUNT(p.id) as total_pedidos,
                        COALESCE(SUM(p.total), 0) as total_gastado,
                        COALESCE(AVG(p.total), 0) as ticket_promedio,
                        c.puntosLealtad
                     FROM {$this->table} c
                     LEFT JOIN pedido p ON c.id = p.clienteId
                     WHERE c.id = :clienteId
                     GROUP BY c.id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':clienteId', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
}