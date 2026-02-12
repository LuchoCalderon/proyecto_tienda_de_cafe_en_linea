<?php
// Si ya esta autenticado, redirigir
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: perfilUsuario.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafe en Linea - Recuperar Contrasena</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <div class="container-fluid">
    <div class="row vh-100">
      <!-- Columna de la imagen -->
      <div class="col-md-6 d-none d-md-block p-0">
        <div class="image-container">
          <img src="images/cafe.jpeg" alt="Cafe" class="custom-image">
        </div>
      </div>

      <!-- Columna del formulario -->
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <div class="card shadow small-card">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h2><a href="home.php">Cafe en Linea</a></h2>
              <div class="mb-3">
                <i class="bi bi-lock-fill" style="font-size: 3rem; color: var(--coffee-brown);"></i>
              </div>
              <h4>Recuperar contrasena</h4>
              <p class="text-muted">Ingresa tu correo electronico y te enviaremos un enlace para restablecer tu contrasena.</p>
            </div>

            <div id="mensajeForgot" class="alert d-none" role="alert"></div>

            <!-- Formulario de solicitud -->
            <form id="formForgotPassword">
              <div class="mb-3">
                <label for="email" class="form-label">Correo electronico</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="tucorreo@ejemplo.com" required>
              </div>

              <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-primary" id="btnEnviar">
                  <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                  Enviar enlace de recuperacion
                </button>
              </div>
            </form>

            <!-- Enlace para desarrollo (se muestra cuando mail() falla en XAMPP) -->
            <div id="devLinkContainer" class="d-none mt-3">
              <div class="alert alert-info">
                <small>
                  <strong>Modo desarrollo (XAMPP):</strong><br>
                  El servidor de correo no esta configurado. Usa este enlace directamente:
                </small>
                <div class="mt-2">
                  <a href="#" id="devResetLink" class="btn btn-sm btn-outline-primary" target="_blank">Ir al enlace de recuperacion</a>
                </div>
              </div>
            </div>

            <hr class="my-4">

            <div class="text-center">
              <a href="login.php" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Volver a iniciar sesion</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
  document.getElementById('formForgotPassword').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnEnviar');
    const spinner = btn.querySelector('.spinner-border');
    const mensajeDiv = document.getElementById('mensajeForgot');

    // Mostrar loading
    btn.disabled = true;
    spinner.classList.remove('d-none');
    mensajeDiv.classList.add('d-none');

    try {
      const formData = new FormData(this);
      const response = await fetch('./php/forgot_password.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      mensajeDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
      mensajeDiv.classList.add(data.success ? 'alert-success' : 'alert-danger');
      mensajeDiv.textContent = data.message;

      if (data.success) {
        this.reset();

        // Si hay enlace de desarrollo (XAMPP local), mostrarlo
        if (data.dev_link) {
          document.getElementById('devLinkContainer').classList.remove('d-none');
          document.getElementById('devResetLink').href = data.dev_link;
        }
      }
    } catch (error) {
      mensajeDiv.classList.remove('d-none', 'alert-success');
      mensajeDiv.classList.add('alert-danger');
      mensajeDiv.textContent = 'Error al conectar con el servidor.';
    } finally {
      btn.disabled = false;
      spinner.classList.add('d-none');
    }
  });
  </script>
</body>
</html>
