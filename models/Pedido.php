<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Pedido - Modelo para gestión de pedidos
 */
class Pedido {
    private $db;
    private $tablePedido = 'pedido';
    private $tableItems = 'itempedido';
    
    public $id;
    public $clienteId;
    public $metodoPagoId;
    public $direccionId;
    public $total;
    public $estado;
    public $fechaCreacion;
    public $numeroSeguimiento;
    public $fecha_entrega_estimada;
    
    // Estados posibles del pedido
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PROCESANDO = 'procesando';
    const ESTADO_ENVIADO = 'enviado';
    const ESTADO_ENTREGADO = 'entregado';
    const ESTADO_CANCELADO = 'cancelado';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear un nuevo pedido
     * @param array $items Array de items del carrito
     * @return int|false ID del pedido o false si falla
     */
    public function crear($items) {
        try {
            $this->db->beginTransaction();
            
            // Generar número de seguimiento único
            $this->numeroSeguimiento = $this->generarNumeroSeguimiento();
            
            // Calcular fecha estimada de entrega (7 días)
            $this->fecha_entrega_estimada = date('Y-m-d H:i:s', strtotime('+7 days'));
            
            // Insertar pedido
            $query = "INSERT INTO {$this->tablePedido} 
                     (clienteId, metodoPagoId, direccionId, total, estado, 
                      fechaCreacion, numeroSeguimiento, fecha_entrega_estimada) 
                     VALUES (:clienteId, :metodoPagoId, :direccionId, :total, 
                             :estado, NOW(), :numeroSeguimiento, :fecha_entrega_estimada)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':clienteId', $this->clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':metodoPagoId', $this->metodoPagoId, PDO::PARAM_INT);
            $stmt->bindParam(':direccionId', $this->direccionId, PDO::PARAM_INT);
            $stmt->bindParam(':total', $this->total);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':numeroSeguimiento', $this->numeroSeguimiento);
            $stmt->bindParam(':fecha_entrega_estimada', $this->fecha_entrega_estimada);
            
            $stmt->execute();
            $pedidoId = $this->db->lastInsertId();
            
            // Insertar items del pedido
            foreach ($items as $item) {
                $subtotal = $item['cantidad'] * $item['precioUnitario'];
                
                $query = "INSERT INTO {$this->tableItems} 
                         (pedidoId, productoId, cantidad, precioUnitario, subtotal) 
                         VALUES (:pedidoId, :productoId, :cantidad, :precioUnitario, :subtotal)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':pedidoId', $pedidoId, PDO::PARAM_INT);
                $stmt->bindParam(':productoId', $item['productoId'], PDO::PARAM_INT);
                $stmt->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmt->bindParam(':precioUnitario', $item['precioUnitario']);
                $stmt->bindParam(':subtotal', $subtotal);
                $stmt->execute();
            }
            
            $this->db->commit();
            return $pedidoId;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al crear pedido: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener pedido por ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT p.*, 
                            u.nombre as cliente_nombre, 
                            u.email as cliente_email,
                            d.calle, d.ciudad, d.instrucciones
                     FROM {$this->tablePedido} p
                     INNER JOIN cliente c ON p.clienteId = c.id
                     INNER JOIN usuario u ON c.usuarioId = u.id
                     LEFT JOIN direccion d ON p.direccionId = d.id
                     WHERE p.id = :id LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al obtener pedido: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener items de un pedido
     * @param int $pedidoId
     * @return array
     */
    public function obtenerItems($pedidoId) {
        try {
            $query = "SELECT ip.*, p.nombre, p.imagen 
                     FROM {$this->tableItems} ip
                     INNER JOIN producto p ON ip.productoId = p.id
                     WHERE ip.pedidoId = :pedidoId";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':pedidoId', $pedidoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener items del pedido: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener pedidos de un cliente
     * @param int $clienteId
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function obtenerPorCliente($clienteId, $limite = 10, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->tablePedido} 
                     WHERE clienteId = :clienteId 
                     ORDER BY fechaCreacion DESC 
                     LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':clienteId', $clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener pedidos del cliente: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar estado del pedido
     * @param int $pedidoId
     * @param string $nuevoEstado
     * @return bool
     */
    public function actualizarEstado($pedidoId, $nuevoEstado) {
        try {
            // Validar estado
            $estadosValidos = [
                self::ESTADO_PENDIENTE,
                self::ESTADO_PROCESANDO,
                self::ESTADO_ENVIADO,
                self::ESTADO_ENTREGADO,
                self::ESTADO_CANCELADO
            ];
            
            if (!in_array($nuevoEstado, $estadosValidos)) {
                return false;
            }
            
            $query = "UPDATE {$this->tablePedido} 
                     SET estado = :estado 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id', $pedidoId, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los pedidos (para administrador)
     * @param string $estado
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function obtenerTodos($estado = null, $limite = 20, $offset = 0) {
        try {
            $query = "SELECT p.*, 
                            u.nombre as cliente_nombre, 
                            u.email as cliente_email
                     FROM {$this->tablePedido} p
                     INNER JOIN cliente c ON p.clienteId = c.id
                     INNER JOIN usuario u ON c.usuarioId = u.id";
            
            if ($estado) {
                $query .= " WHERE p.estado = :estado";
            }
            
            $query .= " ORDER BY p.fechaCreacion DESC LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            if ($estado) {
                $stmt->bindParam(':estado', $estado);
            }
            
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener pedidos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar número de seguimiento único
     * @return string
     */
    private function generarNumeroSeguimiento() {
        return 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
    }
    
    /**
     * Obtener estadísticas de pedidos
     * @return array
     */
    public function obtenerEstadisticas() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_pedidos,
                        SUM(CASE WHEN estado = :pendiente THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = :procesando THEN 1 ELSE 0 END) as procesando,
                        SUM(CASE WHEN estado = :enviado THEN 1 ELSE 0 END) as enviados,
                        SUM(CASE WHEN estado = :entregado THEN 1 ELSE 0 END) as entregados,
                        SUM(total) as ventas_totales,
                        AVG(total) as ticket_promedio
                     FROM {$this->tablePedido}
                     WHERE fechaCreacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':pendiente', self::ESTADO_PENDIENTE);
            $stmt->bindValue(':procesando', self::ESTADO_PROCESANDO);
            $stmt->bindValue(':enviado', self::ESTADO_ENVIADO);
            $stmt->bindValue(':entregado', self::ESTADO_ENTREGADO);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
}