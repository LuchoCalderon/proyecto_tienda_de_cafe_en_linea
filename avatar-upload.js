// Script para manejar la subida de foto de perfil

document.addEventListener('DOMContentLoaded', function() {
    console.log('Avatar upload script cargado');
    
    const avatarEdit = document.querySelector('.avatar-edit');
    const avatarImg = document.querySelector('.avatar');
    
    if (!avatarEdit) {
        console.error('No se encontró el elemento .avatar-edit');
        return;
    }
    
    if (!avatarImg) {
        console.error('No se encontró el elemento .avatar');
        return;
    }
    
    console.log('Elementos de avatar encontrados');
    
    // Crear input file oculto
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/jpg,image/png,image/gif,image/webp';
    fileInput.style.display = 'none';
    fileInput.id = 'avatarFileInput';
    document.body.appendChild(fileInput);
    
    console.log('Input file creado');
    
    // Al hacer clic en el ícono de edición
    avatarEdit.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Click en avatar-edit');
        fileInput.click();
    });
    
    // También permitir click en toda la imagen
    const avatarContainer = document.querySelector('.avatar-container');
    if (avatarContainer) {
        avatarContainer.style.cursor = 'pointer';
        avatarContainer.addEventListener('click', function(e) {
            if (e.target !== avatarEdit && !avatarEdit.contains(e.target)) {
                console.log('Click en avatar-container');
                fileInput.click();
            }
        });
    }
    
    // Cuando se selecciona un archivo
    fileInput.addEventListener('change', function(e) {
        console.log('Archivo seleccionado');
        const file = e.target.files[0];
        
        if (!file) {
            console.log('No se seleccionó ningún archivo');
            return;
        }
        
        console.log('Archivo:', file.name, 'Tipo:', file.type, 'Tamaño:', file.size);
        
        // Validar tipo de archivo
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            mostrarAlerta('Por favor selecciona una imagen válida (JPG, PNG, GIF o WEBP)', 'danger');
            fileInput.value = ''; // Limpiar input
            return;
        }
        
        // Validar tamaño (5MB)
        if (file.size > 5 * 1024 * 1024) {
            mostrarAlerta('La imagen es demasiado grande. Tamaño máximo: 5MB', 'danger');
            fileInput.value = ''; // Limpiar input
            return;
        }
        
        // Previsualizar imagen
        const reader = new FileReader();
        reader.onload = function(e) {
            console.log('Imagen cargada para previsualización');
            avatarImg.src = e.target.result;
        };
        reader.onerror = function(e) {
            console.error('Error al leer el archivo:', e);
        };
        reader.readAsDataURL(file);
        
        // Subir archivo
        subirAvatar(file);
    });
});

function subirAvatar(file) {
    console.log('Iniciando subida de avatar');
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    console.log('FormData creado con archivo:', file.name);
    
    // Mostrar indicador de carga
    const avatarContainer = document.querySelector('.avatar-container');
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'avatar-loading';
    loadingOverlay.innerHTML = '<div class="spinner-border spinner-border-sm text-light" role="status"><span class="visually-hidden">Subiendo...</span></div>';
    avatarContainer.appendChild(loadingOverlay);
    
    console.log('Enviando petición a upload_avatar.php');
    
    fetch('upload_avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Respuesta recibida, status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        
        // Remover indicador de carga
        if (loadingOverlay && loadingOverlay.parentNode) {
            loadingOverlay.remove();
        }
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            // Actualizar la imagen con la URL del servidor
            const avatarImg = document.querySelector('.avatar');
            if (avatarImg && data.avatar_url) {
                avatarImg.src = data.avatar_url;
                console.log('Avatar actualizado con URL:', data.avatar_url);
            }
            
            // Limpiar el input file
            const fileInput = document.getElementById('avatarFileInput');
            if (fileInput) {
                fileInput.value = '';
            }
        } else {
            mostrarAlerta(data.message || 'Error al subir la imagen', 'danger');
            console.error('Error en la respuesta:', data);
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        
        // Remover indicador de carga
        if (loadingOverlay && loadingOverlay.parentNode) {
            loadingOverlay.remove();
        }
        
        mostrarAlerta('Error al subir la imagen. Por favor intenta de nuevo. Revisa la consola para más detalles.', 'danger');
    });
}

function mostrarAlerta(mensaje, tipo) {
    console.log('Mostrando alerta:', tipo, mensaje);
    
    // Remover alertas anteriores
    const alertasAnteriores = document.querySelectorAll('.alert.avatar-alert');
    alertasAnteriores.forEach(alerta => alerta.remove());
    
    // Crear elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 avatar-alert`;
    alerta.style.zIndex = '9999';
    alerta.style.maxWidth = '500px';
    alerta.innerHTML = `
        <i class="bi bi-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alerta);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        if (alerta && alerta.parentNode) {
            alerta.classList.remove('show');
            setTimeout(() => {
                if (alerta && alerta.parentNode) {
                    alerta.remove();
                }
            }, 150);
        }
    }, 5000);
}