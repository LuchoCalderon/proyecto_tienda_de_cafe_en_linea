<?php
require_once 'php/check_auth.php';
verificarAutenticacion();

$usuario = obtenerUsuarioActual();
if (!$usuario) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cafe en Linea - Mi Perfil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  .navbar {
    background-color: var(--dark-brown);
  }
  
  .navbar-brand, .nav-link {
    color: var(--cream);
  }
  
  .nav-link:hover {
    color: #fff;
  }
  
  .profile-sidebar {
    background-color: #f8f9fa;
    border-radius: 10px;
  }
  
  .profile-sidebar .nav-link {
    color: #333;
    border-radius: 5px;
    padding: 10px 15px;
    margin-bottom: 5px;
  }
  
  .profile-sidebar .nav-link:hover {
    background-color: #e9ecef;
  }
  
  .profile-sidebar .nav-link.active {
    background-color: var(--coffee-brown);
    color: white;
  }
  
  .profile-sidebar .nav-link i {
    width: 20px;
    text-align: center;
    margin-right: 10px;
  }
  
  .profile-content {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
  }
  
  .avatar-container {
    width: 100px;
    height: 100px;
    position: relative;
  }
  
  .avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
  }
  
  .avatar-edit {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: var(--coffee-brown);
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .avatar-edit:hover {
    background-color: var(--dark-brown);
  }
  
  .loyalty-card {
    background: linear-gradient(135deg, var(--coffee-brown) 0%, var(--dark-brown) 100%);
    color: white;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
  }
  
  .loyalty-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    opacity: 0.3;
  }
</style>
</head>
<body>
<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <div class="row">
    <!-- Sidebar de navegacion del perfil -->
    <div class="col-lg-3 mb-4 mb-lg-0">
      <div class="profile-sidebar p-4">
        <h5 class="coffee-title mb-4">Mi Cuenta</h5>
        <nav class="nav flex-column">
          <a class="nav-link active" href="perfilUsuario.php">
            <i class="bi bi-person"></i> Mi Perfil
          </a>
          <a class="nav-link" href="historialOrdenes.php">
            <i class="bi bi-box-seam"></i> Mis Pedidos
          </a>
          <a class="nav-link" href="misDirecciones.php">
            <i class="bi bi-geo-alt"></i> Mis Direcciones
          </a>
          <a class="nav-link" href="metodosPago.php">
            <i class="bi bi-credit-card"></i> Metodos de Pago
          </a>
          <a class="nav-link" href="misSuscripciones.php">
            <i class="bi bi-calendar-check"></i> Mis Suscripciones
          </a>
          <hr>
          <a class="nav-link text-danger" href="php/logout.php">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesion
          </a>
        </nav>
      </div>
    </div>
    
    <!-- Contenido principal del perfil -->
    <div class="col-lg-9">
      <div class="profile-content p-4">
        <h2 class="coffee-title mb-4">Mi Perfil</h2>
        
        <div class="row mb-5">
          <!-- Columna izquierda: Formulario de datos personales -->
          <div class="col-md-6 mb-4 mb-md-0">
            <h5 class="mb-3">Informacion Personal</h5>
            <div id="profileAlert" class="alert d-none" role="alert"></div>
            <form id="formDatosPersonales">
              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Correo electronico</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
              </div>
              
              <div class="mb-3">
                <label for="telefono" class="form-label">Telefono</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required>
              </div>
              
              <button type="submit" class="btn btn-primary" id="btnGuardarDatos">
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                Guardar cambios
              </button>
            </form>
          </div>
          
          <!-- Columna derecha: Avatar y tarjeta de lealtad -->
          <div class="col-md-6 d-flex flex-column">
            <div class="text-center mb-4">
              <div class="avatar-container mx-auto">
                <?php if (!empty($usuario['avatar'])): ?>
                  <img src="<?php echo htmlspecialchars($usuario['avatar']); ?>?v=<?php echo time(); ?>" alt="Foto de perfil" class="avatar" id="avatarPreview">
                <?php else: ?>
                  <div class="avatar d-flex align-items-center justify-content-center bg-secondary text-white" style="font-size: 2.5rem;" id="avatarPreview">
                    <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                  </div>
                <?php endif; ?>
                <label for="inputFotoPerfil" class="avatar-edit" title="Cambiar foto">
                  <i class="bi bi-camera"></i>
                </label>
                <input type="file" id="inputFotoPerfil" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
              </div>
              
              <!-- Indicador de carga -->
              <div id="uploadProgress" class="d-none mt-2">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Subiendo...</span>
                </div>
                <small class="text-muted ms-2">Subiendo imagen...</small>
              </div>
              <div id="uploadAlert" class="d-none mt-2"></div>
              
              <h5 class="mt-3 mb-0"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
              <?php
              $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
              $fechaTexto = 'N/A';
              if (isset($_SESSION['fecha_registro']) && $_SESSION['fecha_registro']) {
                  $mes = (int)date('n', strtotime($_SESSION['fecha_registro'])) - 1;
                  $anio = date('Y', strtotime($_SESSION['fecha_registro']));
                  $fechaTexto = $meses[$mes] . ' ' . $anio;
              }
              ?>
              <p class="text-muted">Cliente desde <?php echo $fechaTexto; ?></p>
            </div>
            
            <div class="loyalty-card p-4 mt-auto">
              <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                  <h6 class="text-white-50 mb-1">Programa de Lealtad</h6>
                  <h4 class="mb-0">Tarjeta Oro</h4>
                </div>
                <div class="bg-white text-dark rounded-circle p-2">
                  <i class="bi bi-cup-hot-fill"></i>
                </div>
              </div>
              
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span>Puntos disponibles</span>
                  <span class="fw-bold"><?php echo $usuario['puntosLealtad'] ?? 0; ?></span>
                </div>
                <div class="progress" style="height: 10px;">
                  <?php 
                  $puntos = $usuario['puntosLealtad'] ?? 0;
                  $porcentaje = min(($puntos / 500) * 100, 100);
                  ?>
                  <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $porcentaje; ?>%" aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <small>0</small>
                  <small><?php echo max(500 - $puntos, 0); ?> para siguiente nivel</small>
                </div>
              </div>
              
              <a href="" class="btn btn-sm btn-light">Ver beneficios</a>
            </div>
          </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row">
          <div class="col-md-6 mb-4 mb-md-0">
            <h5 class="mb-3">Cambiar contrasena</h5>
            <div id="passwordAlert" class="alert d-none" role="alert"></div>
            <form id="formCambiarPassword">
              <div class="mb-3">
                <label for="passwordActual" class="form-label">Contrasena actual</label>
                <input type="password" class="form-control" id="passwordActual" name="password_actual" required>
              </div>
              
              <div class="mb-3">
                <label for="passwordNueva" class="form-label">Nueva contrasena</label>
                <input type="password" class="form-control" id="passwordNueva" name="password_nueva" required>
                <div class="form-text">La contrasena debe tener al menos 8 caracteres.</div>
              </div>
              
              <div class="mb-3">
                <label for="passwordConfirmar" class="form-label">Confirmar nueva contrasena</label>
                <input type="password" class="form-control" id="passwordConfirmar" name="password_confirmar" required>
              </div>
              
              <button type="submit" class="btn btn-primary" id="btnCambiarPassword">
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                Actualizar contrasena
              </button>
            </form>
          </div>
          
          <div class="col-md-6">
            <h5 class="mb-3">Preferencias de comunicacion</h5>
            <form>
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="newsletterCheck" checked>
                <label class="form-check-label" for="newsletterCheck">Recibir newsletter con novedades y promociones</label>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="orderUpdatesCheck" checked>
                <label class="form-check-label" for="orderUpdatesCheck">Recibir actualizaciones sobre mis pedidos</label>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="productUpdatesCheck" checked>
                <label class="form-check-label" for="productUpdatesCheck">Recibir notificaciones sobre nuevos productos</label>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="specialOffersCheck" checked>
                <label class="form-check-label" for="specialOffersCheck">Recibir ofertas especiales y descuentos</label>
              </div>
              
              <button type="submit" class="btn btn-primary">Guardar preferencias</button>
            </form>
            
            <hr class="my-4">
            
            <h5 class="mb-3 text-danger">Zona de peligro</h5>
            <p class="text-muted small">Una vez que elimines tu cuenta, no hay vuelta atras. Por favor, asegurate de estar seguro.</p>
            <div class="d-grid">
              <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarCuenta">
                <i class="bi bi-trash"></i> Eliminar mi cuenta
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Eliminar Cuenta -->
<div class="modal fade" id="modalEliminarCuenta" tabindex="-1" aria-labelledby="modalEliminarCuentaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalEliminarCuentaLabel">
          <i class="bi bi-exclamation-triangle"></i> Eliminar cuenta
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <strong>Advertencia:</strong> Esta accion es irreversible. Se eliminaran todos tus datos, pedidos e informacion asociada a tu cuenta.
        </div>
        <div id="deleteAlert" class="alert d-none" role="alert"></div>
        <form id="formEliminarCuenta">
          <div class="mb-3">
            <label for="passwordEliminar" class="form-label">Ingresa tu contrasena para confirmar:</label>
            <input type="password" class="form-control" id="passwordEliminar" name="password" required placeholder="Tu contrasena actual">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          Eliminar mi cuenta permanentemente
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

function showAlert(elementId, message, type) {
  const alert = document.getElementById(elementId);
  alert.className = `alert alert-${type}`;
  alert.textContent = message;
  alert.classList.remove('d-none');
  
  if (type === 'success') {
    setTimeout(() => {
      alert.classList.add('d-none');
    }, 5000);
  }
}

function toggleButtonLoading(button, loading) {
  const spinner = button.querySelector('.spinner-border');
  if (loading) {
    spinner.classList.remove('d-none');
    button.disabled = true;
  } else {
    spinner.classList.add('d-none');
    button.disabled = false;
  }
}

// =============================================
// SUBIDA DE FOTO DE PERFIL (AVATAR)
// =============================================
document.getElementById('inputFotoPerfil').addEventListener('change', async function(e) {
  const file = e.target.files[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    showUploadAlert('Solo se permiten imagenes (JPG, PNG, GIF, WEBP)', 'danger');
    return;
  }

  const maxSize = 5 * 1024 * 1024;
  if (file.size > maxSize) {
    showUploadAlert('La imagen no debe superar los 5MB', 'danger');
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e) {
    const preview = document.getElementById('avatarPreview');
    if (preview.tagName === 'IMG') {
      preview.src = e.target.result;
    } else {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.alt = 'Foto de perfil';
      img.className = 'avatar';
      img.id = 'avatarPreview';
      preview.parentNode.replaceChild(img, preview);
    }
  };
  reader.readAsDataURL(file);

  const formData = new FormData();
  formData.append('avatar', file);

  document.getElementById('uploadProgress').classList.remove('d-none');
  document.getElementById('uploadAlert').classList.add('d-none');

  try {
    const response = await fetch(basePath + 'php/upload_avatar.php', {
      method: 'POST',
      body: formData
    });

    const responseText = await response.text();
    let data;
    try {
      data = JSON.parse(responseText);
    } catch (parseError) {
      showUploadAlert('Error del servidor', 'danger');
      return;
    }

    if (data.success) {
      showUploadAlert('Foto actualizada correctamente', 'success');
    } else {
      showUploadAlert(data.message || 'Error al subir la imagen', 'danger');
    }
  } catch (error) {
    showUploadAlert('Error al conectar con el servidor', 'danger');
  } finally {
    document.getElementById('uploadProgress').classList.add('d-none');
  }
});

function showUploadAlert(message, type) {
  const alert = document.getElementById('uploadAlert');
  alert.className = `alert alert-${type} py-1 px-2 small`;
  alert.textContent = message;
  alert.classList.remove('d-none');
  
  if (type === 'success') {
    setTimeout(() => alert.classList.add('d-none'), 3000);
  }
}

// =============================================
// FORMULARIO DE DATOS PERSONALES
// =============================================
document.getElementById('formDatosPersonales').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const btn = document.getElementById('btnGuardarDatos');
  toggleButtonLoading(btn, true);
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch(basePath + 'php/update_profile.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showAlert('profileAlert', data.message, 'success');
      const navbarUserName = document.querySelector('.navbar .dropdown-toggle');
      if (navbarUserName && data.data) {
        navbarUserName.innerHTML = '<i class="bi bi-person-circle me-1"></i>' + data.data.nombre;
      }
    } else {
      showAlert('profileAlert', data.message, 'danger');
    }
  } catch (error) {
    showAlert('profileAlert', 'Error al conectar con el servidor', 'danger');
  } finally {
    toggleButtonLoading(btn, false);
  }
});

// =============================================
// FORMULARIO DE CAMBIO DE CONTRASENA
// =============================================
document.getElementById('formCambiarPassword').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const passwordNueva = document.getElementById('passwordNueva').value;
  const passwordConfirmar = document.getElementById('passwordConfirmar').value;
  
  if (passwordNueva !== passwordConfirmar) {
    showAlert('passwordAlert', 'Las contrasenas nuevas no coinciden', 'danger');
    return;
  }
  
  if (passwordNueva.length < 8) {
    showAlert('passwordAlert', 'La contrasena debe tener al menos 8 caracteres', 'danger');
    return;
  }
  
  const btn = document.getElementById('btnCambiarPassword');
  toggleButtonLoading(btn, true);
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch(basePath + 'php/change_password.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showAlert('passwordAlert', data.message, 'success');
      this.reset();
    } else {
      showAlert('passwordAlert', data.message, 'danger');
    }
  } catch (error) {
    showAlert('passwordAlert', 'Error al conectar con el servidor', 'danger');
  } finally {
    toggleButtonLoading(btn, false);
  }
});

// =============================================
// ELIMINAR CUENTA
// =============================================
document.getElementById('btnConfirmarEliminar').addEventListener('click', async function() {
  const password = document.getElementById('passwordEliminar').value;
  
  if (!password) {
    showAlert('deleteAlert', 'Debes ingresar tu contrasena', 'danger');
    return;
  }
  
  const btn = this;
  toggleButtonLoading(btn, true);
  
  const formData = new FormData();
  formData.append('password', password);
  
  try {
    const response = await fetch(basePath + 'php/delete_account.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showAlert('deleteAlert', data.message + ' Redirigiendo...', 'success');
      setTimeout(() => {
        window.location.href = basePath + 'login.php';
      }, 2000);
    } else {
      showAlert('deleteAlert', data.message, 'danger');
      toggleButtonLoading(btn, false);
    }
  } catch (error) {
    showAlert('deleteAlert', 'Error al conectar con el servidor', 'danger');
    toggleButtonLoading(btn, false);
  }
});
</script>
</body>
</html>
