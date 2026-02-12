<?php
require_once 'php/check_auth.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Café en Línea - Mis Suscripciones</title>
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
  
  .subscription-card {
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .subscription-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  
  .subscription-card.active {
    border-color: var(--coffee-brown);
    background-color: #fdfbf7;
  }
  
  .subscription-card.inactive {
    opacity: 0.7;
    background-color: #f8f9fa;
  }
  
  .badge-frequency {
    background-color: var(--coffee-brown);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
  }
  
  .product-mini-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .next-delivery-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
  }
  
  .savings-badge {
    background-color: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 0.75rem;
    font-weight: bold;
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
          <a class="nav-link" href="metodosPago.php">
            <i class="bi bi-credit-card"></i> Métodos de Pago
          </a>
          <a class="nav-link active" href="misSuscripciones.php">
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
          <h2 class="coffee-title mb-0">Mis Suscripciones</h2>
          <a href="planesSuscripcion.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Suscripción
          </a>
        </div>
        
        <div class="alert alert-info">
          <i class="bi bi-gift"></i> <strong>¡Ahorra 10%!</strong> Todas las suscripciones tienen un descuento automático del 10%.
        </div>
        
        <div id="alertContainer"></div>
        
        <!-- Loading -->
        <div id="loadingSuscripciones" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Cargando suscripciones...</p>
        </div>
        
        <!-- Contenedor de suscripciones -->
        <div id="suscripcionesContainer"></div>
        
        <!-- Estado vacío -->
        <div id="estadoVacio" class="text-center py-5 d-none">
          <i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>
          <h4 class="mt-3 text-muted">No tienes suscripciones activas</h4>
          <p class="text-muted">Suscríbete y recibe tu café favorito automáticamente</p>
          <a href="planesSuscripcion.php" class="btn btn-primary mt-2">
            <i class="bi bi-calendar-check"></i> Ver Planes
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Cancelar Suscripción -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Cancelar Suscripción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que deseas cancelar esta suscripción?</p>
        <p class="text-muted">Dejarás de recibir entregas automáticas de tus productos favoritos.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, mantener</button>
        <button type="button" class="btn btn-warning" onclick="confirmarCancelar()">
          <i class="bi bi-x-circle"></i> Sí, cancelar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Productos -->
<div class="modal fade" id="modalProductos" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Productos de la Suscripción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="productosModalBody">
        <!-- Se cargará dinámicamente -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
let suscripcionIdCancelar = null;

function formatPrice(price) {
  return '$' + Number(price).toLocaleString('es-CO');
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' });
}

function getFrecuenciaTexto(frecuencia) {
  const frecuencias = {
    'semanal': 'Semanal',
    'quincenal': 'Quincenal',
    'mensual': 'Mensual'
  };
  return frecuencias[frecuencia] || frecuencia;
}

async function cargarSuscripciones() {
  try {
    const response = await fetch(basePath + 'php/suscripciones_api.php?action=list');
    const data = await response.json();
    
    document.getElementById('loadingSuscripciones').classList.add('d-none');
    
    if (data.success && data.suscripciones && data.suscripciones.length > 0) {
      renderSuscripciones(data.suscripciones);
      document.getElementById('estadoVacio').classList.add('d-none');
    } else {
      document.getElementById('suscripcionesContainer').innerHTML = '';
      document.getElementById('estadoVacio').classList.remove('d-none');
    }
  } catch (e) {
    console.error('Error al cargar suscripciones:', e);
    document.getElementById('loadingSuscripciones').classList.add('d-none');
    document.getElementById('estadoVacio').classList.remove('d-none');
  }
}

function renderSuscripciones(suscripciones) {
  const container = document.getElementById('suscripcionesContainer');
  container.innerHTML = '';
  
  // Separar activas e inactivas
  const activas = suscripciones.filter(s => s.activa == 1);
  const inactivas = suscripciones.filter(s => s.activa == 0);
  
  // Renderizar activas primero
  if (activas.length > 0) {
    activas.forEach(sub => renderSuscripcion(sub, container, true));
  }
  
  // Renderizar inactivas
  if (inactivas.length > 0) {
    if (activas.length > 0) {
      const divider = document.createElement('h5');
      divider.className = 'mt-4 mb-3 text-muted';
      divider.textContent = 'Suscripciones Canceladas';
      container.appendChild(divider);
    }
    inactivas.forEach(sub => renderSuscripcion(sub, container, false));
  }
}

function renderSuscripcion(sub, container, activa) {
  const card = document.createElement('div');
  card.className = `subscription-card ${activa ? 'active' : 'inactive'}`;
  
  // Calcular ahorro (10%)
  const precioOriginal = sub.precioTotal / 0.9;
  const ahorro = precioOriginal - sub.precioTotal;
  
  // Generar HTML de productos (máximo 3)
  let productosHTML = '';
  sub.productos.slice(0, 3).forEach(prod => {
    const imgSrc = prod.imagen || 'images/placeholder.png';
    productosHTML += `
      <div class="d-flex align-items-center mb-2">
        <img src="${imgSrc}" class="product-mini-img me-2" alt="${prod.nombre}" onerror="this.src='images/placeholder.png'">
        <div>
          <small><strong>${prod.nombre}</strong></small><br>
          <small class="text-muted">Cantidad: ${prod.cantidad}</small>
        </div>
      </div>`;
  });
  
  if (sub.totalProductos > 3) {
    productosHTML += `<small class="text-muted">+${sub.totalProductos - 3} producto(s) más</small>`;
  }
  
  card.innerHTML = `
    <div class="row">
      <div class="col-md-8 mb-3 mb-md-0">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h4 class="mb-2">
              Suscripción ${getFrecuenciaTexto(sub.frecuencia)}
              <span class="badge-frequency">${getFrecuenciaTexto(sub.frecuencia)}</span>
              ${!activa ? '<span class="badge bg-secondary ms-2">Cancelada</span>' : ''}
            </h4>
            <p class="text-muted mb-0">
              <i class="bi bi-calendar-event"></i> Inicio: ${formatDate(sub.fechaInicio)}
            </p>
          </div>
          <span class="savings-badge">
            <i class="bi bi-tag-fill"></i> Ahorras ${formatPrice(ahorro)}
          </span>
        </div>
        
        <h6 class="mb-2">Productos (${sub.totalProductos}):</h6>
        ${productosHTML}
        
        <div class="mt-3">
          <button class="btn btn-sm btn-outline-primary" onclick="verProductos(${sub.id})">
            <i class="bi bi-eye"></i> Ver todos los productos
          </button>
        </div>
      </div>
      
      <div class="col-md-4">
        ${activa ? `
          <div class="next-delivery-box mb-3">
            <h6 class="mb-1">Próxima Entrega</h6>
            <p class="mb-0 fs-5 fw-bold">${formatDate(sub.fechaProximoEnvio)}</p>
          </div>
        ` : ''}
        
        <div class="bg-light p-3 rounded mb-3">
          <div class="d-flex justify-content-between mb-2">
            <span>Precio Original:</span>
            <span class="text-decoration-line-through">${formatPrice(precioOriginal)}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Descuento 10%:</span>
            <span class="text-success">-${formatPrice(ahorro)}</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <strong>Total ${getFrecuenciaTexto(sub.frecuencia)}:</strong>
            <strong class="text-primary">${formatPrice(sub.precioTotal)}</strong>
          </div>
        </div>
        
        ${activa ? `
          <div class="d-grid gap-2">
            <button class="btn btn-outline-warning" onclick="cancelarSuscripcion(${sub.id})">
              <i class="bi bi-x-circle"></i> Cancelar Suscripción
            </button>
          </div>
        ` : ''}
      </div>
    </div>`;
  
  container.appendChild(card);
}

function cancelarSuscripcion(id) {
  suscripcionIdCancelar = id;
  new bootstrap.Modal(document.getElementById('modalCancelar')).show();
}

async function confirmarCancelar() {
  if (!suscripcionIdCancelar) return;
  
  try {
    const formData = new FormData();
    formData.append('action', 'cancel');
    formData.append('id', suscripcionIdCancelar);
    
    const response = await fetch(basePath + 'php/suscripciones_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalCancelar')).hide();
      mostrarAlerta('Suscripción cancelada exitosamente', 'success');
      cargarSuscripciones();
    } else {
      mostrarAlerta('Error: ' + data.message, 'danger');
    }
  } catch (e) {
    mostrarAlerta('Error al cancelar suscripción', 'danger');
  }
}

async function verProductos(suscripcionId) {
  try {
    const response = await fetch(basePath + `php/suscripciones_api.php?action=get_products&id=${suscripcionId}`);
    const data = await response.json();
    
    if (data.success && data.productos) {
      const modalBody = document.getElementById('productosModalBody');
      modalBody.innerHTML = '';
      
      data.productos.forEach(prod => {
        const imgSrc = prod.imagen || 'images/placeholder.png';
        const div = document.createElement('div');
        div.className = 'card mb-2';
        div.innerHTML = `
          <div class="card-body">
            <div class="d-flex align-items-center">
              <img src="${imgSrc}" style="width: 80px; height: 80px; object-fit: cover;" class="rounded me-3" alt="${prod.nombre}" onerror="this.src='images/placeholder.png'">
              <div class="flex-grow-1">
                <h6 class="mb-1">${prod.nombre}</h6>
                <p class="mb-1 text-muted">Cantidad: ${prod.cantidad}</p>
                <p class="mb-0"><strong>${formatPrice(prod.precio)} c/u</strong></p>
              </div>
              <div class="text-end">
                <strong>${formatPrice(prod.precio * prod.cantidad)}</strong>
              </div>
            </div>
          </div>`;
        modalBody.appendChild(div);
      });
      
      new bootstrap.Modal(document.getElementById('modalProductos')).show();
    }
  } catch (e) {
    console.error('Error al cargar productos:', e);
  }
}

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
  cargarSuscripciones();
});
</script>
</body>
</html>