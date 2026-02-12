<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../config/Session.php';

/**
 * Clase AuthController - Controlador de autenticación
 */
class AuthController {
    private $usuarioModel;
    private $clienteModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->clienteModel = new Cliente();
    }
    
    /**
     * Registrar nuevo usuario
     * @param array $datos
     * @return array
     */
    public function registrar($datos) {
        try {
            // Validar datos requeridos
            $required = ['nombre', 'email', 'contrasena', 'telefono'];
            foreach ($required as $field) {
                if (empty($datos[$field])) {
                    return [
                        'success' => false,
                        'message' => "El campo {$field} es requerido"
                    ];
                }
            }
            
            // Validar formato de email
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'El formato del email no es válido'
                ];
            }
            
            // Verificar si el email ya existe
            if ($this->usuarioModel->emailExiste($datos['email'])) {
                return [
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ];
            }
            
            // Validar contraseña (mínimo 8 caracteres)
            if (strlen($datos['contrasena']) < 8) {
                return [
                    'success' => false,
                    'message' => 'La contraseña debe tener al menos 8 caracteres'
                ];
            }
            
            // Crear usuario
            $this->usuarioModel->nombre = $datos['nombre'];
            $this->usuarioModel->email = $datos['email'];
            $this->usuarioModel->contrasena = $datos['contrasena'];
            $this->usuarioModel->telefono = $datos['telefono'];
            $this->usuarioModel->activo = 1;
            
            if ($this->usuarioModel->crear()) {
                // Crear registro de cliente
                $this->clienteModel->usuarioId = $this->usuarioModel->id;
                $this->clienteModel->puntosLealtad = 0;
                
                if ($this->clienteModel->crear()) {
                    return [
                        'success' => true,
                        'message' => 'Usuario registrado exitosamente',
                        'userId' => $this->usuarioModel->id,
                        'clienteId' => $this->clienteModel->id
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Error al registrar el usuario'
            ];
            
        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en el servidor'
            ];
        }
    }
    
    /**
     * Iniciar sesión
     * @param string $email
     * @param string $contrasena
     * @return array
     */
    public function login($email, $contrasena) {
        try {
            // Validar datos
            if (empty($email) || empty($contrasena)) {
                return [
                    'success' => false,
                    'message' => 'Email y contraseña son requeridos'
                ];
            }
            
            // Verificar credenciales
            $usuario = $this->usuarioModel->verificarCredenciales($email, $contrasena);
            
            if (!$usuario) {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ];
            }
            
            // Verificar si el usuario está activo
            if (!$usuario['activo']) {
                return [
                    'success' => false,
                    'message' => 'Usuario desactivado'
                ];
            }
            
            // Obtener datos del cliente
            $cliente = $this->clienteModel->obtenerPorUsuarioId($usuario['id']);
            
            // Crear sesión
            Session::start();
            Session::set('usuario_id', $usuario['id']);
            Session::set('cliente_id', $cliente['id']);
            Session::set('nombre', $usuario['nombre']);
            Session::set('email', $usuario['email']);
            Session::set('autenticado', true);
            
            return [
                'success' => true,
                'message' => 'Login exitoso',
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'clienteId' => $cliente['id']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en el servidor'
            ];
        }
    }
    
    /**
     * Cerrar sesión
     * @return array
     */
    public function logout() {
        Session::destroy();
        
        return [
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ];
    }
    
    /**
     * Verificar si hay sesión activa
     * @return bool
     */
    public function verificarSesion() {
        Session::start();
        return Session::get('autenticado') === true;
    }
    
    /**
     * Obtener usuario actual
     * @return array|null
     */
    public function obtenerUsuarioActual() {
        if (!$this->verificarSesion()) {
            return null;
        }
        
        return [
            'id' => Session::get('usuario_id'),
            'clienteId' => Session::get('cliente_id'),
            'nombre' => Session::get('nombre'),
            'email' => Session::get('email')
        ];
    }
    
    /**
     * Cambiar contraseña
     * @param int $userId
     * @param string $contrasenaActual
     * @param string $nuevaContrasena
     * @return array
     */
    public function cambiarContrasena($userId, $contrasenaActual, $nuevaContrasena) {
        try {
            // Obtener usuario
            $usuario = $this->usuarioModel->obtenerPorId($userId);
            
            if (!$usuario) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }
            
            // Verificar contraseña actual
            if (!password_verify($contrasenaActual, $usuario['contrasena'])) {
                return [
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ];
            }
            
            // Validar nueva contraseña
            if (strlen($nuevaContrasena) < 8) {
                return [
                    'success' => false,
                    'message' => 'La nueva contraseña debe tener al menos 8 caracteres'
                ];
            }
            
            // Cambiar contraseña
            if ($this->usuarioModel->cambiarContrasena($userId, $nuevaContrasena)) {
                return [
                    'success' => true,
                    'message' => 'Contraseña actualizada exitosamente'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña'
            ];
            
        } catch (Exception $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en el servidor'
            ];
        }
    }
}