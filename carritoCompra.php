<?php
require_once 'php/check_auth.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cafe en Linea - Carrito de Compras</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  .product-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
  .quantity-selector { width: 120px; }
  .cart-item { border-bottom: 1px solid #eee; padding: 15px 0; }
  .cart-item:last-child { border-bottom: none; }
  .summary-card { background-color: #f8f9fa; border-radius: 10px; }
  .empty-cart { text-align: center; padding: 60px 20px; }
  .empty-cart i { font-size: 4rem; color: #ccc; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <h1 class="coffee-title mb-4">Tu Carrito de Compras</h1>
  
  <!-- Estado vacio -->
  <div id="carritoVacio" class="empty-cart d-none">
    <i class="bi bi-cart-x"></i>
    <h3 class="mt-3">Tu carrito está vacío</h3>
    <p class="text-muted">Agrega productos desde nuestro catálogo para comenzar.</p>
    <a href="catalogo.php" class="btn btn-primary mt-2"><i class="bi bi-shop"></i> Ir al catálogo</a>
  </div>
  
  <!-- Contenido del carrito -->
  <div id="carritoContenido" class="d-none">
    <div class="row">
      <div class="col-lg-8 mb-4 mb-lg-0">
        <!-- Encabezado -->
        <div class="d-none d-md-flex fw-bold mb-3 pb-2 border-bottom">
          <div class="col-md-6">Producto</div>
          <div class="col-md-2 text-center">Precio</div>
          <div class="col-md-2 text-center">Cantidad</div>
          <div class="col-md-2 text-end">Total</div>
        </div>
        
        <div id="listaItems">
          <!-- Items cargados dinámicamente -->
        </div>
        
        <!-- Acciones -->
        <div class="d-flex justify-content-between mt-4">
          <a href="catalogo.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
          <button class="btn btn-outline-danger" id="btnVaciarCarrito"><i class="bi bi-trash"></i> Vaciar carrito</button>
        </div>
      </div>
      
      <!-- Resumen -->
      <div class="col-lg-4">
        <div class="card summary-card">
          <div class="card-body">
            <h4 class="coffee-title mb-4">Resumen del pedido</h4>
            
            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal (<span id="totalItemsCount">0</span> productos)</span>
              <span id="subtotalDisplay">$0</span>
            </div>
            
            <div class="d-flex justify-content-between mb-2">
              <span>Envío</span>
              <span id="envioDisplay">Gratis</span>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between mb-4">
              <span class="fw-bold">Total</span>
              <span class="fw-bold fs-5" id="totalDisplay">$0</span>
            </div>
            
            <div class="d-grid">
              <a href="checkout.php" class="btn btn-primary btn-lg" id="btnCheckout">Proceder al pago</a>
            </div>
            
            <div class="mt-3 text-center">
              <small class="text-muted"><i class="bi bi-shield-lock"></i> Pago seguro garantizado</small>
            </div>
          </div>
        </div>
        
        <!-- Información adicional -->
        <div class="mt-3 p-3 bg-light rounded">
          <h6 class="fw-bold mb-2"><i class="bi bi-truck"></i> Envío Gratis</h6>
          <p class="small text-muted mb-0">En compras superiores a $50.000</p>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Loading -->
  <div id="carritoLoading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
    <p class="mt-2 text-muted">Cargando tu carrito...</p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

function formatPrice(price) {
  return '$' + Number(price).toLocaleString('es-CO');
}

async function cargarCarrito() {
  try {
    const response = await fetch(basePath + 'php/carrito_api.php?action=list');
    const data = await response.json();
    
    document.getElementById('carritoLoading').classList.add('d-none');
    
    if (data.success && data.items && data.items.length > 0) {
      document.getElementById('carritoContenido').classList.remove('d-none');
      document.getElementById('carritoVacio').classList.add('d-none');
      renderItems(data.items);
      actualizarResumen(data);
    } else {
      document.getElementById('carritoVacio').classList.remove('d-none');
      document.getElementById('carritoContenido').classList.add('d-none');
    }
  } catch (e) {
    console.error('Error al cargar carrito:', e);
    document.getElementById('carritoLoading').classList.add('d-none');
    document.getElementById('carritoVacio').classList.remove('d-none');
  }
}

function renderItems(items) {
  const container = document.getElementById('listaItems');
  container.innerHTML = '';
  
  items.forEach(item => {
    const imgSrc = item.imagen || 'images/placeholder.png';
    const stockDisponible = item.stockDisponible || 0;
    const stockBajo = stockDisponible > 0 && stockDisponible <= 5;
    
    container.innerHTML += `
      <div class="cart-item" data-item-id="${item.id}">
        <div class="row align-items-center">
          <div class="col-md-6 mb-3 mb-md-0">
            <div class="d-flex align-items-center">
              <img src="${imgSrc}" class="product-image me-3" alt="${item.nombre}" onerror="this.src='images/placeholder.png'">
              <div>
                <h6 class="mb-1">${item.nombre}</h6>
                <p class="text-muted mb-1 small">${(item.descripcion || '').substring(0, 50)}${(item.descripcion || '').length > 50 ? '...' : ''}</p>
                ${stockBajo ? '<small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Pocas unidades</small>' : ''}
                <div class="mt-1">
                  <button class="btn btn-sm btn-link text-danger p-0" onclick="eliminarItem(${item.id})">
                    <i class="bi bi-trash"></i> Eliminar
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-2 text-md-center">
            <span class="d-md-none fw-bold">Precio: </span>
            <span>${formatPrice(item.precioUnitario)}</span>
          </div>
          <div class="col-6 col-md-2 text-md-center">
            <div class="input-group quantity-selector mx-auto">
              <button class="btn btn-outline-secondary btn-sm" type="button" onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1})" ${item.cantidad <= 1 ? 'disabled' : ''}>
                <i class="bi bi-dash"></i>
              </button>
              <input type="text" class="form-control form-control-sm text-center" value="${item.cantidad}" readonly>
              <button class="btn btn-outline-secondary btn-sm" type="button" onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1})" ${item.cantidad >= stockDisponible ? 'disabled' : ''}>
                <i class="bi bi-plus"></i>
              </button>
            </div>
            ${item.cantidad >= stockDisponible ? '<small class="text-warning d-block mt-1">Stock máx.</small>' : ''}
          </div>
          <div class="col-12 col-md-2 text-md-end mt-2 mt-md-0">
            <span class="d-md-none fw-bold">Total: </span>
            <span class="fw-bold">${formatPrice(item.subtotal)}</span>
          </div>
        </div>
      </div>`;
  });
}

function actualizarResumen(data) {
  document.getElementById('totalItemsCount').textContent = data.count;
  document.getElementById('subtotalDisplay').textContent = formatPrice(data.total);
  
  // Calcular envío (gratis si es > 50000)
  const envio = data.total >= 50000 ? 0 : 5000;
  const envioTexto = data.total >= 50000 ? 'Gratis' : formatPrice(5000);
  document.getElementById('envioDisplay').textContent = envioTexto;
  
  const totalConEnvio = data.total + envio;
  document.getElementById('totalDisplay').textContent = formatPrice(totalConEnvio);
}

async function cambiarCantidad(itemId, nuevaCantidad) {
  if (nuevaCantidad <= 0) {
    eliminarItem(itemId);
    return;
  }
  
  const formData = new FormData();
  formData.append('action', 'update');
  formData.append('itemId', itemId);
  formData.append('cantidad', nuevaCantidad);
  
  try {
    const response = await fetch(basePath + 'php/carrito_api.php', { method: 'POST', body: formData });
    const data = await response.json();
    if (data.success) {
      cargarCarrito();
      actualizarBadgeCarrito();
    } else {
      alert(data.message);
    }
  } catch (e) {
    alert('Error al actualizar cantidad');
  }
}

async function eliminarItem(itemId) {
  if (!confirm('¿Eliminar este producto del carrito?')) return;
  
  const formData = new FormData();
  formData.append('action', 'remove');
  formData.append('itemId', itemId);
  
  try {
    const response = await fetch(basePath + 'php/carrito_api.php', { method: 'POST', body: formData });
    const data = await response.json();
    if (data.success) {
      cargarCarrito();
      actualizarBadgeCarrito();
    }
  } catch (e) {
    alert('Error al eliminar producto');
  }
}

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

document.addEventListener('DOMContentLoaded', function() {
  cargarCarrito();
  
  document.getElementById('btnVaciarCarrito').addEventListener('click', async function() {
    if (!confirm('¿Seguro que deseas vaciar todo el carrito?')) return;
    
    const formData = new FormData();
    formData.append('action', 'clear');
    
    try {
      const response = await fetch(basePath + 'php/carrito_api.php', { method: 'POST', body: formData });
      const data = await response.json();
      if (data.success) {
        cargarCarrito();
        actualizarBadgeCarrito();
      }
    } catch (e) {
      alert('Error al vaciar carrito');
    }
  });
});
</script>
</body>
</html>