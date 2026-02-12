<?php
require_once 'php/check_auth.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Café en Línea - Mis Direcciones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
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
  
  .address-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
    border: 2px solid #e9ecef;
  }
  
  .address-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .address-card.default {
    border-color: var(--coffee-brown);
    background-color: #fdfbf7;
  }
  
  .badge-default {
    background-color: var(--coffee-brown);
  }
  
  .btn-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }
</style>
</head>
<body>

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3 mb-4 mb-lg-0">
      <div class="profile-sidebar p-4">
        <h5 class="coffee-title mb-4">Mi Cuenta</h5>
        <nav class="nav flex-column">
          <a class="nav-link" href="perfilUsuario.php">
            <i class="bi bi-person"></i> Mi Perfil
          </a>
          <a class="nav-link" href="historialOrdenes.php">
            <i class="bi bi-box-seam"></i> Mis Pedidos
          </a>
          <a class="nav-link active" href="misDirecciones.php">
            <i class="bi bi-geo-alt"></i> Mis Direcciones
          </a>
          <a class="nav-link" href="metodosPago.php">
             <i class="bi bi-credit-card"></i> Métodos de Pago
          </a>
          <a class="nav-link" href="misSuscripciones.php">
            <i class="bi bi-calendar-check"></i> Mis Suscripciones
          </a>
          <hr>
          <a class="nav-link text-danger" href="php/logout.php">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
          </a>
        </nav>
      </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="col-lg-9">
      <div class="profile-content p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="coffee-title mb-0">Mis Direcciones</h2>
          <button class="btn btn-primary" onclick="mostrarModalAgregar()">
            <i class="bi bi-plus-circle"></i> Agregar Dirección
          </button>
        </div>
        
        <div id="alertContainer"></div>
        
        <div id="direccionesContainer" class="row">
          <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Agregar/Editar -->
<div class="modal fade" id="modalDireccion" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title coffee-title" id="modalDireccionLabel">Agregar Dirección</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formDireccion">
        <div class="modal-body">
          <input type="hidden" id="direccionId" name="id">
          
          <div class="mb-3">
            <label for="alias" class="form-label">Alias</label>
            <input type="text" class="form-control" id="alias" name="alias" placeholder="Casa, Oficina, etc.">
            <small class="text-muted">Opcional - Ayuda a identificar esta dirección</small>
          </div>
          
          <div class="mb-3">
            <label for="calle" class="form-label">Dirección <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="calle" name="calle" placeholder="Cra 7 #32-16" required>
          </div>
          
          <div class="mb-3">
            <label for="apartamento" class="form-label">Apartamento/Suite</label>
            <input type="text" class="form-control" id="apartamento" name="apartamento" placeholder="Apto 301">
          </div>
          
          <div class="mb-3">
            <label for="instrucciones" class="form-label">Instrucciones de entrega</label>
            <textarea class="form-control" id="instrucciones" name="instrucciones" rows="2" placeholder="Ej: Tocar timbre, dejar con portero..."></textarea>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="ciudad" class="form-label">Ciudad <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="ciudad" name="ciudad" required>
            </div>
            <div class="col-md-6">
              <label for="departamento" class="form-label">Departamento <span class="text-danger">*</span></label>
              <select class="form-select" id="departamento" name="departamento" required>
                <option value="">Seleccione...</option>
                <option value="Antioquia">Antioquia</option>
                <option value="Atlántico">Atlántico</option>
                <option value="Bogotá D.C.">Bogotá D.C.</option>
                <option value="Bolívar">Bolívar</option>
                <option value="Boyacá">Boyacá</option>
                <option value="Caldas">Caldas</option>
                <option value="Cundinamarca">Cundinamarca</option>
                <option value="Meta">Meta</option>
                <option value="Nariño">Nariño</option>
                <option value="Norte de Santander">Norte de Santander</option>
                <option value="Quindío">Quindío</option>
                <option value="Risaralda">Risaralda</option>
                <option value="Santander">Santander</option>
                <option value="Tolima">Tolima</option>
                <option value="Valle del Cauca">Valle del Cauca</option>
              </select>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="codigoPostal" class="form-label">Código Postal <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="codigoPostal" name="codigoPostal" required>
          </div>
          
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="esPredeterminada" name="esPredeterminada">
            <label class="form-check-label" for="esPredeterminada">
              Establecer como dirección predeterminada
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirmar Eliminación</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que deseas eliminar esta dirección?</p>
        <p class="text-muted">Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
          <i class="bi bi-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
// Definir funciones globales ANTES de cargar direcciones.js
function mostrarModalAgregar() {
    if (window.gestorDirecciones) {
        gestorDirecciones.mostrarModalAgregar();
    }
}

function confirmarEliminar() {
    if (window.gestorDirecciones) {
        gestorDirecciones.confirmarEliminar();
    }
}
</script>
<script src="direcciones.js"></script>

</body>
</html>