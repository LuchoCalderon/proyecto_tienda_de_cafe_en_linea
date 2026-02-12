<?php
session_start();
$usuarioAutenticado = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Café en Línea - Planes de Suscripción</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  .hero-section {
    background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/cafe.jpeg');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 100px 0;
    margin-bottom: 60px;
  }
  
  .plan-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
    height: 100%;
    border: 2px solid #e9ecef;
  }
  
  .plan-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
  }
  
  .plan-card.featured {
    border: 3px solid var(--coffee-brown);
    position: relative;
  }
  
  .featured-badge {
    position: absolute;
    top: 0;
    right: 0;
    background-color: var(--coffee-brown);
    color: white;
    padding: 8px 15px;
    font-size: 0.8rem;
    font-weight: 600;
    border-bottom-left-radius: 10px;
    z-index: 1;
  }
  
  .plan-header {
    background: linear-gradient(135deg, var(--coffee-brown) 0%, #4a2c1a 100%);
    color: white;
    padding: 30px 20px;
    text-align: center;
  }
  
  .plan-price {
    font-size: 2.5rem;
    font-weight: 700;
  }
  
  .plan-price small {
    font-size: 1rem;
    font-weight: 400;
  }
  
  .savings-badge {
    background-color: #28a745;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    display: inline-block;
    margin-top: 10px;
  }
  
  .plan-features {
    padding: 30px 20px;
  }
  
  .plan-feature {
    padding: 12px 0;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
  }
  
  .plan-feature:last-child {
    border-bottom: none;
  }
  
  .feature-icon {
    color: var(--coffee-brown);
    margin-right: 10px;
    font-size: 1.2rem;
  }
  
  .benefit-icon {
    width: 60px;
    height: 60px;
    background-color: var(--coffee-brown);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin: 0 auto 15px;
  }
  
  .product-selector {
    max-height: 400px;
    overflow-y: auto;
  }
  
  .product-item {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    transition: all 0.3s;
  }
  
  .product-item.selected {
    border-color: var(--coffee-brown);
    background-color: #fdfbf7;
  }
  
  .product-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
  }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center">
  <div class="container">
    <h1 class="display-4 fw-bold mb-4">Planes de Suscripción</h1>
    <p class="lead mb-4">Recibe tu café favorito automáticamente con un 10% de descuento</p>
    <p class="fs-5"><i class="bi bi-truck"></i> Envío gratis | <i class="bi bi-x-circle"></i> Cancela cuando quieras | <i class="bi bi-tag-fill"></i> 10% de ahorro</p>
  </div>
</section>

<!-- Planes -->
<section class="container mb-5">
  <div class="row g-4">
    <!-- Plan Semanal -->
    <div class="col-md-4">
      <div class="card plan-card">
        <div class="plan-header">
          <h3 class="mb-2">Semanal</h3>
          <div class="plan-price">$36.000 <small>/semana</small></div>
          <div class="savings-badge">
            <i class="bi bi-tag-fill"></i> Ahorras $4.000/semana
          </div>
        </div>
        <div class="plan-features">
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Entrega cada 7 días</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>10% de descuento automático</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Envío gratis</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Cancela en cualquier momento</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Modifica productos fácilmente</span>
          </div>
        </div>
        <div class="card-footer bg-white p-3">
          <button class="btn btn-primary w-100" onclick="iniciarSuscripcion('semanal')">
            <i class="bi bi-calendar-check"></i> Suscribirme
          </button>
        </div>
      </div>
    </div>
    
    <!-- Plan Quincenal - Destacado -->
    <div class="col-md-4">
      <div class="card plan-card featured">
        <span class="featured-badge">MÁS POPULAR</span>
        <div class="plan-header">
          <h3 class="mb-2">Quincenal</h3>
          <div class="plan-price">$63.000 <small>/quincena</small></div>
          <div class="savings-badge">
            <i class="bi bi-tag-fill"></i> Ahorras $7.000/quincena
          </div>
        </div>
        <div class="plan-features">
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Entrega cada 15 días</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>10% de descuento automático</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Envío gratis</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Cancela en cualquier momento</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Equilibrio perfecto de frescura</span>
          </div>
        </div>
        <div class="card-footer bg-white p-3">
          <button class="btn btn-primary w-100" onclick="iniciarSuscripcion('quincenal')">
            <i class="bi bi-calendar-check"></i> Suscribirme
          </button>
        </div>
      </div>
    </div>
    
    <!-- Plan Mensual -->
    <div class="col-md-4">
      <div class="card plan-card">
        <div class="plan-header">
          <h3 class="mb-2">Mensual</h3>
          <div class="plan-price">$108.000 <small>/mes</small></div>
          <div class="savings-badge">
            <i class="bi bi-tag-fill"></i> Ahorras $12.000/mes
          </div>
        </div>
        <div class="plan-features">
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Entrega cada 30 días</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>10% de descuento automático</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Envío gratis</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Cancela en cualquier momento</span>
          </div>
          <div class="plan-feature">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <span>Mayor ahorro mensual</span>
          </div>
        </div>
        <div class="card-footer bg-white p-3">
          <button class="btn btn-primary w-100" onclick="iniciarSuscripcion('mensual')">
            <i class="bi bi-calendar-check"></i> Suscribirme
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Beneficios -->
<section class="container mb-5">
  <h2 class="coffee-title text-center mb-5">¿Por qué suscribirse?</h2>
  <div class="row g-4 text-center">
    <div class="col-md-3">
      <div class="benefit-icon">
        <i class="bi bi-tag-fill"></i>
      </div>
      <h5>Ahorra 10%</h5>
      <p class="text-muted">Descuento automático en todos tus productos</p>
    </div>
    <div class="col-md-3">
      <div class="benefit-icon">
        <i class="bi bi-truck"></i>
      </div>
      <h5>Envío Gratis</h5>
      <p class="text-muted">Sin costos adicionales de envío</p>
    </div>
    <div class="col-md-3">
      <div class="benefit-icon">
        <i class="bi bi-calendar-check"></i>
      </div>
      <h5>Entregas Automáticas</h5>
      <p class="text-muted">Nunca te quedarás sin café</p>
    </div>
    <div class="col-md-3">
      <div class="benefit-icon">
        <i class="bi bi-x-circle"></i>
      </div>
      <h5>Sin Compromiso</h5>
      <p class="text-muted">Cancela cuando quieras</p>
    </div>
  </div>
</section>

<!-- Modal Seleccionar Productos -->
<div class="modal fade" id="modalSuscripcion" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Selecciona tus Productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>Plan <span id="planNombre"></span></strong><br>
          <i class="bi bi-tag-fill"></i> 10% de descuento aplicado automáticamente
        </div>
        
        <h6 class="mb-3">Productos Disponibles:</h6>
        <div id="productosDisponibles" class="product-selector">
          <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Cargando productos...</p>
          </div>
        </div>
        
        <hr>
        
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <strong>Total Original:</strong> <span id="totalOriginal" class="text-muted text-decoration-line-through">$0</span><br>
            <strong>Descuento 10%:</strong> <span id="descuento" class="text-success">-$0</span><br>
            <h5 class="mb-0 mt-2">Total: <span id="totalFinal" class="text-primary">$0</span></h5>
          </div>
          <button class="btn btn-primary btn-lg" id="btnConfirmarSuscripcion" disabled>
            <i class="bi bi-check-circle"></i> Confirmar Suscripción
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-5">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h3 class="mt-3">¡Suscripción Creada!</h3>
        <p class="mb-3">Tu suscripción ha sido activada exitosamente</p>
        <p class="text-muted">Próximo envío: <strong id="proximoEnvio"></strong></p>
        <div class="d-grid gap-2">
          <a href="misSuscripciones.php" class="btn btn-primary">Ver Mis Suscripciones</a>
          <a href="home.php" class="btn btn-outline-secondary">Ir al Inicio</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
const usuarioAutenticado = <?php echo $usuarioAutenticado ? 'true' : 'false'; ?>;
let frecuenciaSeleccionada = '';
let productosSeleccionados = [];
let productosDisponibles = [];

function formatPrice(price) {
  return '$' + Number(price).toLocaleString('es-CO');
}

function iniciarSuscripcion(frecuencia) {
  if (!usuarioAutenticado) {
    alert('Debes iniciar sesión para suscribirte');
    window.location.href = 'login.php';
    return;
  }
  
  frecuenciaSeleccionada = frecuencia;
  const nombresFrecuencia = {
    'semanal': 'Semanal',
    'quincenal': 'Quincenal',
    'mensual': 'Mensual'
  };
  document.getElementById('planNombre').textContent = nombresFrecuencia[frecuencia];
  
  productosSeleccionados = [];
  cargarProductos();
  new bootstrap.Modal(document.getElementById('modalSuscripcion')).show();
}

async function cargarProductos() {
  try {
    const response = await fetch(basePath + 'php/catalogo_api.php?porPagina=100');
    const data = await response.json();
    
    if (data.success && data.data) {
      productosDisponibles = data.data;
      renderProductos();
    }
  } catch (e) {
    console.error('Error al cargar productos:', e);
  }
}

function renderProductos() {
  const container = document.getElementById('productosDisponibles');
  container.innerHTML = '';
  
  productosDisponibles.forEach(prod => {
    const isSelected = productosSeleccionados.find(p => p.productoId === prod.id);
    const imgSrc = prod.imagen || 'images/placeholder.png';
    
    const div = document.createElement('div');
    div.className = `product-item ${isSelected ? 'selected' : ''}`;
    div.innerHTML = `
      <div class="d-flex align-items-center">
        <img src="${imgSrc}" alt="${prod.nombre}" onerror="this.src='images/placeholder.png'">
        <div class="flex-grow-1 mx-3">
          <h6 class="mb-1">${prod.nombre}</h6>
          <p class="mb-0 text-muted small">${prod.categoriaNombre || ''}</p>
          <strong>${formatPrice(prod.precio)}</strong>
        </div>
        <div class="d-flex align-items-center">
          ${isSelected ? `
            <button class="btn btn-sm btn-outline-secondary me-2" onclick="cambiarCantidad(${prod.id}, -1)">
              <i class="bi bi-dash"></i>
            </button>
            <span class="mx-2 fw-bold">${isSelected.cantidad}</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="cambiarCantidad(${prod.id}, 1)">
              <i class="bi bi-plus"></i>
            </button>
          ` : `
            <button class="btn btn-sm btn-primary" onclick="agregarProducto(${prod.id})">
              <i class="bi bi-plus-circle"></i> Agregar
            </button>
          `}
        </div>
      </div>`;
    
    container.appendChild(div);
  });
  
  actualizarTotales();
}

function agregarProducto(productoId) {
  productosSeleccionados.push({ productoId, cantidad: 1 });
  renderProductos();
}

function cambiarCantidad(productoId, delta) {
  const index = productosSeleccionados.findIndex(p => p.productoId === productoId);
  if (index !== -1) {
    productosSeleccionados[index].cantidad += delta;
    if (productosSeleccionados[index].cantidad <= 0) {
      productosSeleccionados.splice(index, 1);
    }
  }
  renderProductos();
}

function actualizarTotales() {
  let total = 0;
  productosSeleccionados.forEach(item => {
    const prod = productosDisponibles.find(p => p.id === item.productoId);
    if (prod) {
      total += prod.precio * item.cantidad;
    }
  });
  
  const descuento = total * 0.1;
  const totalFinal = total - descuento;
  
  document.getElementById('totalOriginal').textContent = formatPrice(total);
  document.getElementById('descuento').textContent = '-' + formatPrice(descuento);
  document.getElementById('totalFinal').textContent = formatPrice(totalFinal);
  
  document.getElementById('btnConfirmarSuscripcion').disabled = productosSeleccionados.length === 0;
}

document.getElementById('btnConfirmarSuscripcion')?.addEventListener('click', async function() {
  const btn = this;
  const btnText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
  
  try {
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('frecuencia', frecuenciaSeleccionada);
    formData.append('productos', JSON.stringify(productosSeleccionados));
    
    const response = await fetch(basePath + 'php/suscripciones_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalSuscripcion')).hide();
      
      // Formatear fecha
      const fecha = new Date(data.fechaProximoEnvio);
      document.getElementById('proximoEnvio').textContent = fecha.toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      });
      
      new bootstrap.Modal(document.getElementById('modalConfirmacion')).show();
    } else {
      alert('Error: ' + data.message);
      btn.disabled = false;
      btn.innerHTML = btnText;
    }
  } catch (e) {
    alert('Error al crear suscripción');
    btn.disabled = false;
    btn.innerHTML = btnText;
  }
});
</script>
</body>
</html>