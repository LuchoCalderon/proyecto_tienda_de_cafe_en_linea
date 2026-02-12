<?php
// Obtener categorías de la base de datos para mostrar sus imágenes
require_once 'config/db_config.php';
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT c.id, c.nombre, c.descripcion, c.imagen,
               COUNT(p.id) as totalProductos
        FROM categoria c
        LEFT JOIN producto p ON c.id = p.categoriaId AND p.activo = 1
        GROUP BY c.id
        ORDER BY c.nombre
    ");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Café en Línea - Tienda de Café Premium</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  .hero-section {
    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/cafe.jpeg');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 120px 0;
    margin-bottom: 40px;
  }
  
  .category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
    height: 200px;
    cursor: pointer;
    text-decoration: none;
    display: block;
    position: relative;
  }
  
  .category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
  }
  
  .category-card img {
    height: 100%;
    width: 100%;
    object-fit: cover;
    object-position: center;
  }
  
  .category-card .card-img-overlay {
    background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0.2));
    display: flex;
    align-items: flex-end;
    padding: 1rem;
  }
  
  .category-card .card-title {
    color: white;
    font-weight: 600;
    margin-bottom: 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
  }
  
  .category-count {
    background-color: rgba(255,255,255,0.3);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.85rem;
    margin-left: 8px;
  }
  
  .featured-product {
    transition: transform 0.3s ease;
  }
  
  .featured-product:hover {
    transform: translateY(-5px);
  }

  .featured-product .product-img-container {
    height: 250px;
    width: 100%;
    overflow: hidden;
    position: relative;
  }
  
  .featured-product .product-img {
    width: 100%;
    height: 100%;
    object-fit: cover; 
  }

  .testimonial-card {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
  }
  
  .subscription-section {
    background-color: var(--coffee-brown);
    color: white;
    padding: 60px 0;
  }
  
  .skeleton-card {
    animation: pulse 1.5s infinite;
  }
  
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
  
  .toast-container {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
  }
  
  @media (max-width: 576px) {
    .category-card {
      height: 150px;
    }
  }
</style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container text-center">
    <h1 class="display-4 fw-bold mb-3">Café Premium de Colombia</h1>
    <p class="lead mb-4">Descubre la excelencia en cada taza. Café 100% colombiano, tostado artesanalmente.</p>
    <div class="d-flex gap-3 justify-content-center">
      <a href="catalogo.php" class="btn btn-primary btn-lg">Explorar catálogo</a>
      <a href="planesSuscripcion.php" class="btn btn-outline-light btn-lg">Suscribirme</a>
    </div>
  </div>
</section>

<!-- Categorías -->
<section class="container mb-5">
  <h2 class="coffee-title text-center mb-4">Nuestras Categorías</h2>
  <div class="row g-3">
    <?php if (!empty($categorias)): ?>
      <?php foreach ($categorias as $cat): ?>
        <div class="col-6 col-md-4 col-lg-2">
          <a href="catalogo.php?categoria=<?php echo $cat['id']; ?>" class="card category-card">
            <?php 
            $imagenSrc = !empty($cat['imagen']) ? htmlspecialchars($cat['imagen']) : 'images/placeholder-category.jpg';
            ?>
            <img src="<?php echo $imagenSrc; ?>" class="card-img" alt="<?php echo htmlspecialchars($cat['nombre']); ?>" onerror="this.src='images/placeholder-category.jpg'">
            <div class="card-img-overlay">
              <h5 class="card-title">
                <?php echo htmlspecialchars($cat['nombre']); ?>
                <span class="category-count"><?php echo $cat['totalProductos']; ?></span>
              </h5>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Categorías por defecto si no hay en BD -->
      <div class="col-6 col-md-4 col-lg-2">
        <a href="catalogo.php" class="card category-card">
          <img src="images/cafeGrano.png" class="card-img" alt="Café en Grano" onerror="this.src='images/placeholder-category.jpg'">
          <div class="card-img-overlay">
            <h5 class="card-title text-white">Café en Grano</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="catalogo.php" class="card category-card">
          <img src="images/cafeMolido.png" class="card-img" alt="Café Molido" onerror="this.src='images/placeholder-category.jpg'">
          <div class="card-img-overlay">
            <h5 class="card-title text-white">Café Molido</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="catalogo.php" class="card category-card">
          <img src="images/bebidas.png" class="card-img" alt="Bebidas" onerror="this.src='images/placeholder-category.jpg'">
          <div class="card-img-overlay">
            <h5 class="card-title text-white">Bebidas</h5>
          </div>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Productos Destacados -->
<section class="container mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="coffee-title mb-0">Productos Destacados</h2>
    <a href="catalogo.php?destacados=1" class="btn btn-outline-secondary">Ver todos</a>
  </div>
  
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="productosDestacados">
    <!-- Skeleton loading -->
    <div class="col">
      <div class="card skeleton-card">
        <div class="bg-secondary" style="height: 250px;"></div>
        <div class="card-body">
          <div class="bg-secondary rounded mb-2" style="height: 20px; width: 70%;"></div>
          <div class="bg-secondary rounded mb-2" style="height: 15px; width: 50%;"></div>
          <div class="bg-secondary rounded" style="height: 30px; width: 40%;"></div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card skeleton-card">
        <div class="bg-secondary" style="height: 250px;"></div>
        <div class="card-body">
          <div class="bg-secondary rounded mb-2" style="height: 20px; width: 70%;"></div>
          <div class="bg-secondary rounded mb-2" style="height: 15px; width: 50%;"></div>
          <div class="bg-secondary rounded" style="height: 30px; width: 40%;"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Sección de Suscripción -->
<section class="subscription-section mb-5">
  <div class="container text-center">
    <h2 class="mb-4">Nunca te quedes sin café</h2>
    <p class="lead mb-4">Suscríbete y recibe tu café favorito directamente en tu puerta cada mes con un 10% de descuento.</p>
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card">
          <div class="card-body p-4">
            <h3 class="coffee-title mb-3">Plan Mensual</h3>
            <p class="mb-4">Selecciona tus cafés favoritos y recíbelos cada mes con envío gratuito.</p>
            <div class="d-grid">
              <a href="planesSuscripcion.php" class="btn btn-primary">Ver planes</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonios -->
<section class="container mb-5">
  <h2 class="coffee-title text-center mb-4">Lo que dicen nuestros clientes</h2>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="testimonial-card">
        <div class="mb-3">
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
        </div>
        <p>"El mejor café que he probado. La suscripción mensual es perfecta."</p>
        <footer class="text-muted">— María G.</footer>
      </div>
    </div>
    <div class="col-md-4">
      <div class="testimonial-card">
        <div class="mb-3">
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
        </div>
        <p>"Calidad excepcional y envío rápido. Totalmente recomendado."</p>
        <footer class="text-muted">— Carlos R.</footer>
      </div>
    </div>
    <div class="col-md-4">
      <div class="testimonial-card">
        <div class="mb-3">
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
        </div>
        <p>"Excelente servicio al cliente. El café es delicioso."</p>
        <footer class="text-muted">— Ana M.</footer>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Toast container -->
<div class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

// Cargar productos destacados
async function cargarProductosDestacados() {
  try {
    const response = await fetch(basePath + 'php/catalogo_api.php?destacados=1&porPagina=8');
    const data = await response.json();
    
    const container = document.getElementById('productosDestacados');
    container.innerHTML = '';
    
    if (data.success && data.data.length > 0) {
      const productosAMostrar = data.data.slice(0, 4);
      
      productosAMostrar.forEach(prod => {
        const imagenSrc = prod.imagen || 'images/placeholder.png';
        const stockBajo = prod.stockDisponible > 0 && prod.stockDisponible <= 5;
        const agotado = prod.stockDisponible <= 0;
        
        container.innerHTML += `
          <div class="col">
            <div class="card featured-product h-100">
              ${!agotado && prod.destacado ? '<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2 z-3">Destacado</span>' : ''}
              ${agotado ? '<span class="badge bg-danger position-absolute top-0 end-0 m-2 z-3">Agotado</span>' : ''}
              <div class="product-img-container">
                <a href="productos.php?id=${prod.id}">
                  <img src="${imagenSrc}" class="product-img" alt="${prod.nombre}" onerror="this.src='images/placeholder.png'">
                </a>
              </div>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">${prod.nombre}</h5>
                <p class="card-text text-muted small">${prod.categoriaNombre || 'Sin categoría'}</p>
                <div class="mt-auto">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5 fw-bold">$${Number(prod.precio).toLocaleString('es-CO')}</span>
                    <button class="btn btn-primary btn-sm" onclick="agregarAlCarrito(${prod.id})" ${agotado ? 'disabled' : ''}>
                      <i class="bi bi-cart-plus"></i> ${agotado ? 'Agotado' : 'Añadir'}
                    </button>
                  </div>
                  ${stockBajo ? '<small class="text-warning mt-1 d-block"><i class="bi bi-exclamation-triangle"></i> Pocas unidades</small>' : ''}
                </div>
              </div>
            </div>
          </div>`;
      });
    } else {
      container.innerHTML = `
        <div class="col-12 text-center py-5">
          <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
          <p class="text-muted mt-3">No hay productos destacados disponibles</p>
          <a href="catalogo.php" class="btn btn-primary">Ver catálogo completo</a>
        </div>`;
    }
  } catch (e) {
    console.error('Error al cargar productos destacados:', e);
  }
}

// Agregar al carrito
async function agregarAlCarrito(productoId) {
  try {
    const formData = new FormData();
    formData.append('productoId', productoId);
    formData.append('cantidad', 1);
    
    const response = await fetch(basePath + 'php/carrito_api.php?action=agregar', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      mostrarToast('Producto agregado al carrito', 'success');
      if (typeof actualizarContadorCarrito === 'function') {
        actualizarContadorCarrito();
      }
    } else {
      mostrarToast(data.message || 'Error al agregar al carrito', 'danger');
    }
  } catch (e) {
    mostrarToast('Error de conexión', 'danger');
  }
}

function mostrarToast(mensaje, tipo = 'success') {
  const container = document.querySelector('.toast-container');
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
  toast.setAttribute('role', 'alert');
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${mensaje}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  
  container.appendChild(toast);
  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();
  
  toast.addEventListener('hidden.bs.toast', function() {
    toast.remove();
  });
}

document.addEventListener('DOMContentLoaded', function() {
  cargarProductosDestacados();
});
</script>
</body>
</html>