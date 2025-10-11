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
   .navbar {
    background-color: var(--dark-brown);
  }
  
  .navbar-brand, .nav-link {
    color: var(--cream);
  }
  
  .nav-link:hover {
    color: #fff;
  }
  
  .search-form {
    max-width: 400px;
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
          <a class="nav-link active" href="home.php">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="catalogo.php">Catálogo</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="planesSuscripcion.php">Suscripciones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="SobreNosotros.php">Sobre Nosotros</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contactenos.php">Contacto</a>
        </li>
      </ul>
      <form class="d-flex search-form">
        <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Search">
        <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
      </form>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="login.php"><i class="bi bi-person"></i> Iniciar Sesión</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="carritoCompra.php"><i class="bi bi-cart"></i> Carrito (0)</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</html>