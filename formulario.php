<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Café en línea - Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
          <img src="images/cafe.jpeg" alt="Granos de café" class="custom-image">
        </div>
      </div>
      
      <!-- Columna del formulario -->
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <div class="card shadow small-card">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h2 class="coffee-title">Café en Línea</h2>
              <p class="text-muted">Crea tu cuenta</p>
            </div>
            
            <!-- Actualizado action y agregado id al formulario -->
            <form id="formRegistro" action="./php/register.php" method="POST">
              <div class="row mb-3">
                <div class="mb-3">
                  <label for="firstName" class="form-label">Nombre Completo</label>
                  <input type="text" class="form-control" id="firstName" name="nombres" placeholder="Ingresa tus nombres" required>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="cedula" class="form-label">Cédula</label>
                    <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ingresa tu número de cédula" required pattern="[0-9]{6,10}">
                </div>
            
                <div class="col-md-6">
                  <label for="birthdate" class="form-label">Fecha de nacimiento</label>
                  <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                </div>
              </div>
              
              <div class="row mb-3">  
                <div class="col-md-6 mb-3 mb-md-0">
                  <label for="email" class="form-label">Correo electrónico</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="tucorreo@ejemplo.com" required>
                </div>
                <div class="col-md-6">
                    <label for="lastName" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="lastName" name="telefono" placeholder="Ingresa tu teléfono" required pattern="[0-9]{7,15}">
                </div>
              </div>
              
              <div class="row mb-3">
                 <!-- Campo contraseña -->
                 <div class="col-md-6 mb-3 mb-md-0">
                     <label for="password" class="form-label">Contraseña</label>
                     <div class="input-group">
                         <input 
                             type="password" 
                             class="form-control" 
                             id="password" 
                             name="password" 
                             placeholder="Crea una contraseña" 
                             required
                             minlength="6"
                         >
                         <span 
                             class="input-group-text password-toggle" 
                             style="cursor: pointer;"
                             onmousedown="showPassword('password')"
                             onmouseup="hidePassword('password')"
                             onmouseleave="hidePassword('password')"
                         >
                             <i class="bi bi-eye" id="password-eye"></i>
                         </span>
                     </div>
                 </div>
                 
                 <!-- Campo confirmar contraseña -->
                 <div class="col-md-6">
                     <label for="confirmPassword" class="form-label">Confirmar contraseña</label>
                     <div class="input-group">
                         <input 
                             type="password" 
                             class="form-control" 
                             id="confirmPassword" 
                             name="confirmPassword" 
                             placeholder="Confirma tu contraseña" 
                             required
                         >
                         <span 
                             class="input-group-text password-toggle" 
                             style="cursor: pointer;"
                             onmousedown="showPassword('confirmPassword')"
                             onmouseup="hidePassword('confirmPassword')"
                             onmouseleave="hidePassword('confirmPassword')"
                         >
                             <i class="bi bi-eye" id="confirmPassword-eye"></i>
                         </span>
                     </div>
                 </div>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="termsCheck" required>
                <label class="form-check-label" for="termsCheck">
                  Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a>
                </label>
              </div>
              
              <!-- Agregado div para mensajes -->
              <div id="mensajeRegistro" class="alert d-none" role="alert"></div>
              
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" id="btnRegistro">Registrarse</button>
              </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
              <p>¿Ya tienes una cuenta? <a href="login.php" class="text-decoration-none">Inicia sesión</a></p>
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
  // Funciones para mostrar/ocultar contraseña
  function showPassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-eye');
    field.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  }
  
  function hidePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-eye');
    field.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
  
  // Manejo del formulario de registro
  document.getElementById('formRegistro').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btnRegistro = document.getElementById('btnRegistro');
    const mensajeDiv = document.getElementById('mensajeRegistro');
    
    // Deshabilitar botón
    btnRegistro.disabled = true;
    btnRegistro.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando...';
    
    // Ocultar mensajes anteriores
    mensajeDiv.classList.add('d-none');
    
    try {
      const formData = new FormData(this);
      const response = await fetch('./php/register.php', {
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
        // Redirigir al login después de 2 segundos
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 2000);
      } else {
        // Rehabilitar botón si hay error
        btnRegistro.disabled = false;
        btnRegistro.innerHTML = 'Registrarse';
      }
    } catch (error) {
      console.error('Error:', error);
      mensajeDiv.classList.remove('d-none');
      mensajeDiv.classList.remove('alert-success');
      mensajeDiv.classList.add('alert-danger');
      mensajeDiv.textContent = 'Error al conectar con el servidor. Por favor, intenta de nuevo.';
      
      btnRegistro.disabled = false;
      btnRegistro.innerHTML = 'Registrarse';
    }
  });
  </script>
</body>
</html>
