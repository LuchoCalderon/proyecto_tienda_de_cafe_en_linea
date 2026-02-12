<?php
require_once 'php/check_auth.php';
verificarAdmin();
$usuario = obtenerUsuarioActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Pedidos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  body { background-color: #f8f9fa; }
  
  .sidebar {
    background-color: var(--dark-brown);
    color: white;
    min-height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    z-index: 1000;
    transition: margin-left 0.3s ease;
  }
  
  .sidebar.hidden { margin-left: -250px; }
  .sidebar-header { padding: 20px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .sidebar-menu a { color: rgba(255,255,255,0.8); padding: 12px 20px; display: flex; align-items: center; text-decoration: none; transition: all 0.3s; }
  .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background-color: rgba(255,255,255,0.1); }
  .sidebar-menu i { margin-right: 10px; width: 20px; text-align: center; }
  .sidebar-menu .divider { height: 1px; background-color: rgba(255,255,255,0.1); margin: 10px 15px; }
  
  .main-content {
    margin-left: 250px;
    padding: 20px;
    transition: margin-left 0.3s ease;
  }
  
  .main-content.expanded { margin-left: 0; }
  
  .hamburger-btn {
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1001;
    background-color: var(--coffee-brown);
    border: none;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    display: none;
  }
  
  .category-img-preview {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .category-card {
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease;
  }
  
  .category-card:hover {
    transform: translateY(-5px);
  }
  
  @media (max-width: 992px) {
    .sidebar { margin-left: -250px; }
    .sidebar.visible { margin-left: 0; }
    .main-content { margin-left: 0; }
    .hamburger-btn { display: block; }
  }
  
  .sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0,0,0,0.5);
    z-index: 999;
  }
  
  .sidebar-overlay.show { display: block; }
</style>
</head>
<body>
  <button class="hamburger-btn" id="sidebarToggle">
  <i class="bi bi-list"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h5 class="mb-0">Café en Línea</h5>
    <small>Panel de Administración</small>
  </div>
  <div class="sidebar-menu">
    <a href="administrador.php" ><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="admin_products.php"><i class="bi bi-box-seam"></i> Productos</a>
    <a href="admin_orders.php"class="active"><i class="bi bi-bag"></i> Pedidos</a>
    <a href="admin_users.php"><i class="bi bi-people"></i> Clientes</a>
    <a href="admin_categories.php"><i class="bi bi-tags"></i> Categorías</a>
    <div class="divider"></div>
    <a href="home.php"><i class="bi bi-house"></i> Ver Tienda</a>
    <a href="php/logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
  </div>
</nav>

<div class="main-content">
  <h2 class="mb-4">Gestion de Pedidos</h2>
  
  <!-- Filtros -->
  <div class="card border-0 mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <input type="text" class="form-control" id="busquedaPedido" placeholder="Buscar por cliente o # seguimiento...">
        </div>
        <div class="col-md-3">
          <select class="form-select" id="filtroEstado">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="confirmado">Confirmado</option>
            <option value="en_proceso">En proceso</option>
            <option value="enviado">Enviado</option>
            <option value="entregado">Entregado</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-secondary w-100" id="btnBuscarPedido">Buscar</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Tabla -->
  <div class="card border-0">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>N. Seguimiento</th>
              <th>Cliente</th>
              <th>Items</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaPedidos">
            <tr><td colspan="7" class="text-center py-4">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detalle Pedido -->
<div class="modal fade" id="modalPedido" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPedidoTitle">Detalle del Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalPedidoBody">
        <div class="text-center py-4">
          <div class="spinner-border" role="status"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

// Toggle sidebar
document.getElementById('sidebarToggle').addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('visible');
  document.getElementById('sidebarOverlay').classList.toggle('show');
});

const estadoBadge = {
  'pendiente': 'bg-warning text-dark',
  'confirmado': 'bg-info text-dark',
  'en_proceso': 'bg-primary',
  'enviado': 'bg-info',
  'entregado': 'bg-success',
  'cancelado': 'bg-danger'
};

const estadoLabels = {
  'pendiente': 'Pendiente',
  'confirmado': 'Confirmado',
  'en_proceso': 'En proceso',
  'enviado': 'Enviado',
  'entregado': 'Entregado',
  'cancelado': 'Cancelado'
};

async function cargarPedidos() {
  const busqueda = document.getElementById('busquedaPedido').value;
  const estado = document.getElementById('filtroEstado').value;
  let url = basePath + 'php/admin_api.php?resource=pedidos&action=list';
  if (busqueda) url += '&busqueda=' + encodeURIComponent(busqueda);
  if (estado) url += '&estado=' + estado;
  
  const res = await fetch(url);
  const data = await res.json();
  const tbody = document.getElementById('tablaPedidos');
  
  if (data.success && data.data.length > 0) {
    tbody.innerHTML = '';
    data.data.forEach(p => {
      const badge = estadoBadge[p.estado] || 'bg-secondary';
      const label = estadoLabels[p.estado] || p.estado;
      tbody.innerHTML += `
        <tr>
          <td><strong>${p.numeroSeguimiento || '-'}</strong></td>
          <td>${p.clienteNombre}<br><small class="text-muted">${p.clienteEmail}</small></td>
          <td>${p.totalItems}</td>
          <td>$${Number(p.total).toLocaleString('es-CO')}</td>
          <td>
            <select class="form-select form-select-sm" style="width:140px;" onchange="cambiarEstado(${p.id}, this.value)">
              ${Object.keys(estadoLabels).map(e => `<option value="${e}" ${p.estado === e ? 'selected' : ''}>${estadoLabels[e]}</option>`).join('')}
            </select>
          </td>
          <td>${p.fechaCreacion || '-'}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="verPedido(${p.id})"><i class="bi bi-eye"></i></button>
          </td>
        </tr>`;
    });
  } else {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron pedidos</td></tr>';
  }
}

async function cambiarEstado(pedidoId, estado) {
  const formData = new FormData();
  formData.append('resource', 'pedidos');
  formData.append('action', 'update_estado');
  formData.append('id', pedidoId);
  formData.append('estado', estado);
  
  const res = await fetch(basePath + 'php/admin_api.php', { method: 'POST', body: formData });
  const data = await res.json();
  
  if (!data.success) {
    alert(data.message);
    cargarPedidos();
  }
}

async function verPedido(id) {
  const modal = new bootstrap.Modal(document.getElementById('modalPedido'));
  const body = document.getElementById('modalPedidoBody');
  body.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
  modal.show();
  
  const res = await fetch(basePath + 'php/admin_api.php?resource=pedidos&action=get&id=' + id);
  const data = await res.json();
  
  if (data.success) {
    const p = data.data;
    document.getElementById('modalPedidoTitle').textContent = 'Pedido #' + (p['numeroSeguimiento:'] || p.id);
    
    let itemsHTML = '';
    if (p.items) {
      p.items.forEach(item => {
        itemsHTML += `
          <tr>
            <td><img src="${item.productoImagen || 'images/placeholder.png'}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;" onerror="this.src='images/placeholder.png'"></td>
            <td>${item.productoNombre}</td>
            <td>${item.cantidad}</td>
            <td>$${Number(item.precioUnitario).toLocaleString('es-CO')}</td>
            <td>$${Number(item.subtotal).toLocaleString('es-CO')}</td>
          </tr>`;
      });
    }
    
    body.innerHTML = `
      <div class="row mb-3">
        <div class="col-md-6">
          <h6>Cliente</h6>
          <p>${p.clienteNombre}<br>${p.clienteEmail}<br>${p.clienteTelefono || ''}</p>
        </div>
        <div class="col-md-6">
          <h6>Direccion de envio</h6>
          <p>${p.calle || ''} ${p.apartamento || ''}<br>${p.ciudad || ''}, ${p.departamento || ''} ${p.codigoPostal || ''}</p>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-4"><strong>Estado:</strong> <span class="badge ${estadoBadge[p.estado] || 'bg-secondary'}">${estadoLabels[p.estado] || p.estado}</span></div>
        <div class="col-md-4"><strong>Metodo pago:</strong> ${p.metodoPagoTipo || '-'}</div>
        <div class="col-md-4"><strong>Total:</strong> $${Number(p.total).toLocaleString('es-CO')}</div>
      </div>
      <h6>Productos</h6>
      <table class="table table-sm">
        <thead><tr><th></th><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
        <tbody>${itemsHTML}</tbody>
      </table>`;
  } else {
    body.innerHTML = '<div class="alert alert-danger">Error al cargar pedido</div>';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  cargarPedidos();
  document.getElementById('btnBuscarPedido').addEventListener('click', cargarPedidos);
  document.getElementById('filtroEstado').addEventListener('change', cargarPedidos);
  document.getElementById('busquedaPedido').addEventListener('keypress', e => { if (e.key === 'Enter') cargarPedidos(); });
});
</script>
</body>
</html>
