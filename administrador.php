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
<title>Dashboard - Administración</title>
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
  
  .sidebar.hidden {
    margin-left: -250px;
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
  
  .main-content.expanded {
    margin-left: 0;
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
  
  /* Stats cards */
  .stat-card {
    border-radius: 10px;
    padding: 20px;
    background: white;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
  }
  
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  }
  
  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }
  
  .stat-icon.blue { background-color: #e7f3ff; color: #0066cc; }
  .stat-icon.green { background-color: #d4edda; color: #28a745; }
  .stat-icon.orange { background-color: #fff3cd; color: #ff8c00; }
  .stat-icon.red { background-color: #f8d7da; color: #dc3545; }
  
  .chart-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
    <a href="administrador.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="admin_products.php"><i class="bi bi-box-seam"></i> Productos</a>
    <a href="admin_orders.php"><i class="bi bi-bag"></i> Pedidos</a>
    <a href="admin_users.php"><i class="bi bi-people"></i> Clientes</a>
    <a href="admin_categories.php"><i class="bi bi-tags"></i> Categorías</a>
    <div class="divider"></div>
    <a href="home.php"><i class="bi bi-house"></i> Ver Tienda</a>
    <a href="php/logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
  </div>
</nav>

<div class="main-content" id="mainContent">
  <div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
      <div class="col-12">
        <h2 class="mb-1">Dashboard</h2>
        <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></p>
      </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon blue">
              <i class="bi bi-box-seam"></i>
            </div>
            <div class="ms-3 flex-grow-1">
              <p class="text-muted mb-0 small">Total Productos</p>
              <h3 class="mb-0" id="totalProductos">-</h3>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon green">
              <i class="bi bi-bag-check"></i>
            </div>
            <div class="ms-3 flex-grow-1">
              <p class="text-muted mb-0 small">Pedidos Hoy</p>
              <h3 class="mb-0" id="pedidosHoy">-</h3>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon orange">
              <i class="bi bi-people"></i>
            </div>
            <div class="ms-3 flex-grow-1">
              <p class="text-muted mb-0 small">Total Clientes</p>
              <h3 class="mb-0" id="totalClientes">-</h3>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
          <div class="d-flex align-items-center">
            <div class="stat-icon red">
              <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="ms-3 flex-grow-1">
              <p class="text-muted mb-0 small">Ventas Hoy</p>
              <h3 class="mb-0" id="ventasHoy">$0</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Charts & Tables -->
    <div class="row g-4">
      <!-- Últimos Pedidos -->
      <div class="col-lg-8">
        <div class="chart-card">
          <h5 class="mb-3">Últimos Pedidos</h5>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>N° Seguimiento</th>
                  <th>Cliente</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody id="tablaUltimosPedidos">
                <tr><td colspan="5" class="text-center">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <!-- Productos con Bajo Stock -->
      <div class="col-lg-4">
        <div class="chart-card">
          <h5 class="mb-3">Stock Bajo</h5>
          <div id="listaStockBajo">
            <div class="text-center py-3">
              <div class="spinner-border spinner-border-sm" role="status"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Estadísticas Adicionales -->
    <div class="row g-4 mt-2">
      <div class="col-lg-4">
        <div class="chart-card">
          <h5 class="mb-3">Resumen de Pedidos por Estado</h5>
          <div style="max-width: 300px; margin: 0 auto;">
            <canvas id="pedidosChart"></canvas>
          </div>
        </div>
      </div>
      
      <div class="col-lg-8">
        <div class="chart-card">
          <h5 class="mb-3">Top 5 Productos Más Vendidos</h5>
          <div id="topProductos">
            <div class="text-center py-3">
              <div class="spinner-border spinner-border-sm" role="status"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

// Toggle sidebar
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
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

const estadoBadge = {
  'pendiente': 'bg-warning text-dark',
  'confirmado': 'bg-info text-dark',
  'en_proceso': 'bg-primary',
  'enviado': 'bg-info',
  'entregado': 'bg-success',
  'cancelado': 'bg-danger'
};

async function cargarStats() {
  try {
    const res = await fetch(basePath + 'php/admin_api.php?resource=stats&action=dashboard');
    const data = await res.json();
    
    if (data.success) {
      const stats = data.data;
      document.getElementById('totalProductos').textContent = stats.totalProductos || 0;
      document.getElementById('pedidosHoy').textContent = stats.pedidosHoy || 0;
      document.getElementById('totalClientes').textContent = stats.totalClientes || 0;
      document.getElementById('ventasHoy').textContent = '$' + Number(stats.ventasHoy || 0).toLocaleString('es-CO');
      
      // Últimos pedidos
      if (stats.ultimosPedidos && stats.ultimosPedidos.length > 0) {
        const tbody = document.getElementById('tablaUltimosPedidos');
        tbody.innerHTML = '';
        stats.ultimosPedidos.slice(0, 5).forEach(p => {
          tbody.innerHTML += `
            <tr>
              <td><strong>${p.numeroSeguimiento || '#' + p.id}</strong></td>
              <td>${p.clienteNombre}</td>
              <td>$${Number(p.total).toLocaleString('es-CO')}</td>
              <td><span class="badge ${estadoBadge[p.estado]}">${p.estado}</span></td>
              <td>${new Date(p.fechaCreacion).toLocaleDateString('es-CO')}</td>
            </tr>`;
        });
      }
      
      // Productos con bajo stock
      if (stats.stockBajo && stats.stockBajo.length > 0) {
        const lista = document.getElementById('listaStockBajo');
        lista.innerHTML = '';
        stats.stockBajo.forEach(p => {
          lista.innerHTML += `
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
              <img src="${p.imagen || 'images/placeholder.png'}" style="width:50px;height:50px;object-fit:cover;border-radius:5px;" onerror="this.src='images/placeholder.png'">
              <div class="ms-3 flex-grow-1">
                <div class="fw-bold">${p.nombre}</div>
                <small class="text-danger">Stock: ${p.stockDisponible}</small>
              </div>
            </div>`;
        });
      } else {
        document.getElementById('listaStockBajo').innerHTML = '<p class="text-muted text-center">No hay productos con stock bajo</p>';
      }
      
      // Gráfico de pedidos
      if (stats.pedidosPorEstado) {
        crearGraficoPedidos(stats.pedidosPorEstado);
      }
      
      // Top productos
      if (stats.topProductos && stats.topProductos.length > 0) {
        const topDiv = document.getElementById('topProductos');
        topDiv.innerHTML = '';
        stats.topProductos.forEach((p, index) => {
          topDiv.innerHTML += `
            <div class="d-flex align-items-center mb-3">
              <div class="fw-bold me-3" style="color: var(--coffee-brown);">#${index + 1}</div>
              <img src="${p.imagen || 'images/placeholder.png'}" style="width:40px;height:40px;object-fit:cover;border-radius:5px;" onerror="this.src='images/placeholder.png'">
              <div class="ms-3 flex-grow-1">
                <div class="fw-bold">${p.nombre}</div>
                <small class="text-muted">${p.total_vendidos} vendidos</small>
              </div>
            </div>`;
        });
      }
    }
  } catch (e) {
    console.error('Error al cargar estadísticas:', e);
  }
}

function crearGraficoPedidos(pedidosPorEstado) {
  const ctx = document.getElementById('pedidosChart');
  
  const labels = [];
  const data = [];
  const colors = [];
  
  const colorMap = {
    'pendiente': '#ffc107',
    'confirmado': '#17a2b8',
    'en_proceso': '#007bff',
    'enviado': '#0dcaf0',
    'entregado': '#28a745',
    'cancelado': '#dc3545'
  };
  
  pedidosPorEstado.forEach(item => {
    labels.push(item.estado.charAt(0).toUpperCase() + item.estado.slice(1));
    data.push(item.total);
    colors.push(colorMap[item.estado] || '#6c757d');
  });
  
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: data,
        backgroundColor: colors
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 12,
            font: {
              size: 11
            }
          }
        }
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  cargarStats();
  
  // Recargar cada 30 segundos
  setInterval(cargarStats, 30000);
});
</script>
</body>
</html>