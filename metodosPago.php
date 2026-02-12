<?php
require_once 'php/check_auth.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Café en Línea - Métodos de Pago</title>
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
  
  .payment-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 20px;
    margin-bottom: 15px;
  }
  
  .payment-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .payment-icon {
    width: 50px;
    height: 50px;
    background-color: var(--coffee-brown);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }
  
  .card-visa {
    background: linear-gradient(135deg, #1434CB 0%, #2E77D0 100%);
  }
  
  .card-mastercard {
    background: linear-gradient(135deg, #EB001B 0%, #F79E1B 100%);
  }
  
  .card-generic {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>
</head>
<body>
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
          <a class="nav-link" href="misDirecciones.php">
            <i class="bi bi-geo-alt"></i> Mis Direcciones
          </a>
          <a class="nav-link active" href="metodosPago.php">
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
          <h2 class="coffee-title mb-0">Métodos de Pago</h2>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarMetodo">
            <i class="bi bi-plus-circle"></i> Agregar Método
          </button>
        </div>
        
        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i> Estos métodos de pago estarán disponibles durante el proceso de checkout.
        </div>
        
        <div id="alertContainer"></div>
        
        <!-- Loading -->
        <div id="loadingMetodos" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Cargando métodos de pago...</p>
        </div>
        
        <!-- Contenedor de métodos -->
        <div id="metodosContainer"></div>
        
        <!-- Estado vacío -->
        <div id="estadoVacio" class="text-center py-5 d-none">
          <i class="bi bi-credit-card" style="font-size: 4rem; color: #ccc;"></i>
          <h4 class="mt-3 text-muted">No tienes métodos de pago guardados</h4>
          <p class="text-muted">Agrega un método de pago para realizar compras más rápido</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Agregar Método -->
<div class="modal fade" id="modalAgregarMetodo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agregar Método de Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formMetodoPago">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tipo de Método <span class="text-danger">*</span></label>
            <select class="form-select" id="tipoMetodo" name="tipo" required>
              <option value="">Seleccione...</option>
              <option value="tarjeta">Tarjeta de Crédito/Débito</option>
              <option value="contraentrega">Pago Contraentrega</option>
              <option value="transferencia">Transferencia Bancaria</option>
            </select>
          </div>
          
          <!-- Campos para tarjeta (se muestran dinámicamente) -->
          <div id="camposTarjeta" class="d-none">
            <div class="mb-3">
              <label class="form-label">Número de Tarjeta <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="numeroTarjeta" name="numero" maxlength="16" placeholder="1234 5678 9012 3456">
            </div>
            
            <div class="mb-3">
              <label class="form-label">Nombre en la Tarjeta <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombreTarjeta" name="nombre" placeholder="JUAN PEREZ">
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Vencimiento <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="vencimiento" name="vencimiento" placeholder="MM/AA" maxlength="5">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">CVV <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="cvv" maxlength="4" placeholder="123">
              </div>
            </div>
            
            <div class="alert alert-warning small">
              <i class="bi bi-shield-lock"></i> Por seguridad, solo guardaremos los últimos 4 dígitos de tu tarjeta.
            </div>
          </div>
          
          <div id="infoContraentrega" class="d-none alert alert-info">
            <i class="bi bi-cash-coin"></i> Pagarás en efectivo cuando recibas tu pedido.
          </div>
          
          <div id="infoTransferencia" class="d-none alert alert-info">
            <i class="bi bi-bank"></i> Recibirás instrucciones para realizar la transferencia por email.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
        <p>¿Estás seguro de que deseas eliminar este método de pago?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
          <i class="bi bi-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
let metodoIdEliminar = null;

// Mostrar/ocultar campos según tipo
document.getElementById('tipoMetodo').addEventListener('change', function() {
  const tipo = this.value;
  document.getElementById('camposTarjeta').classList.add('d-none');
  document.getElementById('infoContraentrega').classList.add('d-none');
  document.getElementById('infoTransferencia').classList.add('d-none');
  
  if (tipo === 'tarjeta') {
    document.getElementById('camposTarjeta').classList.remove('d-none');
    document.getElementById('numeroTarjeta').required = true;
    document.getElementById('nombreTarjeta').required = true;
    document.getElementById('vencimiento').required = true;
  } else {
    document.getElementById('numeroTarjeta').required = false;
    document.getElementById('nombreTarjeta').required = false;
    document.getElementById('vencimiento').required = false;
    
    if (tipo === 'contraentrega') {
      document.getElementById('infoContraentrega').classList.remove('d-none');
    } else if (tipo === 'transferencia') {
      document.getElementById('infoTransferencia').classList.remove('d-none');
    }
  }
});

// Formatear número de tarjeta
document.getElementById('numeroTarjeta')?.addEventListener('input', function(e) {
  this.value = this.value.replace(/\D/g, '');
});

// Formatear vencimiento MM/AA
document.getElementById('vencimiento')?.addEventListener('input', function(e) {
  let val = this.value.replace(/\D/g, '');
  if (val.length >= 2) {
    this.value = val.substring(0, 2) + '/' + val.substring(2, 4);
  } else {
    this.value = val;
  }
});

async function cargarMetodos() {
  try {
    const response = await fetch(basePath + 'php/metodos_pago_api.php?action=list');
    const data = await response.json();
    
    document.getElementById('loadingMetodos').classList.add('d-none');
    
    if (data.success && data.metodos && data.metodos.length > 0) {
      renderMetodos(data.metodos);
      document.getElementById('estadoVacio').classList.add('d-none');
    } else {
      document.getElementById('metodosContainer').innerHTML = '';
      document.getElementById('estadoVacio').classList.remove('d-none');
    }
  } catch (e) {
    console.error('Error al cargar métodos:', e);
    document.getElementById('loadingMetodos').classList.add('d-none');
    document.getElementById('estadoVacio').classList.remove('d-none');
  }
}

function renderMetodos(metodos) {
  const container = document.getElementById('metodosContainer');
  container.innerHTML = '';
  
  metodos.forEach(metodo => {
    const detalles = metodo.detalles;
    let icono, titulo, descripcion, colorClass;
    
    if (metodo.tipo === 'tarjeta') {
      icono = 'bi-credit-card';
      titulo = 'Tarjeta ' + (detalles.numero || '****');
      descripcion = `${detalles.nombre || 'Titular'}<br>Vence: ${detalles.vencimiento || 'N/A'}`;
      colorClass = 'card-generic';
    } else if (metodo.tipo === 'contraentrega') {
      icono = 'bi-cash-coin';
      titulo = 'Pago Contraentrega';
      descripcion = 'Paga cuando recibas tu pedido';
      colorClass = 'card-generic';
    } else if (metodo.tipo === 'transferencia') {
      icono = 'bi-bank';
      titulo = 'Transferencia Bancaria';
      descripcion = 'Transferencia desde tu banco';
      colorClass = 'card-generic';
    }
    
    const card = document.createElement('div');
    card.className = 'payment-card';
    card.innerHTML = `
      <div class="d-flex align-items-center">
        <div class="payment-icon ${colorClass} me-3">
          <i class="bi ${icono}"></i>
        </div>
        <div class="flex-grow-1">
          <h5 class="mb-1">${titulo}</h5>
          <p class="mb-0 text-muted small">${descripcion}</p>
        </div>
        <div>
          <button class="btn btn-outline-danger btn-sm" onclick="eliminarMetodo(${metodo.id})">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>`;
    
    container.appendChild(card);
  });
}

function eliminarMetodo(id) {
  metodoIdEliminar = id;
  new bootstrap.Modal(document.getElementById('modalEliminar')).show();
}

async function confirmarEliminar() {
  if (!metodoIdEliminar) return;
  
  try {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', metodoIdEliminar);
    
    const response = await fetch(basePath + 'php/metodos_pago_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
      mostrarAlerta('Método de pago eliminado exitosamente', 'success');
      cargarMetodos();
    } else {
      mostrarAlerta('Error: ' + data.message, 'danger');
    }
  } catch (e) {
    mostrarAlerta('Error al eliminar método de pago', 'danger');
  }
}

document.getElementById('formMetodoPago').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const btn = this.querySelector('button[type="submit"]');
  const btnText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
  
  try {
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    const response = await fetch(basePath + 'php/metodos_pago_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalAgregarMetodo')).hide();
      this.reset();
      document.getElementById('camposTarjeta').classList.add('d-none');
      mostrarAlerta('Método de pago agregado exitosamente', 'success');
      cargarMetodos();
    } else {
      mostrarAlerta('Error: ' + data.message, 'danger');
    }
  } catch (e) {
    mostrarAlerta('Error al agregar método de pago', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = btnText;
  }
});

function mostrarAlerta(mensaje, tipo) {
  const alertContainer = document.getElementById('alertContainer');
  alertContainer.innerHTML = `
    <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
      ${mensaje}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
  
  setTimeout(() => {
    alertContainer.innerHTML = '';
  }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
  cargarMetodos();
});
</script>
</body>
</html>