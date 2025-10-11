<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Café en Liniea- Detalle de Producto</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles.css">
<style>
  
  
  .product-main-image {
    height: 400px;
    object-fit: cover;
    border-radius: 10px;
  }
  .category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
    height: 80px;
  }
  
  .category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  
  .category-card img {
    height: 100%;
    object-fit: cover;
  }
  
  .category-card .card-img-overlay {
    background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0.2));
    display: flex;
    align-items: flex-end;
  }

 

  .featured-product {
    transition: transform 0.3s ease;
  }
  
  .featured-product:hover {
    transform: translateY(-5px);
  }
  .featured-product .product-img-container {
    height: 200px;
    width: 90%;
    overflow: hidden;
    position: relative;
  }
  
  .featured-product .product-img {
    width: auto;
    height: 50%;
    object-fit: cover; 
  }
  .thumbnail {
    cursor: pointer;
    transition: opacity 0.3s;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
  }
  
  .thumbnail:hover {
    opacity: 0.8;
  }
  
  .review {
    border-bottom: 1px solid #eee;
  }
  
  .review:last-child {
    border-bottom: none;
  }
  
  .star-rating {
    color: #ffc107;
  }
  
  .quantity-selector {
    max-width: 120px;
  }
</style>
</head>
<body>
<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  
  <div class="row">
    <!-- Imágenes del producto -->
    <div class="col-lg-6 mb-4">
      <img src="images/cafepremium.png" class="img-fluid product-main-image mb-3" alt="Café Colombiano Premium">
      
      <div class="row">
        <div class="col-3">
          <img src="images/cafegrano1.png" class="img-fluid thumbnail" alt="Café Colombiano 1">
        </div>
        <div class="col-3">
          <img src="images/cafegrano2.png" class="img-fluid thumbnail" alt="Café Colombiano 2">
        </div>
        <div class="col-3">
          <img src="images/cafegrano3.png" class="img-fluid thumbnail" alt="Café Colombiano 3">
        </div>
        <div class="col-3">
          <img src="images/cafegrano4.png" class="img-fluid thumbnail" alt="Café Colombiano 4">
        </div>
      </div>
    </div>
    
    <!-- Información del producto -->
    <div class="col-lg-6">
      <h1 class="coffee-title mb-2">Café Colombiano Premium</h1>
      <p class="text-muted mb-3">Café en grano | Ref: CF-COL-001</p>
      
      <div class="d-flex align-items-center mb-3">
        <div class="star-rating me-2">
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star-half"></i>
        </div>
        <span>(24 reseñas)</span>
      </div>
      
      <h2 class="fs-2 fw-bold mb-3">$25.000</h2>
      
      <p class="mb-4">Café de origen único cultivado en las montañas de Colombia a más de 1.700 metros sobre el nivel del mar. Presenta notas de chocolate, caramelo y un sutil toque cítrico. Tostado medio que resalta sus cualidades aromáticas.</p>
      
      <div class="mb-4">
        <h5>Características:</h5>
        <ul>
          <li>Origen: Colombia, región de Huila</li>
          <li>Variedad: Arábica</li>
          <li>Altitud: 1.700 - 2.000 msnm</li>
          <li>Proceso: Lavado</li>
          <li>Tostado: Medio</li>
          <li>Intensidad: 7/10</li>
        </ul>
      </div>
      
      <div class="mb-4">
        <h5>Presentación:</h5>
        <div class="btn-group" role="group" aria-label="Presentación">
          <input type="radio" class="btn-check" name="presentation" id="presentation1" autocomplete="off" checked>
          <label class="btn btn-outline-secondary" for="presentation1">250g</label>
          
          <input type="radio" class="btn-check" name="presentation" id="presentation2" autocomplete="off">
          <label class="btn btn-outline-secondary" for="presentation2">500g</label>
          
          <input type="radio" class="btn-check" name="presentation" id="presentation3" autocomplete="off">
          <label class="btn btn-outline-secondary" for="presentation3">1kg</label>
        </div>
      </div>
      
      <div class="mb-4">
        <h5>Molienda:</h5>
        <div class="btn-group" role="group" aria-label="Molienda">
          <input type="radio" class="btn-check" name="grind" id="grind1" autocomplete="off" checked>
          <label class="btn btn-outline-secondary" for="grind1">Grano</label>
          
          <input type="radio" class="btn-check" name="grind" id="grind2" autocomplete="off">
          <label class="btn btn-outline-secondary" for="grind2">Gruesa</label>
          
          <input type="radio" class="btn-check" name="grind" id="grind3" autocomplete="off">
          <label class="btn btn-outline-secondary" for="grind3">Media</label>
          
          <input type="radio" class="btn-check" name="grind" id="grind4" autocomplete="off">
          <label class="btn btn-outline-secondary" for="grind4">Fina</label>
        </div>
      </div>
      
      <div class="d-flex align-items-center mb-4">
        <div class="input-group quantity-selector me-3">
          <button class="btn btn-outline-secondary" type="button"><i class="bi bi-dash"></i></button>
          <input type="text" class="form-control text-center" value="1" aria-label="Cantidad">
          <button class="btn btn-outline-secondary" type="button"><i class="bi bi-plus"></i></button>
        </div>
        
        <button class="btn btn-primary btn-lg"><i class="bi bi-cart-plus"></i> Añadir al carrito</button>
      </div>
      
      <div class="d-flex align-items-center">
        <button class="btn btn-outline-secondary me-2"><i class="bi bi-heart"></i> Añadir a favoritos</button>
        <button class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i> Suscripción periódica</button>
      </div>
    </div>
  </div>
  
  <!-- Pestañas de información adicional -->
  <div class="row mt-5">
    <div class="col-12">
      <ul class="nav nav-tabs" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Descripción</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="false">Especificaciones</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Reseñas (24)</button>
        </li>
      </ul>
      <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
          <h4>Sobre este café</h4>
          <p>Nuestro Café Colombiano Premium es cultivado por pequeños productores en la región de Huila, Colombia, a una altitud entre 1.700 y 2.000 metros sobre el nivel del mar. Esta altitud, combinada con el clima único de la región, crea condiciones perfectas para el desarrollo de granos de café con características excepcionales.</p>
          
          <p>Los caficultores seleccionan manualmente solo los granos más maduros, que luego son procesados mediante el método de lavado tradicional. Este proceso resalta la limpieza y brillantez en taza característica de los cafés colombianos.</p>
          
          <p>El tostado medio que aplicamos a estos granos está diseñado para resaltar sus cualidades naturales sin enmascarar sus notas distintivas. El resultado es una taza con cuerpo medio-alto, acidez brillante y equilibrada, y un perfil de sabor que incluye notas de chocolate con leche, caramelo y un sutil toque cítrico en el retrogusto.</p>
          
          <p>Este café es ideal para preparaciones en prensa francesa, pour-over, o espresso, adaptándose perfectamente a diferentes métodos de extracción.</p>
        </div>
        <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
          <div class="row">
            <div class="col-md-6">
              <h4>Detalles del producto</h4>
              <table class="table">
                <tbody>
                  <tr>
                    <th scope="row">Origen</th>
                    <td>Colombia, región de Huila</td>
                  </tr>
                  <tr>
                    <th scope="row">Variedad</th>
                    <td>100% Arábica (Caturra, Colombia, Castillo)</td>
                  </tr>
                  <tr>
                    <th scope="row">Altitud</th>
                    <td>1.700 - 2.000 msnm</td>
                  </tr>
                  <tr>
                    <th scope="row">Proceso</th>
                    <td>Lavado</td>
                  </tr>
                  <tr>
                    <th scope="row">Tostado</th>
                    <td>Medio (Agtron: 60-65)</td>
                  </tr>
                  <tr>
                    <th scope="row">Intensidad</th>
                    <td>7/10</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-6">
              <h4>Perfil de sabor</h4>
              <table class="table">
                <tbody>
                  <tr>
                    <th scope="row">Notas principales</th>
                    <td>Chocolate con leche, caramelo, cítricos</td>
                  </tr>
                  <tr>
                    <th scope="row">Cuerpo</th>
                    <td>Medio-alto</td>
                  </tr>
                  <tr>
                    <th scope="row">Acidez</th>
                    <td>Media, brillante</td>
                  </tr>
                  <tr>
                    <th scope="row">Dulzor</th>
                    <td>Alto</td>
                  </tr>
                  <tr>
                    <th scope="row">Postgusto</th>
                    <td>Prolongado, con notas cítricas</td>
                  </tr>
                  <tr>
                    <th scope="row">Métodos recomendados</th>
                    <td>Prensa francesa, pour-over, espresso</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
          <h4>Reseñas de clientes</h4>
          
          <div class="mb-4">
            <div class="d-flex align-items-center mb-2">
              <div class="star-rating me-2">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
              </div>
              <span class="fw-bold">4.6 de 5</span>
            </div>
            <div class="progress mb-1" style="height: 10px;">
              <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">5★ (85%)</div>
            </div>
            <div class="progress mb-1" style="height: 10px;">
              <div class="progress-bar bg-success" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">4★ (10%)</div>
            </div>
            <div class="progress mb-1" style="height: 10px;">
              <div class="progress-bar bg-warning" role="progressbar" style="width: 3%" aria-valuenow="3" aria-valuemin="0" aria-valuemax="100">3★ (3%)</div>
            </div>
            <div class="progress mb-1" style="height: 10px;">
              <div class="progress-bar bg-danger" role="progressbar" style="width: 1%" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100">2★ (1%)</div>
            </div>
            <div class="progress mb-1" style="height: 10px;">
              <div class="progress-bar bg-danger" role="progressbar" style="width: 1%" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100">1★ (1%)</div>
            </div>
          </div>
          
          <!-- Reseña individual -->
          <div class="review py-3">
            <div class="d-flex justify-content-between mb-2">
              <div>
                <h5 class="mb-0">Carlos Rodríguez</h5>
                <div class="star-rating">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                </div>
              </div>
              <small class="text-muted">15/10/2023</small>
            </div>
            <p>Excelente café, con un aroma increíble desde que abres el paquete. Las notas de chocolate son muy evidentes y tiene un equilibrio perfecto entre acidez y dulzor. Lo preparo en prensa francesa y el resultado es espectacular. Definitivamente volveré a comprarlo.</p>
          </div>
          
          <!-- Reseña individual -->
          <div class="review py-3">
            <div class="d-flex justify-content-between mb-2">
              <div>
                <h5 class="mb-0">Ana Martínez</h5>
                <div class="star-rating">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star"></i>
                </div>
              </div>
              <small class="text-muted">02/10/2023</small>
            </div>
            <p>Muy buen café, con un sabor distintivo y agradable. Lo único que me gustaría es que tuviera un poco más de cuerpo para mi gusto personal, pero en general estoy muy satisfecha con la compra. Lo recomendaría especialmente para quienes disfrutan de cafés con notas dulces.</p>
          </div>
          
          <!-- Reseña individual -->
          <div class="review py-3">
            <div class="d-flex justify-content-between mb-2">
              <div>
                <h5 class="mb-0">Javier López</h5>
                <div class="star-rating">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-half"></i>
                </div>
              </div>
              <small class="text-muted">25/09/2023</small>
            </div>
            <p>Como barista aficionado, puedo decir que este es uno de los mejores cafés colombianos que he probado. La frescura del tostado es evidente y las notas de sabor son exactamente como las describen. Funciona perfectamente en espresso y también en métodos de filtrado.</p>
          </div>
          
          <!-- Formulario de reseña -->
          <div class="mt-4">
            <h5>Deja tu reseña</h5>
            <form>
              <div class="mb-3">
                <label for="rating" class="form-label">Calificación</label>
                <div class="star-rating fs-3">
                  <i class="bi bi-star"></i>
                  <i class="bi bi-star"></i>
                  <i class="bi bi-star"></i>
                  <i class="bi bi-star"></i>
                  <i class="bi bi-star"></i>
                </div>
              </div>
              <div class="mb-3">
                <label for="reviewText" class="form-label">Tu reseña</label>
                <textarea class="form-control" id="reviewText" rows="3" placeholder="Comparte tu experiencia con este producto..."></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Enviar reseña</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Productos relacionados -->
  <div class="mt-5">
    <h3 class="coffee-title mb-4">También te puede interesar</h3>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
      <!-- Producto relacionado 1 -->
      <div class="col">
        <div class="card featured-product h-100">
          <img src="images/cafeMolido.png" class="card-img-top product-img" alt="Café Brasileño">
          <div class="card-body">
            <h5 class="card-title">Café Brasileño Suave</h5>
            <p class="card-text text-muted small">Café molido</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="fs-5 fw-bold">$22.000</span>
              <button class="btn btn-sm btn-primary"><i class="bi bi-cart-plus"></i></button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Producto relacionado 2 -->
      <div class="col">
        <div class="card featured-product h-100">
          <img src="images/filtropapel.png" class="card-img-top product-img" alt="Filtros de Café">
          <div class="card-body">
            <h5 class="card-title">Filtros de Papel Premium</h5>
            <p class="card-text text-muted small">Accesorios</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="fs-5 fw-bold">$12.000</span>
              <button class="btn btn-sm btn-primary"><i class="bi bi-cart-plus"></i></button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Producto relacionado 3 -->
      <div class="col">
        <div class="card featured-product h-100">
          <img src="images/cafeperuano.png" class="card-img-top product-img" alt="Café Peruano">
          <div class="card-body">
            <h5 class="card-title">Café Peruano Orgánico</h5>
            <p class="card-text text-muted small">Café en grano</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="fs-5 fw-bold">$26.000</span>
              <button class="btn btn-sm btn-primary"><i class="bi bi-cart-plus"></i></button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Producto relacionado 4 -->
      <div class="col">
        <div class="card featured-product h-100">
          <img src="images/dripper.png" class="card-img-top product-img" alt="Dripper">
          <div class="card-body">
            <h5 class="card-title">Dripper de Cerámica</h5>
            <p class="card-text text-muted small">Accesorios</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="fs-5 fw-bold">$38.000</span>
              <button class="btn btn-sm btn-primary"><i class="bi bi-cart-plus"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script src="scripts.js"></script>
</body>
</html>

