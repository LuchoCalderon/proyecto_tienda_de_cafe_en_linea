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
            
            <form action="./php/enviarDatos.php" method="POST">
              <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                  <label for="firstName" class="form-label">Nombres</label>
                  <input type="text" class="form-control" id="firstName" name="nombres" placeholder="Ingresa tus nombres" required pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚ]{3,40}">
                </div>
                <div class="col-md-6">
                  <label for="lastName" class="form-label">Apellidos</label>
                  <input type="text" class="form-control" id="lastName" name="apellidos" placeholder="Ingresa tus apellidos" required pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚ]{3,40}">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label for="cedula" class="form-label">Cédula</label>
                    <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Ingresa tu número de cédula" required pattern="[0-9]{6,10}">
                </div>
            
              <div class="col-md-6">
                <label for="birthdate" class="form-label">Fecha de nacimiento</label>
                <input type="date" class="form-control" id="birthdate" required>
              </div>
            </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email"  placeholder="tucorreo@ejemplo.com" required>
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
                         >
                         <span 
                             class="input-group-text password-toggle" 
                             onmouseenter="showPassword('password')"
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
                             onmouseenter="showPassword('confirmPassword')"
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
              
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Registrarse</button>
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

  <script src="script.js"></script>
</body>
</html>

