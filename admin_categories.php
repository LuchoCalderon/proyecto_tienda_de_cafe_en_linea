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
<title>Admin - Categorías</title>
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
    width: 100%;
    max-width: 200px;
    height: auto;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .category-card {
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  
  .category-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
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
    <a href="admin_categories.php" class="active"><i class="bi bi-tags"></i> Categorías</a>
    <div class="divider"></div>
    <a href="home.php"><i class="bi bi-house"></i> Ver Tienda</a>
    <a href="php/logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
  </div>
</nav>

<div class="main-content" id="mainContent">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestión de Categorías</h2>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria" onclick="abrirModalNueva()">
        <i class="bi bi-plus-circle"></i> Nueva Categoría
      </button>
    </div>
    
    <div class="row g-4" id="categoriasContainer">
      <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Cargando categorías...</p>
      </div>
    </div>
  </div>
</div>

<!-- Modal Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCategoriaTitle">Nueva Categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formCategoria" enctype="multipart/form-data">
        <div class="modal-body">
          <div id="alertModal" class="alert d-none"></div>
          
          <input type="hidden" name="id" id="catId">
          
          <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" id="catNombre" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" id="catDescripcion" rows="3"></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Imagen de Categoría</label>
            <input type="file" class="form-control" name="imagen" id="catImagen" accept="image/*">
            <div class="form-text">Tamaño recomendado: 800x600px. Formatos: JPG, PNG, GIF, WEBP</div>
          </div>
          
          <div id="imagenPreview" class="mb-3 d-none">
            <label class="form-label">Vista previa:</label>
            <div class="text-center">
              <img id="imagenPreviewImg" class="category-img-preview" src="" alt="Preview">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btnGuardar">
            <span class="spinner-border spinner-border-sm d-none" id="btnSpinner"></span>
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
let modoEdicion = false;

// Toggle sidebar
document.getElementById('sidebarToggle').addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('visible');
  document.getElementById('sidebarOverlay').classList.toggle('show');
});

document.getElementById('sidebarOverlay').addEventListener('click', function() {
  document.getElementById('sidebar').classList.remove('visible');
  this.classList.remove('show');
});

// Cerrar sidebar al hacer click en enlace (móvil)
document.querySelectorAll('.sidebar-menu a').forEach(link => {
  link.addEventListener('click', function() {
    if (window.innerWidth < 992) {
      document.getElementById('sidebar').classList.remove('visible');
      document.getElementById('sidebarOverlay').classList.remove('show');
    }
  });
});

// Preview imagen
document.getElementById('catImagen').addEventListener('change', function(e) {
  if (e.target.files && e.target.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imagenPreviewImg').src = e.target.result;
      document.getElementById('imagenPreview').classList.remove('d-none');
    };
    reader.readAsDataURL(e.target.files[0]);
  }
});

async function cargarCategorias() {
  try {
    const res = await fetch(basePath + 'php/admin_categorias.php?action=list');
    const data = await res.json();
    
    const container = document.getElementById('categoriasContainer');
    
    if (data.success && data.data.length > 0) {
      container.innerHTML = '';
      data.data.forEach(cat => {
        const imgSrc = cat.imagen || 'images/placeholder-category.jpg';
        container.innerHTML += `
          <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card category-card h-100">
              <img src="${imgSrc}" class="category-card-img" alt="${cat.nombre}" onerror="this.src='images/placeholder-category.jpg'">
              <div class="card-body">
                <h5 class="card-title mb-2">${cat.nombre}</h5>
                <p class="card-text text-muted small mb-2">${cat.descripcion || 'Sin descripción'}</p>
                <small class="text-muted">
                  <i class="bi bi-box-seam"></i> ${cat.totalProductos} productos
                </small>
              </div>
              <div class="card-footer bg-white border-0">
                <div class="btn-group w-100">
                  <button class="btn btn-sm btn-outline-primary" onclick="editarCategoria(${cat.id})">
                    <i class="bi bi-pencil"></i> Editar
                  </button>
                  <button class="btn btn-sm btn-outline-danger" onclick="eliminarCategoria(${cat.id}, '${cat.nombre.replace(/'/g, "\\'")}')">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>`;
      });
    } else {
      container.innerHTML = `
        <div class="col-12 text-center py-5">
          <i class="bi bi-tags" style="font-size:4rem;color:#ccc;"></i>
          <h4 class="mt-3 text-muted">No hay categorías</h4>
          <p class="text-muted">Crea la primera categoría para organizar tus productos</p>
          <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalCategoria" onclick="abrirModalNueva()">
            <i class="bi bi-plus-circle"></i> Crear primera categoría
          </button>
        </div>`;
    }
  } catch (e) {
    console.error('Error:', e);
    document.getElementById('categoriasContainer').innerHTML = `
      <div class="col-12 text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size:3rem;"></i>
        <h4 class="mt-3 text-danger">Error al cargar categorías</h4>
        <button class="btn btn-primary mt-2" onclick="cargarCategorias()">Reintentar</button>
      </div>`;
  }
}

function abrirModalNueva() {
  modoEdicion = false;
  document.getElementById('formCategoria').reset();
  document.getElementById('catId').value = '';
  document.getElementById('modalCategoriaTitle').textContent = 'Nueva Categoría';
  document.getElementById('imagenPreview').classList.add('d-none');
  document.getElementById('alertModal').classList.add('d-none');
}

async function editarCategoria(id) {
  modoEdicion = true;
  const res = await fetch(basePath + 'php/admin_categorias.php?action=get&id=' + id);
  const data = await res.json();
  
  if (data.success) {
    const cat = data.data;
    document.getElementById('catId').value = cat.id;
    document.getElementById('catNombre').value = cat.nombre;
    document.getElementById('catDescripcion').value = cat.descripcion || '';
    document.getElementById('modalCategoriaTitle').textContent = 'Editar Categoría';
    document.getElementById('alertModal').classList.add('d-none');
    
    if (cat.imagen) {
      document.getElementById('imagenPreviewImg').src = cat.imagen;
      document.getElementById('imagenPreview').classList.remove('d-none');
    } else {
      document.getElementById('imagenPreview').classList.add('d-none');
    }
    
    new bootstrap.Modal(document.getElementById('modalCategoria')).show();
  }
}

async function eliminarCategoria(id, nombre) {
  if (!confirm(`¿Eliminar la categoría "${nombre}"?\n\nEsta acción no se puede deshacer.`)) return;
  
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('id', id);
  
  try {
    const res = await fetch(basePath + 'php/admin_categorias.php', { method: 'POST', body: formData });
    const data = await res.json();
    
    if (data.success) {
      mostrarAlerta('Categoría eliminada exitosamente', 'success');
      cargarCategorias();
    } else {
      mostrarAlerta(data.message, 'danger');
    }
  } catch (e) {
    mostrarAlerta('Error de conexión', 'danger');
  }
}

function mostrarAlerta(mensaje, tipo) {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
  alertDiv.style.zIndex = '9999';
  alertDiv.innerHTML = `
    ${mensaje}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.body.appendChild(alertDiv);
  
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

document.getElementById('formCategoria').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const spinner = document.getElementById('btnSpinner');
  const btnGuardar = document.getElementById('btnGuardar');
  const alertModal = document.getElementById('alertModal');
  
  btnGuardar.disabled = true;
  spinner.classList.remove('d-none');
  alertModal.classList.add('d-none');
  
  const formData = new FormData(this);
  formData.append('action', modoEdicion ? 'update' : 'create');
  
  try {
    const res = await fetch(basePath + 'php/admin_categorias.php', { method: 'POST', body: formData });
    const data = await res.json();
    
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
      mostrarAlerta(data.message, 'success');
      cargarCategorias();
    } else {
      alertModal.textContent = data.message;
      alertModal.className = 'alert alert-danger';
      alertModal.classList.remove('d-none');
    }
  } catch (e) {
    alertModal.textContent = 'Error de conexión';
    alertModal.className = 'alert alert-danger';
    alertModal.classList.remove('d-none');
  } finally {
    btnGuardar.disabled = false;
    spinner.classList.add('d-none');
  }
});

document.addEventListener('DOMContentLoaded', cargarCategorias);
</script>
</body>
</html>