<?php
/**
 * php/forgot_password.php - Backend para solicitar recuperacion de contrasena
 * 
 * Recibe: POST { email }
 * Retorna: JSON { success, message }
 * 
 * Flujo:
 * 1. Valida que el email exista en la BD
 * 2. Genera un token aleatorio seguro
 * 3. Guarda el token en la tabla password_resets con expiracion de 1 hora
 * 4. Envia un email con el enlace de recuperacion
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

try {
    require_once __DIR__ . '/../config/db_config.php';
    $pdo = getDBConnection();
    
    $email = trim($_POST['email'] ?? '');
    
    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Ingresa un correo electronico valido']);
        exit;
    }
    
    // Verificar que el email existe
    $stmt = $pdo->prepare("SELECT id, nombre FROM usuario WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        // Por seguridad, no revelamos si el email existe o no
        echo json_encode([
            'success' => true, 
            'message' => 'Si el correo esta registrado, recibiras un enlace para restablecer tu contrasena.'
        ]);
        exit;
    }
    
    // Invalidar tokens anteriores no usados para este email
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0");
    $stmt->execute([$email]);
    
    // Generar token seguro
    $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Guardar token en la BD
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (email, token, expires_at, used) 
        VALUES (?, ?, ?, 0)
    ");
    $stmt->execute([$email, hash('sha256', $token), $expires_at]);
    
    // Construir enlace de recuperacion
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
    $resetLink = $protocol . '://' . $host . $basePath . '/reset_password.php?token=' . $token;
    
    // Enviar email
    $nombreUsuario = $usuario['nombre'];
    $asunto = 'Recuperacion de contrasena - Cafe en Linea';
    
    $mensajeHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background-color: #6F4E37; color: white; padding: 30px; text-align: center; }
            .content { padding: 30px; }
            .btn { display: inline-block; background-color: #6F4E37; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Cafe en Linea</h1>
            </div>
            <div class='content'>
                <h2>Hola, {$nombreUsuario}</h2>
                <p>Recibimos una solicitud para restablecer tu contrasena. Haz clic en el siguiente boton para crear una nueva:</p>
                <p style='text-align: center;'>
                    <a href='{$resetLink}' class='btn'>Restablecer Contrasena</a>
                </p>
                <p>Si no puedes hacer clic en el boton, copia y pega este enlace en tu navegador:</p>
                <p style='word-break: break-all; color: #6F4E37;'>{$resetLink}</p>
                <p><strong>Este enlace expirara en 1 hora.</strong></p>
                <p>Si no solicitaste este cambio, puedes ignorar este correo. Tu contrasena no sera modificada.</p>
            </div>
            <div class='footer'>
                <p>Este es un correo automatico de Cafe en Linea. Por favor no respondas a este mensaje.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Headers del email
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Cafe en Linea <noreply@cafeenlinea.com>\r\n";
    $headers .= "Reply-To: noreply@cafeenlinea.com\r\n";
    
    // Intentar enviar email con mail()
    $emailEnviado = @mail($email, $asunto, $mensajeHTML, $headers);
    
    if ($emailEnviado) {
        echo json_encode([
            'success' => true, 
            'message' => 'Se ha enviado un enlace de recuperacion a tu correo electronico.'
        ]);
    } else {
        // Si mail() falla (comun en XAMPP local), mostramos el enlace directamente para desarrollo
        // En produccion, deberias usar PHPMailer con SMTP
        echo json_encode([
            'success' => true, 
            'message' => 'Se ha generado el enlace de recuperacion.',
            'dev_link' => $resetLink, // SOLO PARA DESARROLLO - Quitar en produccion
            'dev_note' => 'En entorno local (XAMPP), el email no se envia. Usa este enlace directamente.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en forgot_password: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud. Intenta de nuevo.']);
}
?>
