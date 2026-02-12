<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cafe en Linea - Catalogo de Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  .product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
  }
  .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  .product-image {
    height: 200px;
    object-fit: cover;
  }
  .category-badge {
    background-color: var(--coffee-brown);
    color: white;
  }
  .filter-section {
    background-color: #f8f9fa;
    border-radius: 10px;
  }
  .category-item {
    cursor: pointer;
    transition: all 0.2s;
  }
  .category-item:hover, .category-item.active {
    background-color: var(--coffee-brown) !important;
    color: white !important;
  }
  .skeleton-card {
    animation: pulse 1.5s infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
  .results-info {
    color: #6c757d;
    font-size: 0.9rem;
  }
  .btn-add-cart {
    background-color: var(--coffee-brown);
    border-color: var(--coffee-brown);
    color: white;
  }
  .btn-add-cart:hover {
    background-color: var(--dark-brown);
    border-color: var(--dark-brown);
    color: white;
  }
  .toast-container {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
  }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- Toast de notificacion -->
<div class="toast-container">
  <div id="toastCarrito" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastMsg">Producto agregado al carrito</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<div class="container py-5">
  <h1 class="coffee-title mb-4">Nuestros Productos</h1>
  
  <!-- Barra de controles (sin búsqueda, ya está en navbar) -->
  <div class="row mb-4">
    <div class="col-md-6">
      <span class="results-info" id="infoResultados">Cargando...</span>
    </div>
    <div class="col-md-6 text-end">
      <select class="form-select d-inline-block w-auto" id="selectOrden">
        <option value="recientes">Más recientes</option>
        <option value="precio_asc">Precio: menor a mayor</option>
        <option value="precio_desc">Precio: mayor a menor</option>
        <option value="nombre_asc">Nombre: A-Z</option>
        <option value="nombre_desc">Nombre: Z-A</option>
      </select>
    </div>
  </div>
  
  <div class="row">
    <!-- Filtros laterales -->
    <div class="col-lg-3 mb-4">
      <div class="filter-section p-3 mb-4">
        <h5 class="coffee-title">Categorías</h5>
        <div class="list-group" id="listaCategorias">
          <a href="#" class="list-group-item list-group-item-action category-item active" data-id="0">Todos los productos</a>
          <!-- Se cargan dinamicamente -->
        </div>
      </div>
      
      <div class="filter-section p-3 mb-4">
        <h5 class="coffee-title">Filtrar por precio</h5>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <input type="number" class="form-control form-control-sm" id="precioMin" placeholder="Min $" min="0">
          </div>
          <div class="col-6">
            <input type="number" class="form-control form-control-sm" id="precioMax" placeholder="Max $" min="0">
          </div>
        </div>
        <button class="btn btn-sm btn-outline-secondary w-100" id="btnFiltrarPrecio">Aplicar filtro</button>
      </div>
      
      <div class="filter-section p-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="soloDestacados">
          <label class="form-check-label" for="soloDestacados">Solo destacados</label>
        </div>
      </div>
      
      <button class="btn btn-outline-secondary w-100 mt-3" id="btnLimpiarFiltros"><i class="bi bi-x-circle"></i> Limpiar filtros</button>
    </div>
    
    <!-- Grid de productos -->
    <div class="col-lg-9">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="gridProductos">
        <!-- Se cargan dinamicamente -->
      </div>
      
      <!-- Estado vacio -->
      <div id="estadoVacio" class="text-center py-5 d-none">
        <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
        <h4 class="mt-3 text-muted">No se encontraron productos</h4>
        <p class="text-muted">Intenta cambiar los filtros o buscar con otro término.</p>
        <button class="btn btn-outline-secondary" id="btnResetBusqueda">Ver todos los productos</button>
      </div>
      
      <!-- Paginacion -->
      <nav aria-label="Navegacion de paginas" class="mt-5" id="contenedorPaginacion">
        <ul class="pagination justify-content-center" id="paginacion"></ul>
      </nav>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

// Estado del catalogo
let filtros = {
  busqueda: '',
  categoriaId: 0,
  precioMin: 0,
  precioMax: 0,
  orden: 'recientes',
  destacados: 0,
  pagina: 1,
  porPagina: 12
};

// Cargar categorias al iniciar
async function cargarCategorias() {
  try {
    const response = await fetch(basePath + 'php/catalogo_api.php?action=categorias');
    const data = await response.json();
    if (data.success) {
      const lista = document.getElementById('listaCategorias');
      // Mantener el primer item "Todos"
      const todosItem = lista.querySelector('[data-id="0"]');
      lista.innerHTML = '';
      lista.appendChild(todosItem);
      
      data.data.forEach(cat => {
        const a = document.createElement('a');
        a.href = '#';
        a.className = 'list-group-item list-group-item-action category-item';
        a.dataset.id = cat.id;
        a.textContent = cat.nombre + ' (' + cat.totalProductos + ')';
        lista.appendChild(a);
      });
    }
  } catch (e) {
    console.error('Error al cargar categorias:', e);
  }
}

// Cargar productos
async function cargarProductos() {
  const grid = document.getElementById('gridProductos');
  const estadoVacio = document.getElementById('estadoVacio');
  
  // Skeleton loading
  grid.innerHTML = '';
  for (let i = 0; i < 6; i++) {
    grid.innerHTML += `
      <div class="col">
        <div class="card skeleton-card">
          <div class="bg-secondary" style="height: 200px;"></div>
          <div class="card-body">
            <div class="bg-secondary rounded mb-2" style="height: 20px; width: 70%;"></div>
            <div class="bg-secondary rounded mb-2" style="height: 15px; width: 50%;"></div>
            <div class="bg-secondary rounded" style="height: 30px; width: 40%;"></div>
          </div>
        </div>
      </div>`;
  }
  
  estadoVacio.classList.add('d-none');
  
  // Construir URL con filtros
  let url = basePath + 'php/catalogo_api.php?';
  const params = new URLSearchParams();
  if (filtros.busqueda) params.append('busqueda', filtros.busqueda);
  if (filtros.categoriaId) params.append('categoriaId', filtros.categoriaId);
  if (filtros.precioMin > 0) params.append('precioMin', filtros.precioMin);
  if (filtros.precioMax > 0) params.append('precioMax', filtros.precioMax);
  if (filtros.destacados) params.append('destacados', filtros.destacados);
  params.append('orden', filtros.orden);
  params.append('pagina', filtros.pagina);
  params.append('porPagina', filtros.porPagina);
  url += params.toString();
  
  try {
    const response = await fetch(url);
    const data = await response.json();
    
    grid.innerHTML = '';
    
    if (data.success && data.data.length > 0) {
      document.getElementById('infoResultados').textContent = 
        data.paginacion.total + ' producto(s) encontrado(s)';
      
      data.data.forEach(prod => {
        const imagenSrc = prod.imagen || 'images/placeholder.png';
        const destacadoBadge = prod.destacado == 1 ? 
          '<span class="badge category-badge position-absolute top-0 end-0 m-2">Destacado</span>' : '';
        
        grid.innerHTML += `
          <div class="col">
            <div class="card product-card h-100">
              ${destacadoBadge}
              <a href="productos.php?id=${prod.id}">
                <img src="${imagenSrc}" class="card-img-top product-image" alt="${prod.nombre}" onerror="this.src='images/placeholder.png'">
              </a>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">${prod.nombre}</h5>
                <p class="card-text text-muted small">${prod.categoriaNombre || 'Sin categoria'}</p>
                <p class="card-text small">${(prod.descripcion || '').substring(0, 80)}${(prod.descripcion || '').length > 80 ? '...' : ''}</p>
                <div class="mt-auto">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5 fw-bold">$${Number(prod.precio).toLocaleString('es-CO')}</span>
                    <button class="btn btn-add-cart btn-sm" onclick="agregarAlCarrito(${prod.id})" ${prod.stockDisponible <= 0 ? 'disabled' : ''}>
                      <i class="bi bi-cart-plus"></i> ${prod.stockDisponible <= 0 ? 'Agotado' : 'Añadir'}
                    </button>
                  </div>
                  ${prod.stockDisponible > 0 && prod.stockDisponible <= 5 ? '<small class="text-warning mt-1 d-block">Quedan pocas unidades</small>' : ''}
                </div>
              </div>
            </div>
          </div>`;
      });
      
      renderPaginacion(data.paginacion);
    } else {
      document.getElementById('infoResultados').textContent = '0 productos encontrados';
      estadoVacio.classList.remove('d-none');
      document.getElementById('contenedorPaginacion').innerHTML = '';
    }
  } catch (e) {
    console.error('Error al cargar productos:', e);
    grid.innerHTML = '<div class="col-12 text-center text-danger">Error al cargar productos</div>';
  }
}

// Paginacion
function renderPaginacion(pag) {
  const ul = document.getElementById('paginacion');
  if (!ul) return;
  ul.innerHTML = '';
  
  if (pag.totalPaginas <= 1) return;
  
  // Anterior
  const liPrev = document.createElement('li');
  liPrev.className = 'page-item' + (pag.pagina <= 1 ? ' disabled' : '');
  liPrev.innerHTML = '<a class="page-link" href="#">Anterior</a>';
  liPrev.addEventListener('click', e => { e.preventDefault(); if (pag.pagina > 1) { filtros.pagina = pag.pagina - 1; cargarProductos(); window.scrollTo(0,0); }});
  ul.appendChild(liPrev);
  
  // Paginas
  for (let i = 1; i <= pag.totalPaginas; i++) {
    if (pag.totalPaginas > 7 && Math.abs(i - pag.pagina) > 2 && i !== 1 && i !== pag.totalPaginas) {
      if (i === pag.pagina - 3 || i === pag.pagina + 3) {
        const liDots = document.createElement('li');
        liDots.className = 'page-item disabled';
        liDots.innerHTML = '<span class="page-link">...</span>';
        ul.appendChild(liDots);
      }
      continue;
    }
    const li = document.createElement('li');
    li.className = 'page-item' + (i === pag.pagina ? ' active' : '');
    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
    li.addEventListener('click', e => { e.preventDefault(); filtros.pagina = i; cargarProductos(); window.scrollTo(0,0); });
    ul.appendChild(li);
  }
  
  // Siguiente
  const liNext = document.createElement('li');
  liNext.className = 'page-item' + (pag.pagina >= pag.totalPaginas ? ' disabled' : '');
  liNext.innerHTML = '<a class="page-link" href="#">Siguiente</a>';
  liNext.addEventListener('click', e => { e.preventDefault(); if (pag.pagina < pag.totalPaginas) { filtros.pagina = pag.pagina + 1; cargarProductos(); window.scrollTo(0,0); }});
  ul.appendChild(liNext);
}

// Agregar al carrito
async function agregarAlCarrito(productoId) {
  try {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('productoId', productoId);
    formData.append('cantidad', 1);
    
    const response = await fetch(basePath + 'php/carrito_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    
    const toastEl = document.getElementById('toastCarrito');
    const toastMsg = document.getElementById('toastMsg');
    
    if (data.success) {
      toastEl.classList.remove('text-bg-danger');
      toastEl.classList.add('text-bg-success');
      toastMsg.textContent = data.message;
      // Actualizar badge del carrito en navbar
      actualizarBadgeCarrito();
    } else {
      toastEl.classList.remove('text-bg-success');
      toastEl.classList.add('text-bg-danger');
      toastMsg.textContent = data.message || 'Debes iniciar sesión';
    }
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
  } catch (e) {
    alert('Error al agregar al carrito. Verifica que hayas iniciado sesión.');
  }
}

// Actualizar badge del carrito en navbar
async function actualizarBadgeCarrito() {
  try {
    const response = await fetch(basePath + 'php/carrito_api.php?action=count');
    const data = await response.json();
    if (data.success) {
      const badge = document.getElementById('cartBadgeNavbar');
      if (badge) {
        badge.textContent = data.count;
        badge.style.display = data.count > 0 ? 'inline-block' : 'none';
      }
    }
  } catch(e) {}
}

// Detectar parámetros de búsqueda en URL
function obtenerParametrosURL() {
  const params = new URLSearchParams(window.location.search);
  const busqueda = params.get('busqueda');
  if (busqueda) {
    filtros.busqueda = busqueda;
    // Actualizar también el input del navbar si existe
    const inputNavbar = document.getElementById('inputBusquedaNavbar');
    if (inputNavbar) {
      inputNavbar.value = busqueda;
    }
  }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Obtener parámetros de URL primero
  obtenerParametrosURL();
  
  cargarCategorias();
  cargarProductos();
  
  // Orden
  document.getElementById('selectOrden').addEventListener('change', e => {
    filtros.orden = e.target.value;
    filtros.pagina = 1;
    cargarProductos();
  });
  
  // Categorias
  document.getElementById('listaCategorias').addEventListener('click', e => {
    e.preventDefault();
    const item = e.target.closest('.category-item');
    if (!item) return;
    
    document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
    item.classList.add('active');
    filtros.categoriaId = parseInt(item.dataset.id) || 0;
    filtros.pagina = 1;
    cargarProductos();
  });
  
  // Filtro precio
  document.getElementById('btnFiltrarPrecio').addEventListener('click', () => {
    filtros.precioMin = parseFloat(document.getElementById('precioMin').value) || 0;
    filtros.precioMax = parseFloat(document.getElementById('precioMax').value) || 0;
    filtros.pagina = 1;
    cargarProductos();
  });
  
  // Solo destacados
  document.getElementById('soloDestacados').addEventListener('change', e => {
    filtros.destacados = e.target.checked ? 1 : 0;
    filtros.pagina = 1;
    cargarProductos();
  });
  
  // Limpiar filtros
  document.getElementById('btnLimpiarFiltros').addEventListener('click', () => {
    filtros = { busqueda:'', categoriaId:0, precioMin:0, precioMax:0, orden:'recientes', destacados:0, pagina:1, porPagina:12 };
    document.getElementById('precioMin').value = '';
    document.getElementById('precioMax').value = '';
    document.getElementById('selectOrden').value = 'recientes';
    document.getElementById('soloDestacados').checked = false;
    document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
    document.querySelector('.category-item[data-id="0"]').classList.add('active');
    
    // Limpiar también el input del navbar
    const inputNavbar = document.getElementById('inputBusquedaNavbar');
    if (inputNavbar) {
      inputNavbar.value = '';
    }
    
    // Limpiar parámetros de URL
    window.history.replaceState({}, document.title, window.location.pathname);
    
    cargarProductos();
  });
  
  // Reset busqueda desde estado vacio
  const btnReset = document.getElementById('btnResetBusqueda');
  if (btnReset) {
    btnReset.addEventListener('click', () => {
      document.getElementById('btnLimpiarFiltros').click();
    });
  }
});
</script>
</body>
</html>