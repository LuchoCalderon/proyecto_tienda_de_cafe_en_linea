<?php
require_once 'php/check_auth.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Café en Línea - Mis Pedidos</title>
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
  
  .order-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
  }
  
  .order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  
  .status-badge {
    border-radius: 20px;
    padding: 5px 10px;
    font-size: 0.8rem;
    font-weight: 600;
  }
  
  .status-entregado {
    background-color: #d1e7dd;
    color: #0f5132;
  }
  
  .status-pendiente {
    background-color: #fff3cd;
    color: #664d03;
  }
  
  .status-confirmado {
    background-color: #cff4fc;
    color: #055160;
  }
  
  .status-en_proceso {
    background-color: #e7d4ff;
    color: #59359a;
  }
  
  .status-enviado {
    background-color: #cfe2ff;
    color: #084298;
  }
  
  .status-cancelado {
    background-color: #f8d7da;
    color: #842029;
  }
  
  .product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
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
          <a class="nav-link active" href="historialOrdenes.php">
            <i class="bi bi-box-seam"></i> Mis Pedidos
          </a>
          <a class="nav-link" href="misDirecciones.php">
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
          <h2 class="coffee-title mb-0">Mis Pedidos</h2>
          <select class="form-select w-auto" id="filtroEstado">
            <option value="">Todos los pedidos</option>
            <option value="pendiente">Pendientes</option>
            <option value="confirmado">Confirmados</option>
            <option value="en_proceso">En Proceso</option>
            <option value="enviado">Enviados</option>
            <option value="entregado">Entregados</option>
            <option value="cancelado">Cancelados</option>
          </select>
        </div>
        
        <!-- Loading -->
        <div id="loadingPedidos" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Cargando pedidos...</p>
        </div>
        
        <!-- Contenedor de pedidos -->
        <div id="pedidosContainer"></div>
        
        <!-- Estado vacío -->
        <div id="estadoVacio" class="text-center py-5 d-none">
          <i class="bi bi-box-seam" style="font-size: 4rem; color: #ccc;"></i>
          <h4 class="mt-3 text-muted">No tienes pedidos</h4>
          <p class="text-muted">Comienza a explorar nuestro catálogo</p>
          <a href="catalogo.php" class="btn btn-primary mt-2">Ir al catálogo</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

function formatPrice(price) {
  return '$' + Number(price).toLocaleString('es-CO');
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function getEstadoTexto(estado) {
  const estados = {
    'pendiente': 'Pendiente',
    'confirmado': 'Confirmado',
    'en_proceso': 'En Proceso',
    'enviado': 'Enviado',
    'entregado': 'Entregado',
    'cancelado': 'Cancelado'
  };
  return estados[estado] || estado;
}

async function cargarPedidos() {
  const filtroEstado = document.getElementById('filtroEstado').value;
  let url = basePath + 'php/pedidos_api.php?action=list';
  if (filtroEstado) url += '&estado=' + filtroEstado;
  
  try {
    const response = await fetch(url);
    const data = await response.json();
    
    document.getElementById('loadingPedidos').classList.add('d-none');
    
    if (data.success && data.pedidos && data.pedidos.length > 0) {
      renderPedidos(data.pedidos);
      document.getElementById('estadoVacio').classList.add('d-none');
    } else {
      document.getElementById('pedidosContainer').innerHTML = '';
      document.getElementById('estadoVacio').classList.remove('d-none');
    }
  } catch (e) {
    console.error('Error al cargar pedidos:', e);
    document.getElementById('loadingPedidos').classList.add('d-none');
    document.getElementById('estadoVacio').classList.remove('d-none');
  }
}

function renderPedidos(pedidos) {
  const container = document.getElementById('pedidosContainer');
  container.innerHTML = '';
  
  pedidos.forEach(pedido => {
    const card = document.createElement('div');
    card.className = 'card order-card mb-4';
    
    let itemsHTML = '';
    pedido.items.forEach((item, index) => {
      if (index < 3) {
        const imgSrc = item.imagen || 'images/placeholder.png';
        itemsHTML += `
          <div class="d-flex mb-2">
            <img src="${imgSrc}" class="product-image rounded me-3" alt="${item.nombre}" onerror="this.src='images/placeholder.png'">
            <div>
              <p class="mb-0">${item.nombre}</p>
              <small class="text-muted">Cantidad: ${item.cantidad}</small>
            </div>
          </div>`;
      }
    });
    
    if (pedido.totalItems > 3) {
      itemsHTML += `<small class="text-muted">Y ${pedido.totalItems - 3} producto(s) más...</small>`;
    }
    
    card.innerHTML = `
      <div class="card-header bg-white py-3">
        <div class="row align-items-center">
          <div class="col-md-3 mb-2 mb-md-0">
            <span class="text-muted">Pedido #</span>
            <span class="fw-bold">${pedido.numeroSeguimiento}</span>
          </div>
          <div class="col-md-3 mb-2 mb-md-0">
            <span class="text-muted">Fecha:</span>
            <span>${formatDate(pedido.fechaCreacion)}</span>
          </div>
          <div class="col-md-3 mb-2 mb-md-0">
            <span class="text-muted">Total:</span>
            <span class="fw-bold">${formatPrice(pedido.total)}</span>
          </div>
          <div class="col-md-3 text-md-end">
            <span class="status-badge status-${pedido.estado}">${getEstadoTexto(pedido.estado)}</span>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8 mb-3 mb-md-0">
            <h6 class="mb-3">Productos (${pedido.totalItems})</h6>
            ${itemsHTML}
          </div>
          <div class="col-md-4">
            <h6 class="mb-3">Información de envío</h6>
            <p class="mb-1">${pedido.calle || 'Dirección no disponible'}</p>
            <p class="mb-1">${pedido.ciudad || ''}, ${pedido.departamento || ''}</p>
            ${pedido.estado === 'entregado' ? `<p class="mt-2"><strong>Entregado</strong></p>` : ''}
            ${pedido.estado === 'enviado' ? `<p class="mt-2"><strong>En camino</strong></p>` : ''}
          </div>
        </div>
      </div>
      <div class="card-footer bg-white d-flex justify-content-between py-3">
        <a href="detallePedido.php?id=${pedido.id}" class="btn btn-outline-secondary">Ver detalles</a>
        <div>
          ${pedido.estado === 'pendiente' ? `<button class="btn btn-outline-danger me-2" onclick="cancelarPedido(${pedido.id})">Cancelar</button>` : ''}
          <button class="btn btn-primary" onclick="comprarNuevamente(${pedido.id})">Comprar de nuevo</button>
        </div>
      </div>`;
    
    container.appendChild(card);
  });
}

async function cancelarPedido(pedidoId) {
  if (!confirm('¿Estás seguro de que deseas cancelar este pedido?')) return;
  
  try {
    const formData = new FormData();
    formData.append('action', 'cancel');
    formData.append('id', pedidoId);
    
    const response = await fetch(basePath + 'php/pedidos_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    
    if (data.success) {
      alert('Pedido cancelado exitosamente');
      cargarPedidos();
    } else {
      alert('Error: ' + data.message);
    }
  } catch (e) {
    alert('Error al cancelar el pedido');
  }
}

function comprarNuevamente(pedidoId) {
  // Aquí puedes implementar la lógica para agregar los productos del pedido al carrito
  alert('Función en desarrollo: Los productos serán agregados al carrito');
}

document.addEventListener('DOMContentLoaded', function() {
  cargarPedidos();
  
  document.getElementById('filtroEstado').addEventListener('change', function() {
    document.getElementById('loadingPedidos').classList.remove('d-none');
    document.getElementById('pedidosContainer').innerHTML = '';
    cargarPedidos();
  });
});
</script>
</body>
</html>