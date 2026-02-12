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
<title>Notificaciones - Administración</title>
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
  
  .notification-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 4px solid #ccc;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
  }
  
  .notification-card:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .notification-card.unread {
    background: #fff8e1;
    border-left-color: var(--coffee-brown);
  }
  
  .notification-card.success { border-left-color: #28a745; }
  .notification-card.warning { border-left-color: #ffc107; }
  .notification-card.danger { border-left-color: #dc3545; }
  .notification-card.info { border-left-color: #17a2b8; }
  
  .filter-tabs {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
  }
  
  .filter-tabs .btn {
    margin-right: 10px;
    margin-bottom: 10px;
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

<nav class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h5 class="mb-0">Café en Línea</h5>
    <small>Panel de Administración</small>
  </div>
  <div class="sidebar-menu">
    <a href="administrador.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="admin_products.php"><i class="bi bi-box-seam"></i> Productos</a>
    <a href="admin_orders.php"><i class="bi bi-bag"></i> Pedidos</a>
    <a href="admin_users.php"><i class="bi bi-people"></i> Clientes</a>
    <a href="admin_categories.php"><i class="bi bi-tags"></i> Categorías</a>
    <a href="admin_notifications.php" class="active"><i class="bi bi-bell"></i> Notificaciones</a>
    <div class="divider"></div>
    <a href="home.php"><i class="bi bi-house"></i> Ver Tienda</a>
    <a href="php/logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
  </div>
</nav>

<div class="main-content" id="mainContent">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Notificaciones</h2>
        <p class="text-muted mb-0">Gestiona todas tus notificaciones</p>
      </div>
      <button class="btn btn-outline-secondary" onclick="marcarTodasLeidas()">
        <i class="bi bi-check-all"></i> Marcar todas como leídas
      </button>
    </div>
    
    <!-- Filtros -->
    <div class="filter-tabs">
      <button class="btn btn-sm btn-primary" onclick="filtrar('todas')">
        Todas <span class="badge bg-white text-primary ms-1" id="countTodas">0</span>
      </button>
      <button class="btn btn-sm btn-outline-primary" onclick="filtrar('no_leidas')">
        No leídas <span class="badge bg-primary ms-1" id="countNoLeidas">0</span>
      </button>
      <button class="btn btn-sm btn-outline-success" onclick="filtrar('pedido')">
        <i class="bi bi-bag"></i> Pedidos <span class="badge bg-success ms-1" id="countPedidos">0</span>
      </button>
      <button class="btn btn-sm btn-outline-warning" onclick="filtrar('stock')">
        <i class="bi bi-exclamation-triangle"></i> Stock <span class="badge bg-warning ms-1" id="countStock">0</span>
      </button>
      <button class="btn btn-sm btn-outline-info" onclick="filtrar('cliente')">
        <i class="bi bi-person"></i> Clientes <span class="badge bg-info ms-1" id="countClientes">0</span>
      </button>
    </div>
    
    <!-- Lista de notificaciones -->
    <div id="notificacionesContainer">
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Cargando notificaciones...</p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
let filtroActual = 'todas';

// Toggle sidebar
document.getElementById('sidebarToggle').addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('visible');
  document.getElementById('sidebarOverlay').classList.toggle('show');
});

document.getElementById('sidebarOverlay').addEventListener('click', function() {
  document.getElementById('sidebar').classList.remove('visible');
  this.classList.remove('show');
});

document.querySelectorAll('.sidebar-menu a').forEach(link => {
  link.addEventListener('click', function() {
    if (window.innerWidth < 992) {
      document.getElementById('sidebar').classList.remove('visible');
      document.getElementById('sidebarOverlay').classList.remove('show');
    }
  });
});

async function cargarNotificaciones() {
  try {
    let url = basePath + 'php/notificaciones_api.php?action=list&limite=100';
    if (filtroActual === 'no_leidas') {
      url += '&no_leidas=1';
    }
    
    const res = await fetch(url);
    const data = await res.json();
    
    const container = document.getElementById('notificacionesContainer');
    
    if (data.success && data.data.length > 0) {
      let notificaciones = data.data;
      
      // Filtrar por tipo si es necesario
      if (['pedido', 'stock', 'cliente', 'suscripcion'].includes(filtroActual)) {
        notificaciones = notificaciones.filter(n => n.tipo === filtroActual);
      }
      
      if (notificaciones.length > 0) {
        renderNotificaciones(notificaciones);
      } else {
        mostrarVacio();
      }
    } else {
      mostrarVacio();
    }
    
    // Actualizar contadores
    actualizarContadores();
    
  } catch (e) {
    console.error('Error:', e);
  }
}

function renderNotificaciones(notificaciones) {
  const container = document.getElementById('notificacionesContainer');
  container.innerHTML = '';
  
  notificaciones.forEach(notif => {
    const card = document.createElement('div');
    card.className = `notification-card ${notif.color} ${notif.leida == 0 ? 'unread' : ''}`;
    card.onclick = () => clickNotificacion(notif);
    
    const tiempoRelativo = calcularTiempoRelativo(notif.fechaCreacion);
    
    card.innerHTML = `
      <div class="d-flex">
        <div class="me-3">
          <i class="${notif.icono} fs-3" style="color: var(--coffee-brown);"></i>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-0">${notif.titulo}</h6>
            <div>
              ${notif.leida == 0 ? '<span class="badge bg-primary">Nueva</span>' : ''}
              <button class="btn btn-sm btn-link text-danger" onclick="eliminarNotificacion(event, ${notif.id})" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          <p class="mb-2 text-muted">${notif.mensaje}</p>
          <small class="text-muted">
            <i class="bi bi-clock"></i> ${tiempoRelativo}
          </small>
        </div>
      </div>`;
    
    container.appendChild(card);
  });
}

function mostrarVacio() {
  const container = document.getElementById('notificacionesContainer');
  container.innerHTML = `
    <div class="text-center py-5">
      <i class="bi bi-bell-slash" style="font-size:4rem;color:#ccc;"></i>
      <h4 class="mt-3 text-muted">No hay notificaciones</h4>
      <p class="text-muted">Todas las notificaciones ${filtroActual === 'no_leidas' ? 'no leídas' : ''} aparecerán aquí</p>
    </div>`;
}

async function clickNotificacion(notif) {
  if (notif.leida == 0) {
    await marcarLeida(notif.id);
  }
  
  if (notif.enlace) {
    window.location.href = basePath + notif.enlace;
  }
}

async function marcarLeida(id) {
  try {
    const formData = new FormData();
    formData.append('action', 'marcar_leida');
    formData.append('id', id);
    
    await fetch(basePath + 'php/notificaciones_api.php', {
      method: 'POST',
      body: formData
    });
  } catch (e) {
    console.error('Error:', e);
  }
}

async function marcarTodasLeidas() {
  if (!confirm('¿Marcar todas las notificaciones como leídas?')) return;
  
  try {
    const formData = new FormData();
    formData.append('action', 'marcar_todas_leidas');
    
    const res = await fetch(basePath + 'php/notificaciones_api.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await res.json();
    if (data.success) {
      cargarNotificaciones();
    }
  } catch (e) {
    console.error('Error:', e);
  }
}

async function eliminarNotificacion(event, id) {
  event.stopPropagation();
  
  if (!confirm('¿Eliminar esta notificación?')) return;
  
  try {
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);
    
    const res = await fetch(basePath + 'php/notificaciones_api.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await res.json();
    if (data.success) {
      cargarNotificaciones();
    }
  } catch (e) {
    console.error('Error:', e);
  }
}

function filtrar(tipo) {
  filtroActual = tipo;
  
  // Actualizar botones activos
  document.querySelectorAll('.filter-tabs .btn').forEach(btn => {
    btn.className = 'btn btn-sm btn-outline-primary';
  });
  event.target.closest('button').className = 'btn btn-sm btn-primary';
  
  cargarNotificaciones();
}

async function actualizarContadores() {
  try {
    const res = await fetch(basePath + 'php/notificaciones_api.php?action=count');
    const data = await res.json();
    
    if (data.success) {
      // Contar todas
      const res2 = await fetch(basePath + 'php/notificaciones_api.php?action=list&limite=1000');
      const data2 = await res2.json();
      
      if (data2.success) {
        document.getElementById('countTodas').textContent = data2.data.length;
        document.getElementById('countNoLeidas').textContent = data.total;
        
        // Contar por tipo
        const porTipo = {
          pedido: 0,
          stock: 0,
          cliente: 0
        };
        
        data2.data.forEach(n => {
          if (porTipo.hasOwnProperty(n.tipo)) {
            porTipo[n.tipo]++;
          }
        });
        
        document.getElementById('countPedidos').textContent = porTipo.pedido;
        document.getElementById('countStock').textContent = porTipo.stock;
        document.getElementById('countClientes').textContent = porTipo.cliente;
      }
    }
  } catch (e) {
    console.error('Error:', e);
  }
}

function calcularTiempoRelativo(fecha) {
  const ahora = new Date();
  const fechaNotif = new Date(fecha);
  const diff = Math.floor((ahora - fechaNotif) / 1000);
  
  if (diff < 60) return 'Hace un momento';
  if (diff < 3600) return `Hace ${Math.floor(diff / 60)} minutos`;
  if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} horas`;
  if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} días`;
  
  return fechaNotif.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
}

document.addEventListener('DOMContentLoaded', function() {
  cargarNotificaciones();
  
  // Actualizar cada 30 segundos
  setInterval(() => {
    cargarNotificaciones();
  }, 30000);
});
</script>
</body>
</html>