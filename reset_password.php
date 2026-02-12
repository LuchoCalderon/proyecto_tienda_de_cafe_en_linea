<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: perfilUsuario.php');
    exit;
}
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cafe en Linea - Restablecer Contrasena</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <div class="container-fluid">
    <div class="row vh-100">
      <div class="col-md-6 d-none d-md-block p-0">
        <div class="image-container">
          <img src="images/cafe.jpeg" alt="Cafe" class="custom-image">
        </div>
      </div>

      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <div class="card shadow small-card">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h2><a href="home.php">Cafe en Linea</a></h2>
              <div class="mb-3">
                <i class="bi bi-shield-lock-fill" style="font-size: 3rem; color: var(--coffee-brown);"></i>
              </div>
              <h4>Nueva contrasena</h4>
              <p class="text-muted">Ingresa tu nueva contrasena.</p>
            </div>

            <!-- Token invalido -->
            <div id="tokenInvalido" class="d-none">
              <div class="alert alert-danger text-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="tokenErrorMsg">El enlace ha expirado o no es valido.</span>
              </div>
              <div class="d-grid">
                <a href="forgot_password.php" class="btn btn-primary">Solicitar nuevo enlace</a>
              </div>
            </div>

            <!-- Formulario de nueva contrasena -->
            <div id="formularioReset" class="d-none">
              <div id="mensajeReset" class="alert d-none" role="alert"></div>

              <form id="formResetPassword">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="mb-3">
                  <label for="password" class="form-label">Nueva contrasena</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Minimo 8 caracteres" required minlength="8">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="password_confirm" class="form-label">Confirmar contrasena</label>
                  <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Repite la contrasena" required minlength="8">
                </div>

                <div class="d-grid gap-2 mb-3">
                  <button type="submit" class="btn btn-primary" id="btnReset">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                    Restablecer contrasena
                  </button>
                </div>
              </form>
            </div>

            <!-- Exito -->
            <div id="resetExitoso" class="d-none text-center">
              <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                Contrasena actualizada correctamente
              </div>
              <div class="d-grid">
                <a href="login.php" class="btn btn-primary">Iniciar sesion</a>
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
  const token = '<?php echo htmlspecialchars($token, ENT_QUOTES); ?>';

  // Al cargar, verificar si el token es valido
  document.addEventListener('DOMContentLoaded', async function() {
    if (!token) {
      document.getElementById('tokenInvalido').classList.remove('d-none');
      return;
    }

    try {
      const response = await fetch('./php/reset_password.php?token=' + encodeURIComponent(token));
      const data = await response.json();

      if (data.success) {
        document.getElementById('formularioReset').classList.remove('d-none');
      } else {
        document.getElementById('tokenErrorMsg').textContent = data.message;
        document.getElementById('tokenInvalido').classList.remove('d-none');
      }
    } catch (error) {
      document.getElementById('tokenInvalido').classList.remove('d-none');
    }
  });

  // Toggle mostrar/ocultar contrasena
  document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
  });

  // Enviar formulario
  document.getElementById('formResetPassword').addEventListener('submit', async function(e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;
    const mensajeDiv = document.getElementById('mensajeReset');
    const btn = document.getElementById('btnReset');
    const spinner = btn.querySelector('.spinner-border');

    // Validar que coincidan
    if (password !== confirm) {
      mensajeDiv.classList.remove('d-none', 'alert-success');
      mensajeDiv.classList.add('alert-danger');
      mensajeDiv.textContent = 'Las contrasenas no coinciden';
      return;
    }

    if (password.length < 8) {
      mensajeDiv.classList.remove('d-none', 'alert-success');
      mensajeDiv.classList.add('alert-danger');
      mensajeDiv.textContent = 'La contrasena debe tener al menos 8 caracteres';
      return;
    }

    btn.disabled = true;
    spinner.classList.remove('d-none');
    mensajeDiv.classList.add('d-none');

    try {
      const formData = new FormData(this);
      const response = await fetch('./php/reset_password.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        document.getElementById('formularioReset').classList.add('d-none');
        document.getElementById('resetExitoso').classList.remove('d-none');
      } else {
        mensajeDiv.classList.remove('d-none', 'alert-success');
        mensajeDiv.classList.add('alert-danger');
        mensajeDiv.textContent = data.message;
      }
    } catch (error) {
      mensajeDiv.classList.remove('d-none', 'alert-success');
      mensajeDiv.classList.add('alert-danger');
      mensajeDiv.textContent = 'Error al conectar con el servidor';
    } finally {
      btn.disabled = false;
      spinner.classList.add('d-none');
    }
  });
  </script>
</body>
</html>
