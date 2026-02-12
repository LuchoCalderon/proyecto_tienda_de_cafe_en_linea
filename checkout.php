<?php
require_once 'php/check_auth.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Cafe en Linea</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  .checkout-step { background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
  .checkout-step h5 { display: flex; align-items: center; }
  .checkout-step .step-number { width: 30px; height: 30px; background-color: var(--coffee-brown); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold; }
  .address-card, .payment-card { border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s; margin-bottom: 10px; }
  .address-card:hover, .payment-card:hover { border-color: var(--coffee-brown); background-color: #f8f9fa; }
  .address-card.selected, .payment-card.selected { border-color: var(--coffee-brown); background-color: #fff8f0; }
  .order-summary { background-color: #f8f9fa; border-radius: 10px; padding: 20px; position: sticky; top: 20px; }
  .product-mini-img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <h1 class="coffee-title mb-4">Finalizar Compra</h1>
  
  <div class="row">
    <div class="col-lg-8">
      <!-- Paso 1: Dirección de Envío -->
      <div class="checkout-step">
        <h5><span class="step-number">1</span> Dirección de Envío</h5>
        <div id="direccionesList" class="mt-3">
          <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Cargando direcciones...</p>
          </div>
        </div>
        <button class="btn btn-outline-secondary btn-sm mt-2" onclick="window.location.href='perfilUsuario.php#direcciones'">
          <i class="bi bi-plus-circle"></i> Agregar nueva dirección
        </button>
      </div>
      
      <!-- Paso 2: Método de Pago -->
      <div class="checkout-step">
        <h5><span class="step-number">2</span> Método de Pago</h5>
        <div id="pagosList" class="mt-3">
          <!-- Opciones de pago predeterminadas -->
          <div class="payment-card" onclick="seleccionarMetodoPago('contraentrega', this)">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="metodoPago" id="pagoContraentrega" value="contraentrega">
              <label class="form-check-label w-100" for="pagoContraentrega">
                <div class="d-flex align-items-center">
                  <i class="bi bi-cash-coin fs-3 me-3"></i>
                  <div>
                    <strong>Pago Contraentrega</strong>
                    <p class="mb-0 small text-muted">Paga cuando recibas tu pedido</p>
                  </div>
                </div>
              </label>
            </div>
          </div>
          
          <div class="payment-card" onclick="seleccionarMetodoPago('tarjeta', this)">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="metodoPago" id="pagoTarjeta" value="tarjeta">
              <label class="form-check-label w-100" for="pagoTarjeta">
                <div class="d-flex align-items-center">
                  <i class="bi bi-credit-card fs-3 me-3"></i>
                  <div>
                    <strong>Tarjeta de Crédito/Débito</strong>
                    <p class="mb-0 small text-muted">Pago seguro en línea</p>
                  </div>
                </div>
              </label>
            </div>
          </div>
          
          <div class="payment-card" onclick="seleccionarMetodoPago('transferencia', this)">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="metodoPago" id="pagoTransferencia" value="transferencia">
              <label class="form-check-label w-100" for="pagoTransferencia">
                <div class="d-flex align-items-center">
                  <i class="bi bi-bank fs-3 me-3"></i>
                  <div>
                    <strong>Transferencia Bancaria</strong>
                    <p class="mb-0 small text-muted">Recibirás instrucciones por email</p>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Paso 3: Notas adicionales -->
      <div class="checkout-step">
        <h5><span class="step-number">3</span> Notas del Pedido (Opcional)</h5>
        <textarea class="form-control mt-3" id="notasPedido" rows="3" placeholder="Ej: Por favor dejar en portería, No tocar timbre, etc."></textarea>
      </div>
      
      <!-- Botón finalizar -->
      <div class="d-grid gap-2">
        <button class="btn btn-primary btn-lg" id="btnFinalizarCompra">
          <i class="bi bi-check-circle"></i> Confirmar y Realizar Pedido
        </button>
        <a href="carritoCompra.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Volver al carrito
        </a>
      </div>
    </div>
    
    <!-- Resumen del Pedido -->
    <div class="col-lg-4">
      <div class="order-summary">
        <h5 class="coffee-title mb-3">Resumen del Pedido</h5>
        
        <div id="resumenItems" class="mb-3">
          <!-- Items cargados dinámicamente -->
        </div>
        
        <hr>
        
        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal</span>
          <span id="resumenSubtotal">$0</span>
        </div>
        
        <div class="d-flex justify-content-between mb-2">
          <span>Envío</span>
          <span id="resumenEnvio">Calculando...</span>
        </div>
        
        <hr>
        
        <div class="d-flex justify-content-between mb-3">
          <span class="fw-bold fs-5">Total</span>
          <span class="fw-bold fs-5 text-primary" id="resumenTotal">$0</span>
        </div>
        
        <div class="alert alert-info small mb-0">
          <i class="bi bi-info-circle"></i> <strong>Envío gratis</strong> en compras superiores a $50.000
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-5">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h3 class="mt-3">¡Pedido Realizado!</h3>
        <p class="mb-3">Tu pedido ha sido procesado exitosamente</p>
        <div class="bg-light p-3 rounded mb-3">
          <strong>Número de Seguimiento:</strong><br>
          <span class="fs-5 text-primary" id="numeroSeguimientoModal"></span>
        </div>
        <div class="d-grid gap-2">
          <a href="historialOrdenes.php" class="btn btn-primary">Ver Mis Pedidos</a>
          <a href="catalogo.php" class="btn btn-outline-secondary">Seguir Comprando</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

let direccionSeleccionada = null;
let metodoPagoSeleccionado = null;
let carritoData = null;

function formatPrice(price) {
  return '$' + Number(price).toLocaleString('es-CO');
}

async function cargarDirecciones() {
  try {
    const response = await fetch(basePath + 'php/checkout_api.php?action=get_addresses');
    const data = await response.json();
    
    const container = document.getElementById('direccionesList');
    
    if (data.success && data.direcciones && data.direcciones.length > 0) {
      container.innerHTML = '';
      data.direcciones.forEach((dir, index) => {
        const isDefault = dir.esPredeterminada == 1;
        container.innerHTML += `
          <div class="address-card ${isDefault && !direccionSeleccionada ? 'selected' : ''}" onclick="seleccionarDireccion(${dir.id}, this)">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="direccion" id="dir${dir.id}" value="${dir.id}" ${isDefault && !direccionSeleccionada ? 'checked' : ''}>
              <label class="form-check-label w-100" for="dir${dir.id}">
                <strong>${dir.alias || 'Dirección ' + (index + 1)}</strong>
                ${isDefault ? '<span class="badge bg-primary ms-2">Predeterminada</span>' : ''}
                <p class="mb-0 small">${dir.calle}${dir.apartamento ? ', ' + dir.apartamento : ''}</p>
                <p class="mb-0 small text-muted">${dir.ciudad}, ${dir.departamento} - ${dir.codigoPostal}</p>
                ${dir.instrucciones ? '<p class="mb-0 small text-muted"><i class="bi bi-info-circle"></i> ' + dir.instrucciones + '</p>' : ''}
              </label>
            </div>
          </div>`;
        
        if (isDefault && !direccionSeleccionada) {
          direccionSeleccionada = dir.id;
        }
      });
    } else {
      container.innerHTML = `
        <div class="alert alert-warning">
          <i class="bi bi-exclamation-triangle"></i> No tienes direcciones registradas.
          <a href="perfilUsuario.php#direcciones" class="alert-link">Agregar dirección</a>
        </div>`;
    }
  } catch (e) {
    console.error('Error al cargar direcciones:', e);
  }
}

async function cargarResumenCarrito() {
  try {
    const response = await fetch(basePath + 'php/carrito_api.php?action=list');
    const data = await response.json();
    
    if (data.success && data.items && data.items.length > 0) {
      carritoData = data;
      
      const container = document.getElementById('resumenItems');
      container.innerHTML = '';
      
      data.items.forEach(item => {
        const imgSrc = item.imagen || 'images/placeholder.png';
        container.innerHTML += `
          <div class="d-flex align-items-center mb-2">
            <img src="${imgSrc}" class="product-mini-img me-2" alt="${item.nombre}" onerror="this.src='images/placeholder.png'">
            <div class="flex-grow-1">
              <small><strong>${item.nombre}</strong></small>
              <br><small class="text-muted">x${item.cantidad}</small>
            </div>
            <strong class="small">${formatPrice(item.subtotal)}</strong>
          </div>`;
      });
      
      const subtotal = data.total;
      const envio = subtotal >= 50000 ? 0 : 5000;
      const total = subtotal + envio;
      
      document.getElementById('resumenSubtotal').textContent = formatPrice(subtotal);
      document.getElementById('resumenEnvio').textContent = envio === 0 ? 'Gratis' : formatPrice(envio);
      document.getElementById('resumenTotal').textContent = formatPrice(total);
    } else {
      window.location.href = 'carritoCompra.php';
    }
  } catch (e) {
    console.error('Error al cargar resumen:', e);
    window.location.href = 'carritoCompra.php';
  }
}

function seleccionarDireccion(id, element) {
  document.querySelectorAll('.address-card').forEach(el => el.classList.remove('selected'));
  element.classList.add('selected');
  document.getElementById('dir' + id).checked = true;
  direccionSeleccionada = id;
}

function seleccionarMetodoPago(tipo, element) {
  document.querySelectorAll('.payment-card').forEach(el => el.classList.remove('selected'));
  element.classList.add('selected');
  metodoPagoSeleccionado = tipo;
}

async function finalizarCompra() {
  if (!direccionSeleccionada) {
    alert('Por favor selecciona una dirección de envío');
    return;
  }
  
  if (!metodoPagoSeleccionado) {
    alert('Por favor selecciona un método de pago');
    return;
  }
  
  const btn = document.getElementById('btnFinalizarCompra');
  const btnText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
  
  try {
    const formData = new FormData();
    formData.append('action', 'create_order');
    formData.append('direccionId', direccionSeleccionada);
    formData.append('metodoPago', metodoPagoSeleccionado);
    formData.append('notas', document.getElementById('notasPedido').value);
    
    const response = await fetch(basePath + 'php/checkout_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    
    if (data.success) {
      document.getElementById('numeroSeguimientoModal').textContent = data.numeroSeguimiento;
      const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
      modal.show();
    } else {
      alert('Error: ' + data.message);
      btn.disabled = false;
      btn.innerHTML = btnText;
    }
  } catch (e) {
    console.error('Error al crear pedido:', e);
    alert('Error al procesar el pedido');
    btn.disabled = false;
    btn.innerHTML = btnText;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  cargarDirecciones();
  cargarResumenCarrito();
  
  document.getElementById('btnFinalizarCompra').addEventListener('click', finalizarCompra);
});
</script>
</body>
</html>