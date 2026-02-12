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
<title>Admin - Productos</title>
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
  
  /* Product image */
  .product-img-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
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
    <a href="admin_products.php" class="active"><i class="bi bi-box-seam"></i> Productos</a>
    <a href="admin_orders.php"><i class="bi bi-bag"></i> Pedidos</a>
    <a href="admin_users.php"><i class="bi bi-people"></i> Clientes</a>
    <a href="admin_categories.php"><i class="bi bi-tags"></i> Categorías</a>
    <div class="divider"></div>
    <a href="home.php"><i class="bi bi-house"></i> Ver Tienda</a>
    <a href="php/logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
  </div>
</nav>

<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion de Productos</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto" onclick="abrirModalCrear()">
      <i class="bi bi-plus-circle"></i> Nuevo Producto
    </button>
  </div>
  
  <!-- Filtros -->
  <div class="card border-0 mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <input type="text" class="form-control" id="busquedaProducto" placeholder="Buscar por nombre...">
        </div>
        <div class="col-md-3">
          <select class="form-select" id="filtroCategoria">
            <option value="">Todas las categorias</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-secondary w-100" id="btnBuscarProd">Buscar</button>
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
              <th>ID</th>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Categoria</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaProductos">
            <tr><td colspan="8" class="text-center py-4">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalProductoTitle">Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="modalAlert" class="alert d-none"></div>
        <form id="formProducto" enctype="multipart/form-data">
          <input type="hidden" name="id" id="prodId">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre *</label>
              <input type="text" class="form-control" name="nombre" id="prodNombre" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Categoria *</label>
              <select class="form-select" name="categoriaId" id="prodCategoria" required>
                <option value="">Seleccionar...</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <textarea class="form-control" name="descripcion" id="prodDescripcion" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Precio *</label>
              <input type="number" class="form-control" name="precio" id="prodPrecio" min="0" step="100" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Stock disponible *</label>
              <input type="number" class="form-control" name="stockDisponible" id="prodStock" min="0" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Imagen</label>
              <input type="file" class="form-control" name="imagen" id="prodImagen" accept="image/*">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="destacado" id="prodDestacado" value="1">
                <label class="form-check-label" for="prodDestacado">Producto destacado</label>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="activo" id="prodActivo" value="1" checked>
                <label class="form-check-label" for="prodActivo">Activo (visible en catalogo)</label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarProducto">
          <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
          Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

let modoEdicion = false;

async function cargarCategorias() {
  const res = await fetch(basePath + 'php/admin_api.php?resource=categorias&action=list');
  const data = await res.json();
  if (data.success) {
    const sel1 = document.getElementById('filtroCategoria');
    const sel2 = document.getElementById('prodCategoria');
    data.data.forEach(c => {
      sel1.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
      sel2.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
    });
  }
}

async function cargarProductos() {
  const busqueda = document.getElementById('busquedaProducto').value;
  const catId = document.getElementById('filtroCategoria').value;
  let url = basePath + 'php/admin_api.php?resource=productos&action=list';
  if (busqueda) url += '&busqueda=' + encodeURIComponent(busqueda);
  if (catId) url += '&categoriaId=' + catId;
  
  const res = await fetch(url);
  const data = await res.json();
  const tbody = document.getElementById('tablaProductos');
  
  if (data.success && data.data.length > 0) {
    tbody.innerHTML = '';
    data.data.forEach(p => {
      tbody.innerHTML += `
        <tr>
          <td>${p.id}</td>
          <td><img src="${p.imagen || 'images/placeholder.png'}" class="product-img-thumb" onerror="this.src='images/placeholder.png'"></td>
          <td><strong>${p.nombre}</strong></td>
          <td>${p.categoriaNombre || '-'}</td>
          <td>$${Number(p.precio).toLocaleString('es-CO')}</td>
          <td>${p.stockDisponible <= 5 ? '<span class="text-danger fw-bold">' + p.stockDisponible + '</span>' : p.stockDisponible}</td>
          <td>
            <span class="badge ${p.activo == 1 ? 'bg-success' : 'bg-secondary'}">${p.activo == 1 ? 'Activo' : 'Inactivo'}</span>
            ${p.destacado == 1 ? '<span class="badge bg-warning text-dark ms-1">Destacado</span>' : ''}
          </td>
          <td>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" onclick="editarProducto(${p.id})"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-outline-${p.activo == 1 ? 'warning' : 'success'}" onclick="toggleProducto(${p.id})">
                <i class="bi bi-${p.activo == 1 ? 'eye-slash' : 'eye'}"></i>
              </button>
            </div>
          </td>
        </tr>`;
    });
  } else {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron productos</td></tr>';
  }
}

function abrirModalCrear() {
  modoEdicion = false;
  document.getElementById('modalProductoTitle').textContent = 'Nuevo Producto';
  document.getElementById('formProducto').reset();
  document.getElementById('prodId').value = '';
  document.getElementById('prodActivo').checked = true;
  document.getElementById('modalAlert').classList.add('d-none');
}

async function editarProducto(id) {
  modoEdicion = true;
  const res = await fetch(basePath + 'php/admin_api.php?resource=productos&action=get&id=' + id);
  const data = await res.json();
  if (data.success) {
    const p = data.data;
    document.getElementById('modalProductoTitle').textContent = 'Editar Producto';
    document.getElementById('prodId').value = p.id;
    document.getElementById('prodNombre').value = p.nombre;
    document.getElementById('prodDescripcion').value = p.descripcion;
    document.getElementById('prodPrecio').value = p.precio;
    document.getElementById('prodStock').value = p.stockDisponible;
    document.getElementById('prodCategoria').value = p.categoriaId;
    document.getElementById('prodDestacado').checked = p.destacado == 1;
    document.getElementById('prodActivo').checked = p.activo == 1;
    document.getElementById('modalAlert').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalProducto')).show();
  }
}

async function toggleProducto(id) {
  const formData = new FormData();
  formData.append('resource', 'productos');
  formData.append('action', 'toggle');
  formData.append('id', id);
  await fetch(basePath + 'php/admin_api.php', { method: 'POST', body: formData });
  cargarProductos();
}

document.addEventListener('DOMContentLoaded', function() {
  cargarCategorias();
  cargarProductos();
  
  document.getElementById('btnBuscarProd').addEventListener('click', cargarProductos);
  document.getElementById('busquedaProducto').addEventListener('keypress', e => { if (e.key === 'Enter') cargarProductos(); });
  
  document.getElementById('btnGuardarProducto').addEventListener('click', async function() {
    const btn = this;
    const spinner = btn.querySelector('.spinner-border');
    const alertDiv = document.getElementById('modalAlert');
    btn.disabled = true;
    spinner.classList.remove('d-none');
    alertDiv.classList.add('d-none');
    
    const form = document.getElementById('formProducto');
    const formData = new FormData(form);
    formData.append('resource', 'productos');
    formData.append('action', modoEdicion ? 'update' : 'create');
    formData.set('destacado', document.getElementById('prodDestacado').checked ? '1' : '0');
    formData.set('activo', document.getElementById('prodActivo').checked ? '1' : '0');
    
    try {
      const res = await fetch(basePath + 'php/admin_api.php', { method: 'POST', body: formData });
      const data = await res.json();
      
      alertDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
      alertDiv.classList.add(data.success ? 'alert-success' : 'alert-danger');
      alertDiv.textContent = data.message;
      
      if (data.success) {
        setTimeout(() => {
          bootstrap.Modal.getInstance(document.getElementById('modalProducto')).hide();
          cargarProductos();
        }, 1000);
      }
    } catch (e) {
      alertDiv.classList.remove('d-none');
      alertDiv.classList.add('alert-danger');
      alertDiv.textContent = 'Error de conexion';
    } finally {
      btn.disabled = false;
      spinner.classList.add('d-none');
    }
  });
});
</script>
</body>
</html>