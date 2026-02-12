<?php
/**
 * Archivo de prueba para diagnosticar problemas de conexión
 * Accede a: http://localhost/tu_proyecto/php/test_connection.php
 * ELIMINA ESTE ARCHIVO EN PRODUCCIÓN
 */

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Test de Conexión a Base de Datos</h2>";

// 1. Verificar que el archivo de configuración existe
$configPath = __DIR__ . '/../config/db_config.php';
echo "<p><strong>1. Archivo de configuración:</strong> ";
if (file_exists($configPath)) {
    echo "✅ Encontrado en: $configPath</p>";
} else {
    echo "❌ NO encontrado en: $configPath</p>";
    die("<p style='color:red;'>Verifica que el archivo config/db_config.php exista.</p>");
}

// 2. Incluir configuración
echo "<p><strong>2. Cargando configuración...</strong> ";
try {
    require_once $configPath;
    echo "✅ Configuración cargada</p>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "</p>";
    die();
}

// 3. Mostrar configuración actual
echo "<p><strong>3. Configuración actual:</strong></p>";
echo "<ul>";
echo "<li>Host: " . DB_HOST . "</li>";
echo "<li>Puerto: " . DB_PORT . "</li>";
echo "<li>Usuario: " . DB_USER . "</li>";
echo "<li>Base de datos: " . DB_NAME . "</li>";
echo "</ul>";

// 4. Intentar conexión
echo "<p><strong>4. Probando conexión...</strong> ";
try {
    $pdo = getDBConnection();
    echo "✅ Conexión exitosa!</p>";
    
    // 5. Verificar tablas
    echo "<p><strong>5. Tablas en la base de datos:</strong></p>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color:orange;'>⚠️ No hay tablas. Ejecuta el script SQL primero.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    // 6. Verificar tabla usuario
    echo "<p><strong>6. Verificando tabla 'usuario'...</strong> ";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuario");
    $result = $stmt->fetch();
    echo "✅ " . $result['total'] . " usuarios encontrados</p>";
    
    // 7. Verificar si hay administradores
    echo "<p><strong>7. Verificando administradores...</strong> ";
    $stmt = $pdo->query("
        SELECT u.nombre, u.email 
        FROM usuario u 
        INNER JOIN administrador a ON u.id = a.usuarioId 
        WHERE a.activo = 1
    ");
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        echo "⚠️ No hay administradores activos. Ejecuta el script SQL de inserción.</p>";
    } else {
        echo "✅ Administradores encontrados:</p>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>" . htmlspecialchars($admin['nombre']) . " (" . htmlspecialchars($admin['email']) . ")</li>";
        }
        echo "</ul>";
    }
    
    // 8. Verificar estructura de contraseñas
    echo "<p><strong>8. Verificando formato de contraseñas...</strong> ";
    $stmt = $pdo->query("SELECT id, nombre, email, contraseña FROM usuario LIMIT 1");
    $user = $stmt->fetch();
    
    if ($user) {
        $passLength = strlen($user['contraseña']);
        if ($passLength >= 60 && strpos($user['contraseña'], '$2y$') === 0) {
            echo "✅ Contraseñas hasheadas correctamente (bcrypt)</p>";
        } else {
            echo "❌ Las contraseñas NO están hasheadas con bcrypt.</p>";
            echo "<p style='color:red;'>Las contraseñas deben estar hasheadas. Longitud actual: $passLength caracteres</p>";
            echo "<p><strong>Solución:</strong> Ejecuta este SQL para hashear las contraseñas existentes:</p>";
            echo "<pre>
-- Para el admin (contraseña: admin123)
UPDATE usuario SET contraseña = '\$2y\$10\$YourHashHere' WHERE email = 'admin@cafeenlinea.com';

-- O ejecuta el script: scripts/02_insert_admin_usuario.sql
            </pre>";
        }
    }
    
    echo "<hr><p style='color:green;'><strong>✅ Todo parece estar configurado correctamente!</strong></p>";
    
} catch (Exception $e) {
    echo "❌ Error de conexión</p>";
    echo "<p style='color:red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>Posibles soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verifica que XAMPP esté corriendo (Apache y MySQL)</li>";
    echo "<li>Verifica el puerto de MySQL en XAMPP (normalmente 3306, a veces 3307)</li>";
    echo "<li>Verifica que la base de datos 'proyecto_tienda_cafe' exista</li>";
    echo "<li>Ejecuta el script SQL en phpMyAdmin</li>";
    echo "</ul>";
}
?>

<hr>
<p><small>Este archivo es solo para diagnóstico. Elimínalo en producción.</small></p>
