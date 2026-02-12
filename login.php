<?php
// Si ya está autenticado, redirigir según su rol
session_start();
if (isset($_SESSION['usuario_id'])) {
    $redirect = ($_SESSION['usuario_rol'] === 'administrador') ? 'administrador.php' : 'perfilUsuario.php';
    header('Location: ' . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Café en Línea - Iniciar Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Navbar -->
  <?php include 'includes/navbar.php'; ?>
  
  <div class="container-fluid">
    <div class="row vh-100">
      <!-- Columna de la imagen -->
      <div class="col-md-6 d-none d-md-block p-0">
        <div class="image-container">
          <img src="images/cafe.jpeg" alt="Café" class="custom-image">
        </div>
      </div>
    
      <!-- Columna del formulario -->
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <div class="card shadow small-card">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h2><a href="home.php">Café en Línea</a></h2>
              <p class="text-muted">Inicia sesión para continuar</p>
            </div>
            
            <!-- Actualizado formulario con id y manejo AJAX -->
            <?php if (isset($_GET['expired'])): ?>
            <div class="alert alert-warning">Tu sesión ha expirado. Por favor, inicia sesión nuevamente.</div>
            <?php endif; ?>
            
            <form id="formLogin">
              <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="tucorreo@ejemplo.com" required>
              </div>
              
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                <label class="form-check-label" for="rememberMe">Recordarme</label>
              </div>
              
              <!-- Agregado div para mensajes -->
              <div id="mensajeLogin" class="alert d-none" role="alert"></div>
              
              <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-primary" id="btnLogin">Iniciar Sesión</button>
              </div>
              
              <div class="text-center mt-3">
                <a href="forgot_password.php" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
              </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
              <p>¿No tienes una cuenta? <a href="formulario.php" class="text-decoration-none">Regístrate</a></p>
            </div>
            
            <!-- Agregada información de prueba -->
            <div class="mt-4 p-3 bg-light rounded">
              <small class="text-muted">
                <strong>Credenciales de prueba:</strong><br>
                <strong>Admin:</strong> admin@cafeenlinea.com / admin123<br>
                <strong>Usuario:</strong> Regístrate para crear uno
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <!-- Agregado script para manejo del formulario -->
  <script>
  document.getElementById('formLogin').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btnLogin = document.getElementById('btnLogin');
    const mensajeDiv = document.getElementById('mensajeLogin');
    
    // Deshabilitar botón
    btnLogin.disabled = true;
    btnLogin.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando sesión...';
    
    // Ocultar mensajes anteriores
    mensajeDiv.classList.add('d-none');
    
    try {
      const formData = new FormData(this);
      const response = await fetch('./php/login.php', {
        method: 'POST',
        body: formData
      });
      
      const data = await response.json();
      
      // Mostrar mensaje
      mensajeDiv.classList.remove('d-none');
      mensajeDiv.classList.remove('alert-success', 'alert-danger');
      mensajeDiv.classList.add(data.success ? 'alert-success' : 'alert-danger');
      mensajeDiv.textContent = data.message;
      
      if (data.success) {
        // Redirigir según el rol
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 1000);
      } else {
        // Rehabilitar botón si hay error
        btnLogin.disabled = false;
        btnLogin.innerHTML = 'Iniciar Sesión';
      }
    } catch (error) {
      console.error('Error:', error);
      mensajeDiv.classList.remove('d-none');
      mensajeDiv.classList.remove('alert-success');
      mensajeDiv.classList.add('alert-danger');
      mensajeDiv.textContent = 'Error al conectar con el servidor. Por favor, intenta de nuevo.';
      
      btnLogin.disabled = false;
      btnLogin.innerHTML = 'Iniciar Sesión';
    }
  });
  </script>
</body>
</html>
