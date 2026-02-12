<?php
// Verificar si hay una sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
$usuarioAutenticado = isset($_SESSION['usuario_id']);
$nombreUsuario = $usuarioAutenticado ? $_SESSION['usuario_nombre'] : '';
$rolUsuario = $usuarioAutenticado ? $_SESSION['usuario_rol'] : '';

// Obtener la página actual para marcar el menú activo
$paginaActual = basename($_SERVER['PHP_SELF']);
?>

<style>
  .navbar {
    background-color: var(--dark-brown);
  }
  
  .navbar-brand, .nav-link {
    color: var(--cream) !important;
  }
  
  .nav-link {
    position: relative;
    transition: all 0.3s ease;
    border-radius: 6px;
    margin: 0 2px;
  }
  
  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.15);
    color: #fff !important;
  }
  
  /* Estilo para el link activo - recuadro con tono más claro */
  .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff !important;
    font-weight: 500;
  }
  
  /* Animación suave al hacer hover sobre el link activo */
  .nav-link.active:hover {
    background-color: rgba(255, 255, 255, 0.25);
  }
  
  .search-form {
    max-width: 400px;
  }
  
  .dropdown-menu {
    background-color: #fff;
  }
  
  .dropdown-item {
    color: #333;
  }
  
  .dropdown-item:hover {
    background-color: var(--dark-brown, #4a2511);
    color: #fff;
  }
  
  .cart-badge {
    position: relative;
    top: -8px;
    background-color: #dc3545;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 0.7rem;
    margin-left: 3px;
    display: none;
  }
  
  /* Asegurar que el texto del navbar siempre sea claro */
  .navbar-nav .nav-link,
  .navbar-nav .nav-link.active {
    color: var(--cream, #f5f5dc) !important;
  }
  
  .navbar-nav .nav-link:hover {
    color: #ffffff !important;
  }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="home.php">Café en Línea</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo ($paginaActual == 'home.php') ? 'active' : ''; ?>" href="home.php">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($paginaActual == 'catalogo.php') ? 'active' : ''; ?>" href="catalogo.php">Catálogo</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($paginaActual == 'planesSuscripcion.php' || $paginaActual == 'misSuscripciones.php') ? 'active' : ''; ?>" href="planesSuscripcion.php">Suscripciones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (strtolower($paginaActual) == 'sobrenosotros.php') ? 'active' : ''; ?>" href="sobreNosotros.php">Sobre Nosotros</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($paginaActual == 'contactenos.php') ? 'active' : ''; ?>" href="contactenos.php">Contacto</a>
        </li>
      </ul>
      
      <!-- Formulario de búsqueda -->
      <form class="d-flex search-form me-3" id="formBusquedaNavbar" onsubmit="return buscarDesdeNavbar()">
        <input type="text" class="form-control" id="inputBusquedaNavbar" placeholder="Buscar productos..." aria-label="Buscar productos">
        <button class="btn btn-outline-light" type="submit">
          <i class="bi bi-search"></i>
        </button>
      </form>
      
      <ul class="navbar-nav ms-auto">
        <?php if ($usuarioAutenticado): ?>
          <!-- Usuario autenticado: Mostrar nombre del usuario con dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nombreUsuario); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
              <?php if ($rolUsuario === 'administrador'): ?>
                <li><a class="dropdown-item" href="administrador.php"><i class="bi bi-speedometer2"></i> Panel de Administración</a></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="perfilUsuario.php"><i class="bi bi-person"></i> Mi Perfil</a></li>
                <li><a class="dropdown-item" href="historialOrdenes.php"><i class="bi bi-clock-history"></i> Mis Pedidos</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="php/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
            </ul>
          </li>
        <?php else: ?>
          <!-- Usuario NO autenticado: Mostrar link de Iniciar Sesión -->
          <li class="nav-item">
            <a class="nav-link" href="login.php"><i class="bi bi-person"></i> Iniciar Sesión</a>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link <?php echo ($paginaActual == 'carritoCompra.php') ? 'active' : ''; ?>" href="carritoCompra.php">
            <i class="bi bi-cart"></i> Carrito
            <span class="cart-badge" id="cartBadgeNavbar">0</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
// Función para buscar desde el navbar
function buscarDesdeNavbar() {
  const query = document.getElementById('inputBusquedaNavbar').value.trim();
  
  // Si estamos en la página del catálogo, no redirigir, solo buscar
  if (window.location.pathname.includes('catalogo.php')) {
    // Si existe la función cargarProductos del catálogo, úsala
    if (typeof filtros !== 'undefined' && typeof cargarProductos === 'function') {
      filtros.busqueda = query;
      filtros.pagina = 1;
      cargarProductos();
      // Actualizar también el input de búsqueda del catálogo si existe
      const inputCatalogo = document.getElementById('inputBusqueda');
      if (inputCatalogo) {
        inputCatalogo.value = query;
      }
    }
  } else {
    // Si estamos en otra página, redirigir al catálogo con el parámetro de búsqueda
    if (query) {
      window.location.href = 'catalogo.php?busqueda=' + encodeURIComponent(query);
    } else {
      window.location.href = 'catalogo.php';
    }
  }
  
  return false; // Prevenir el submit normal del formulario
}

// Actualizar el badge del carrito cuando cargue la página
document.addEventListener('DOMContentLoaded', async function() {
  const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
  
  try {
    const response = await fetch(basePath + 'php/carrito_api.php?action=count');
    const data = await response.json();
    if (data.success && data.count > 0) {
      const badge = document.getElementById('cartBadgeNavbar');
      if (badge) {
        badge.textContent = data.count;
        badge.style.display = 'inline-block';
      }
    }
  } catch(e) {
    // Si hay error, no mostrar nada
  }
});
</script>