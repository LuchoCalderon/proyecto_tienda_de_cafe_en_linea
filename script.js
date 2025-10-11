

  document.addEventListener('DOMContentLoaded', function() {
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  const navbarCollapse = document.querySelector('.navbar-collapse');
  
  navLinks.forEach(function(link) {
    link.addEventListener('click', function() {
      if (window.innerWidth < 992) {
        const bsCollapse = new bootstrap.Collapse(navbarCollapse);
        bsCollapse.hide();
      }
    });
  });
});

// Función para mostrar la contraseña
function showPassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(inputId + '-eye');
    
    passwordInput.type = 'text';
    eyeIcon.className = 'bi bi-eye-slash';
}

function hidePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(inputId + '-eye');
    
    passwordInput.type = 'password';
    eyeIcon.className = 'bi bi-eye';
}

// Alternar barra lateral
document.querySelector('.toggle-sidebar').addEventListener('click', function() {
  document.querySelector('.sidebar').classList.toggle('active');
  document.querySelector('.main-content').classList.toggle('active');
});

// fecha actual
const options = { year: 'numeric', month: 'long', day: 'numeric' };
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('es-ES', options);
