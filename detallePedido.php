<?php
require_once 'php/check_auth.php';
verificarAutenticacion();

$pedidoId = intval($_GET['id'] ?? 0);
if ($pedidoId <= 0) {
    header('Location: historialOrdenes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle del Pedido - Café en Línea</title>
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
  
  .order-summary {
    background-color: #f8f9fa;
    border-radius: 10px;
  }
  
  .product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
  }
  
  .status-badge {
    border-radius: 20px;
    padding: 8px 12px;
    font-size: 0.9rem;
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
  
  .tracking-step {
    position: relative;
    padding-bottom: 30px;
  }
  
  .tracking-step::before {
    content: '';
    position: absolute;
    left: 17px;
    top: 30px;
    height: calc(100% - 30px);
    width: 3px;
    background-color: #dee2e6;
  }
  
  .tracking-step:last-child::before {
    display: none;
  }
  
  .step-icon {
    width: 36px;
    height: 36px;
    background-color: #dee2e6;
    color: #6c757d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    position: relative;
    z-index: 1;
  }
  
  .step-icon.active {
    background-color: var(--coffee-brown);
    color: white;
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
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="historialOrdenes.php" class="text-decoration-none">Mis Pedidos</a></li>
          <li class="breadcrumb-item active" aria-current="page" id="breadcrumbPedido">Cargando...</li>
        </ol>
      </nav>
      
      <!-- Loading -->
      <div id="loadingDetalle" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Cargando detalles del pedido...</p>
      </div>
      
      <!-- Contenido del pedido -->
      <div id="detalleContenido" class="profile-content p-4 d-none"></div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
const pedidoId = <?php echo $pedidoId; ?>;

function formatPrice(price) {
  return '$' + Number(price).toLocaleString('es-CO');
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ', ' +
         date.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
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

async function cargarDetallePedido() {
  try {
    const response = await fetch(basePath + `php/pedidos_api.php?action=get&id=${pedidoId}`);
    const data = await response.json();
    
    document.getElementById('loadingDetalle').classList.add('d-none');
    
    if (data.success && data.pedido) {
      renderDetalle(data.pedido);
      document.getElementById('detalleContenido').classList.remove('d-none');
    } else {
      alert('Pedido no encontrado');
      window.location.href = 'historialOrdenes.php';
    }
  } catch (e) {
    console.error('Error al cargar pedido:', e);
    alert('Error al cargar el pedido');
    window.location.href = 'historialOrdenes.php';
  }
}

function renderDetalle(pedido) {
  document.getElementById('breadcrumbPedido').textContent = 'Pedido #' + pedido.numeroSeguimiento;
  
  let productosHTML = '';
  pedido.items.forEach(item => {
    const imgSrc = item.imagen || 'images/placeholder.png';
    productosHTML += `
      <div class="d-flex mb-3">
        <img src="${imgSrc}" class="product-image rounded me-3" alt="${item.nombre}" onerror="this.src='images/placeholder.png'">
        <div class="flex-grow-1">
          <h6 class="mb-1">${item.nombre}</h6>
          <p class="text-muted mb-0 small">${item.descripcion ? item.descripcion.substring(0, 60) + '...' : ''}</p>
          <p class="mb-0">${formatPrice(item.precioUnitario)} x ${item.cantidad} = ${formatPrice(item.subtotal)}</p>
        </div>
      </div>`;
  });
  
  const container = document.getElementById('detalleContenido');
  container.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="coffee-title mb-0">Pedido #${pedido.numeroSeguimiento}</h2>
      <span class="status-badge status-${pedido.estado}">${getEstadoTexto(pedido.estado)}</span>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-6 mb-3 mb-md-0">
        <h5>Información del Pedido</h5>
        <table class="table table-borderless">
          <tbody>
            <tr>
              <th scope="row" class="ps-0">Número de pedido:</th>
              <td>${pedido.numeroSeguimiento}</td>
            </tr>
            <tr>
              <th scope="row" class="ps-0">Fecha de pedido:</th>
              <td>${formatDate(pedido.fechaCreacion)}</td>
            </tr>
            <tr>
              <th scope="row" class="ps-0">Método de pago:</th>
              <td>${pedido.metodoPagoTipo || 'No especificado'}</td>
            </tr>
            <tr>
              <th scope="row" class="ps-0">Fecha estimada de entrega:</th>
              <td>${formatDate(pedido.fecha_entrega_estimada)}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="col-md-6">
        <h5>Dirección de Envío</h5>
        <address>
          ${pedido.usuarioNombre}<br>
          ${pedido.calle}${pedido.apartamento ? ', ' + pedido.apartamento : ''}<br>
          ${pedido.ciudad}, ${pedido.departamento}<br>
          Código Postal: ${pedido.codigoPostal}<br>
          ${pedido.usuarioTelefono ? 'Teléfono: ' + pedido.usuarioTelefono : ''}
          ${pedido.instrucciones ? '<br><small class="text-muted">Instrucciones: ' + pedido.instrucciones + '</small>' : ''}
        </address>
      </div>
    </div>
    
    <hr class="my-4">
    
    <h5 class="mb-3">Productos</h5>
    
    <div class="card border mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-8 mb-3 mb-md-0">
            ${productosHTML}
          </div>
          
          <div class="col-md-4">
            <div class="order-summary p-3">
              <h6 class="mb-3">Resumen</h6>
              
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <span>${formatPrice(pedido.subtotal)}</span>
              </div>
              
              <div class="d-flex justify-content-between mb-2">
                <span>Envío</span>
                <span>${pedido.costoEnvio > 0 ? formatPrice(pedido.costoEnvio) : 'Gratis'}</span>
              </div>
              
              <hr>
              
              <div class="d-flex justify-content-between mb-0">
                <span class="fw-bold">Total</span>
                <span class="fw-bold">${formatPrice(pedido.total)}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <h5 class="mb-3">Seguimiento del Pedido</h5>
    
    <div class="card border mb-4">
      <div class="card-body">
        ${generarSeguimiento(pedido.estado, pedido.fechaCreacion)}
      </div>
    </div>
    
    <div class="d-flex justify-content-between">
      <a href="historialOrdenes.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver a mis pedidos</a>
      <div>
        ${pedido.estado === 'pendiente' ? '<button class="btn btn-outline-danger me-2" onclick="cancelarPedido()"><i class="bi bi-x-circle"></i> Cancelar pedido</button>' : ''}
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
      </div>
    </div>`;
}

function generarSeguimiento(estado, fechaCreacion) {
  const fecha = new Date(fechaCreacion);
  
  const pasos = [
    { id: 'pendiente', titulo: 'Pedido Realizado', activo: true, fecha: fechaCreacion },
    { id: 'confirmado', titulo: 'Pago Confirmado', activo: false },
    { id: 'en_proceso', titulo: 'En Preparación', activo: false },
    { id: 'enviado', titulo: 'Enviado', activo: false },
    { id: 'entregado', titulo: 'Entregado', activo: false }
  ];
  
  // Activar pasos según el estado actual
  const estadoIndex = pasos.findIndex(p => p.id === estado);
  if (estadoIndex >= 0) {
    for (let i = 0; i <= estadoIndex; i++) {
      pasos[i].activo = true;
    }
  }
  
  let html = '';
  pasos.forEach(paso => {
    html += `
      <div class="tracking-step d-flex">
        <div class="step-icon ${paso.activo ? 'active' : ''}">
          ${paso.activo ? '<i class="bi bi-check"></i>' : '<i class="bi bi-circle"></i>'}
        </div>
        <div>
          <h6 class="mb-1">${paso.titulo}</h6>
          ${paso.activo && paso.fecha ? '<p class="text-muted mb-1">' + formatDateTime(paso.fecha) + '</p>' : ''}
        </div>
      </div>`;
  });
  
  return html;
}

async function cancelarPedido() {
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
      window.location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  } catch (e) {
    alert('Error al cancelar el pedido');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  cargarDetallePedido();
});
</script>
</body>
</html>