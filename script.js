document.addEventListener('DOMContentLoaded', function() {
  // Cerrar navbar en móvil al hacer clic en un enlace
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  const navbarCollapse = document.querySelector('.navbar-collapse');
  const bootstrap = window.bootstrap; // Declare the bootstrap variable
  
  navLinks.forEach(function(link) {
    link.addEventListener('click', function() {
      if (window.innerWidth < 992 && navbarCollapse) {
        const bsCollapse = new bootstrap.Collapse(navbarCollapse);
        bsCollapse.hide();
      }
    });
  });

  // Alternar barra lateral (solo si existe el elemento)
  const toggleSidebar = document.querySelector('.toggle-sidebar');
  const sidebar = document.querySelector('.sidebar');
  const mainContent = document.querySelector('.main-content');
  
  if (toggleSidebar && sidebar && mainContent) {
    toggleSidebar.addEventListener('click', function() {
      sidebar.classList.toggle('active');
      mainContent.classList.toggle('active');
    });
  }

  // Fecha actual (solo si existe el elemento)
  const currentDateElement = document.getElementById('currentDate');
  if (currentDateElement) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    currentDateElement.textContent = new Date().toLocaleDateString('es-ES', options);
  }
});

// Función para mostrar la contraseña
function showPassword(inputId) {
  const passwordInput = document.getElementById(inputId);
  const eyeIcon = document.getElementById(inputId + '-eye');
  
  if (passwordInput && eyeIcon) {
    passwordInput.type = 'text';
    eyeIcon.className = 'bi bi-eye-slash';
  }
}

function hidePassword(inputId) {
  const passwordInput = document.getElementById(inputId);
  const eyeIcon = document.getElementById(inputId + '-eye');
  
  if (passwordInput && eyeIcon) {
    passwordInput.type = 'password';
    eyeIcon.className = 'bi bi-eye';
  }
}

// =============================================
// SUBIDA DE FOTO DE PERFIL (AVATAR)
// =============================================

const inputFotoPerfil = document.getElementById('inputFotoPerfil');
if (inputFotoPerfil) {
  inputFotoPerfil.addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Validar tipo de archivo
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
      showUploadAlert('Solo se permiten imagenes (JPG, PNG, GIF, WEBP)', 'danger');
      return;
    }

    // Validar tamano (maximo 5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
      showUploadAlert('La imagen no debe superar los 5MB', 'danger');
      return;
    }

    // Mostrar vista previa inmediata
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('avatarPreview');
      if (preview.tagName === 'IMG') {
        preview.src = e.target.result;
      } else {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.alt = 'Foto de perfil';
        img.className = 'avatar';
        img.id = 'avatarPreview';
        preview.parentNode.replaceChild(img, preview);
      }
    };
    reader.readAsDataURL(file);

    // Subir al servidor
    const formData = new FormData();
    formData.append('avatar', file);

    const uploadProgress = document.getElementById('uploadProgress');
    const uploadAlert = document.getElementById('uploadAlert');
    
    if (uploadProgress) uploadProgress.classList.remove('d-none');
    if (uploadAlert) uploadAlert.classList.add('d-none');

    // Construir la URL correctamente
    const basePath = ''; // Declare the basePath variable
    const uploadUrl = basePath + 'php/upload_avatar.php';
    console.log('[v0] URL de subida:', uploadUrl);
    console.log('[v0] Archivo:', file.name, file.type, file.size);

    try {
      const response = await fetch(uploadUrl, {
        method: 'POST',
        body: formData
      });

      console.log('[v0] Status:', response.status);
      console.log('[v0] Status Text:', response.statusText);

      // Obtener el texto de la respuesta primero
      const responseText = await response.text();
      console.log('[v0] Respuesta del servidor:', responseText);

      // Intentar parsear como JSON
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error('[v0] Error al parsear JSON:', parseError);
        showUploadAlert('Error del servidor: ' + responseText.substring(0, 200), 'danger');
        return;
      }

      if (data.success) {
        showUploadAlert('Foto actualizada correctamente', 'success');
      } else {
        showUploadAlert(data.message || 'Error al subir la imagen', 'danger');
      }
    } catch (error) {
      console.error('[v0] Error de fetch:', error);
      showUploadAlert('Error: ' + error.message, 'danger');
  } finally {
    const uploadProgress = document.getElementById('uploadProgress');
    if (uploadProgress) uploadProgress.classList.add('d-none');
  }
  });
}

function showUploadAlert(message, type) {
  const uploadAlert = document.getElementById('uploadAlert');
  if (uploadAlert) {
    uploadAlert.textContent = message;
    uploadAlert.className = `alert alert-${type}`;
    uploadAlert.classList.remove('d-none');
  }
}
