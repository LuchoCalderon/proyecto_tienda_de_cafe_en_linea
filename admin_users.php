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
<title>Admin - Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  body { background-color: #f8f9fa; }
  
  /* Sidebar */
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
  
  .sidebar-header {
    padding: 20px 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
  }
  
  .sidebar-menu a {
    color: rgba(255,255,255,0.8);
    padding: 12px 20px;
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: all 0.3s;
  }
  
  .sidebar-menu a:hover,
  .sidebar-menu a.active {
    color: white;
    background-color: rgba(255,255,255,0.1);
  }
  
  .sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
  }
  
  .sidebar-menu .divider {
    height: 1px;
    background-color: rgba(255,255,255,0.1);
    margin: 10px 15px;
  }
  
  /* Main content */
  .main-content {
    margin-left: 250px;
    padding: 20px;
    transition: margin-left 0.3s ease;
  }
  
  /* Hamburger button */
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
  
  /* User avatars */
  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
  }
  
  .user-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--coffee-brown);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1rem;
  }
  
  /* Responsive */
  @media (max-width: 992px) {
    .sidebar {
      margin-left: -250px;
    }
    
    .sidebar.visible {
      margin-left: 0;
    }
    
    .main-content {
      margin-left: 0;
    }
    
    .hamburger-btn {
      display: block;
    }
  }
  
  /* Overlay para cerrar sidebar en móvil */
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
  
  .sidebar-overlay.show {
    display: block;
  }
</style>
</head>
<body>
<!-- Botón hamburguesa -->
<button class="hamburger-btn" id="sidebarToggle">
  <i class="bi bi-list"></i>
</button>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h5 class="mb-0">Café en Línea</h5>
    <small>Panel de Administración</small>
  </div>
  <div class="sidebar-menu">
    <a href="administrador.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="admin_products.php"><i class="bi bi-box-seam"></i> Productos</a>
    <a href="admin_orders.php"><i class="bi bi-bag"></i> Pedidos</a>
    <a href="admin_users.php" class="active"><i class="bi bi-people"></i> Clientes</a>
    <a href="admin_categories.php"><i class="bi bi-tags"></i> Categorías</a>
    <div class="divider"></div>
    <a href="home.php"><i class="bi bi-house"></i> Ver Tienda</a>
    <a href="php/logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
  </div>
</nav>

<div class="main-content">
  <h2 class="mb-4">Gestion de Usuarios</h2>
  
  <!-- Stats rapidas -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 text-center p-3">
        <h3 id="statTotal" class="mb-0">-</h3>
        <small class="text-muted">Total usuarios</small>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 text-center p-3">
        <h3 id="statActivos" class="mb-0 text-success">-</h3>
        <small class="text-muted">Activos</small>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 text-center p-3">
        <h3 id="statInactivos" class="mb-0 text-danger">-</h3>
        <small class="text-muted">Inactivos</small>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 text-center p-3">
        <h3 id="statAdmins" class="mb-0 text-primary">-</h3>
        <small class="text-muted">Administradores</small>
      </div>
    </div>
  </div>
  
  <!-- Busqueda -->
  <div class="card border-0 mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <input type="text" class="form-control" id="busquedaUsuario" placeholder="Buscar por nombre o email...">
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-secondary w-100" id="btnBuscarUser">Buscar</button>
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
              <th>Usuario</th>
              <th>Email</th>
              <th>Telefono</th>
              <th>Rol</th>
              <th>Pedidos</th>
              <th>Puntos</th>
              <th>Registro</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaUsuarios">
            <tr><td colspan="9" class="text-center py-4">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle del Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalUsuarioBody">
        <div class="text-center py-4"><div class="spinner-border" role="status"></div></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

// Toggle sidebar
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarOverlay = document.getElementById('sidebarOverlay');

sidebarToggle.addEventListener('click', function() {
  sidebar.classList.toggle('visible');
  sidebarOverlay.classList.toggle('show');
});

sidebarOverlay.addEventListener('click', function() {
  sidebar.classList.remove('visible');
  sidebarOverlay.classList.remove('show');
});

// Cerrar sidebar al hacer click en un enlace (móvil)
document.querySelectorAll('.sidebar-menu a').forEach(link => {
  link.addEventListener('click', function() {
    if (window.innerWidth < 992) {
      sidebar.classList.remove('visible');
      sidebarOverlay.classList.remove('show');
    }
  });
});

async function cargarUsuarios() {
  const busqueda = document.getElementById('busquedaUsuario').value;
  let url = basePath + 'php/admin_api.php?resource=usuarios&action=list';
  if (busqueda) url += '&busqueda=' + encodeURIComponent(busqueda);
  
  const res = await fetch(url);
  const data = await res.json();
  const tbody = document.getElementById('tablaUsuarios');
  
  if (data.success && data.data.length > 0) {
    // Stats
    let activos = 0, inactivos = 0, admins = 0;
    data.data.forEach(u => {
      if (u.activo == 1) activos++; else inactivos++;
      if (u.rol === 'administrador') admins++;
    });
    document.getElementById('statTotal').textContent = data.data.length;
    document.getElementById('statActivos').textContent = activos;
    document.getElementById('statInactivos').textContent = inactivos;
    document.getElementById('statAdmins').textContent = admins;
    
    tbody.innerHTML = '';
    data.data.forEach(u => {
      const avatarHTML = u.avatar 
        ? `<img src="${u.avatar}" class="user-avatar" onerror="this.outerHTML='<div class=user-avatar-placeholder>${u.nombre.charAt(0).toUpperCase()}</div>'">`
        : `<div class="user-avatar-placeholder">${u.nombre.charAt(0).toUpperCase()}</div>`;
      
      tbody.innerHTML += `
        <tr>
          <td>
            <div class="d-flex align-items-center">
              ${avatarHTML}
              <span class="ms-2">${u.nombre}</span>
            </div>
          </td>
          <td>${u.email}</td>
          <td>${u.telefono || '-'}</td>
          <td><span class="badge ${u.rol === 'administrador' ? 'bg-primary' : 'bg-secondary'}">${u.rol}</span></td>
          <td>${u.totalPedidos || 0}</td>
          <td>${u.puntosLealtad || 0}</td>
          <td>${u.fecha_registro ? new Date(u.fecha_registro).toLocaleDateString('es-CO') : '-'}</td>
          <td>
            <span class="badge ${u.activo == 1 ? 'bg-success' : 'bg-danger'}">${u.activo == 1 ? 'Activo' : 'Inactivo'}</span>
          </td>
          <td>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" onclick="verUsuario(${u.id})"><i class="bi bi-eye"></i></button>
              <button class="btn btn-outline-${u.activo == 1 ? 'warning' : 'success'}" onclick="toggleUsuario(${u.id})" title="${u.activo == 1 ? 'Desactivar' : 'Activar'}">
                <i class="bi bi-${u.activo == 1 ? 'person-slash' : 'person-check'}"></i>
              </button>
            </div>
          </td>
        </tr>`;
    });
  } else {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No se encontraron usuarios</td></tr>';
  }
}

async function toggleUsuario(id) {
  if (!confirm('Cambiar el estado de este usuario?')) return;
  
  const formData = new FormData();
  formData.append('resource', 'usuarios');
  formData.append('action', 'toggle');
  formData.append('id', id);
  
  const res = await fetch(basePath + 'php/admin_api.php', { method: 'POST', body: formData });
  const data = await res.json();
  
  if (data.success) {
    cargarUsuarios();
  } else {
    alert(data.message);
  }
}

async function verUsuario(id) {
  const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
  const body = document.getElementById('modalUsuarioBody');
  body.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
  modal.show();
  
  const res = await fetch(basePath + 'php/admin_api.php?resource=usuarios&action=get&id=' + id);
  const data = await res.json();
  
  if (data.success) {
    const u = data.data;
    body.innerHTML = `
      <div class="text-center mb-3">
        ${u.avatar ? `<img src="${u.avatar}" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;">` : `<div class="user-avatar-placeholder mx-auto mb-2" style="width:80px;height:80px;font-size:2rem;">${u.nombre.charAt(0).toUpperCase()}</div>`}
        <h5>${u.nombre}</h5>
        <p class="text-muted">${u.email}</p>
      </div>
      <table class="table table-sm">
        <tr><td><strong>Telefono:</strong></td><td>${u.telefono || '-'}</td></tr>
        <tr><td><strong>Estado:</strong></td><td><span class="badge ${u.activo == 1 ? 'bg-success' : 'bg-danger'}">${u.activo == 1 ? 'Activo' : 'Inactivo'}</span></td></tr>
        <tr><td><strong>Registro:</strong></td><td>${u.fecha_registro || '-'}</td></tr>
        <tr><td><strong>Puntos lealtad:</strong></td><td>${u.puntosLealtad || 0}</td></tr>
        <tr><td><strong>Ultima compra:</strong></td><td>${u.fecha_ultima_Compra || 'Sin compras'}</td></tr>
      </table>`;
  } else {
    body.innerHTML = '<div class="alert alert-danger">Error al cargar usuario</div>';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  cargarUsuarios();
  document.getElementById('btnBuscarUser').addEventListener('click', cargarUsuarios);
  document.getElementById('busquedaUsuario').addEventListener('keypress', e => { if (e.key === 'Enter') cargarUsuarios(); });
});
</script>
</body>
</html>