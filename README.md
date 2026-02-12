# proyecto_tienda_de_cafe_en_linea
# Backend - Tienda de Café en Línea

## Descripción
Backend desarrollado en PHP utilizando arquitectura MVC y patrón de diseño orientado a objetos para la gestión de una tienda de café en línea.

## Estructura del Proyecto

```
backend/
├── config/
│   ├── Database.php          # Conexión a base de datos (Singleton)
│   └── Session.php            # Manejo de sesiones
├── models/
│   ├── Usuario.php            # Modelo de usuarios
│   ├── Cliente.php            # Modelo de clientes
│   ├── Producto.php           # Modelo de productos
│   ├── Carrito.php            # Modelo del carrito
│   └── Pedido.php             # Modelo de pedidos
├── controllers/
│   └── AuthController.php     # Controlador de autenticación
├── api/
│   └── productos.php          # API REST de productos
└── README.md
```

## Requisitos

- PHP 8.0 o superior
- MySQL 5.7 o superior (MariaDB 10.4+)
- Extensiones PHP:
  - PDO
  - PDO_MySQL
  - JSON
  - Session

## Configuración

### 1. Base de Datos

Configurar las credenciales en `config/Database.php`:

```php
private $host = 'localhost';
private $port = '3307';
private $dbname = 'proyecto_tienda_cafe';
private $username = 'root';
private $password = '';
```

### 2. Importar Schema

Ejecutar el archivo SQL proporcionado para crear las tablas necesarias:

```bash
mysql -u root -p proyecto_tienda_cafe < proyecto_tienda_cafe.sql
```

## Características Principales

### Patrones de Diseño

1. **Singleton** - Para la conexión a base de datos
2. **MVC** - Separación de lógica de negocio y presentación
3. **Repository Pattern** - Modelos para acceso a datos
4. **RESTful API** - Endpoints siguiendo estándares REST

### Seguridad

- **Password Hashing**: Uso de `PASSWORD_BCRYPT` para contraseñas
- **Prepared Statements**: Prevención de SQL Injection
- **Input Sanitization**: Limpieza de datos de entrada
- **Session Security**: Configuración segura de sesiones PHP
- **HTTPS Ready**: Configuración para cookies seguras

### Modelos Implementados

#### Usuario
- Registro de usuarios
- Autenticación
- Gestión de perfiles
- Cambio de contraseñas

#### Producto
- CRUD completo de productos
- Búsqueda y filtrado
- Gestión de inventario
- Productos destacados

#### Carrito
- Agregar/eliminar productos
- Actualizar cantidades
- Cálculo de totales
- Persistencia por usuario

#### Pedido
- Creación de pedidos
- Seguimiento de estados
- Historial de compras
- Estadísticas

## API Endpoints

### Productos

#### GET /api/productos.php
Obtener listado de productos

**Parámetros:**
- `limite` (opcional): Número de resultados (default: 20)
- `offset` (opcional): Offset para paginación (default: 0)
- `categoria` (opcional): Filtrar por categoría
- `busqueda` (opcional): Búsqueda de texto
- `destacados` (opcional): Solo productos destacados

**Respuesta:**
```json
[
  {
    "id": 1,
    "nombre": "Café Colombiano Premium",
    "descripcion": "Café de origen único...",
    "precio": 25000,
    "stockDisponible": 50,
    "imagen": "cafe_colombiano.jpg",
    "destacado": 1,
    "activo": 1
  }
]
```

#### GET /api/productos.php?id={id}
Obtener un producto específico

#### POST /api/productos.php
Crear nuevo producto

**Body:**
```json
{
  "nombre": "Producto nuevo",
  "descripcion": "Descripción del producto",
  "precio": 30000,
  "categoriaId": 1,
  "stockDisponible": 100,
  "imagen": "imagen.jpg",
  "destacado": 0,
  "activo": 1
}
```

#### PUT /api/productos.php
Actualizar producto existente

#### DELETE /api/productos.php?id={id}
Eliminar (desactivar) producto

## Uso de los Modelos

### Ejemplo: Registro de Usuario

```php
require_once 'controllers/AuthController.php';

$authController = new AuthController();

$datos = [
    'nombre' => 'Juan Pérez',
    'email' => 'juan@ejemplo.com',
    'contrasena' => 'password123',
    'telefono' => '3101234567'
];

$resultado = $authController->registrar($datos);

if ($resultado['success']) {
    echo "Usuario registrado: " . $resultado['userId'];
} else {
    echo "Error: " . $resultado['message'];
}
```

### Ejemplo: Crear Pedido

```php
require_once 'models/Pedido.php';

$pedido = new Pedido();
$pedido->clienteId = 1;
$pedido->metodoPagoId = 1;
$pedido->direccionId = 1;
$pedido->total = 75000;
$pedido->estado = Pedido::ESTADO_PENDIENTE;

$items = [
    [
        'productoId' => 1,
        'cantidad' => 2,
        'precioUnitario' => 25000
    ],
    [
        'productoId' => 2,
        'cantidad' => 1,
        'precioUnitario' => 25000
    ]
];

$pedidoId = $pedido->crear($items);

if ($pedidoId) {
    echo "Pedido creado: #" . $pedidoId;
}
```

### Ejemplo: Gestión del Carrito

```php
require_once 'models/Carrito.php';

$carrito = new Carrito();

// Obtener o crear carrito
$carritoId = $carrito->obtenerOCrearCarrito($clienteId);

// Agregar producto
$carrito->agregarProducto(
    $carritoId,
    $productoId,
    $cantidad,
    $precioUnitario
);

// Obtener items
$items = $carrito->obtenerItems($carritoId);

// Calcular total
$total = $carrito->calcularTotal($carritoId);
```

## Buenas Prácticas Implementadas

### Código
- **PSR-4**: Autoloading de clases
- **Nombres descriptivos**: Variables y métodos con nombres claros
- **DRY**: No repetir código
- **SOLID**: Principios de diseño orientado a objetos
- **Documentación**: PHPDoc en todas las clases y métodos

### Base de Datos
- **Índices**: En campos de búsqueda frecuente
- **Transacciones**: Para operaciones críticas
- **Prepared Statements**: En todas las consultas
- **Normalización**: Base de datos normalizada

### Seguridad
- **Validación de entrada**: Todos los datos son validados
- **Escape de salida**: Datos sanitizados antes de usar
- **Error handling**: Manejo apropiado de errores
- **Logging**: Registro de errores para debugging

## Testing

### Pruebas Unitarias Sugeridas

```php
// Ejemplo de test para Usuario
public function testCrearUsuario() {
    $usuario = new Usuario();
    $usuario->nombre = "Test User";
    $usuario->email = "test@ejemplo.com";
    $usuario->contrasena = "password123";
    $usuario->telefono = "3001234567";
    $usuario->activo = 1;
    
    $this->assertTrue($usuario->crear());
    $this->assertNotNull($usuario->id);
}
```

## Despliegue

### Configuración para Producción

1. **Habilitar HTTPS**
```php
// En config/Session.php
ini_set('session.cookie_secure', 1);
```

2. **Deshabilitar errores visibles**
```php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');
```

3. **Optimizar base de datos**
```sql
-- Crear índices adicionales
CREATE INDEX idx_producto_categoria ON producto(categoriaId);
CREATE INDEX idx_pedido_cliente ON pedido(clienteId);
CREATE INDEX idx_pedido_estado ON pedido(estado);
```

## Mantenimiento

### Logs
Los errores se registran automáticamente usando `error_log()`. Revisar logs periódicamente:

```bash
tail -f /var/log/php_errors.log
```

### Backup de Base de Datos
Realizar backups periódicos:

```bash
mysqldump -u root -p proyecto_tienda_cafe > backup_$(date +%Y%m%d).sql
```

## Contacto y Soporte

Para reportar bugs o solicitar nuevas características, contactar al equipo de desarrollo.

## Licencia

Proyecto educativo - SENA 2024
