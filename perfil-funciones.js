// Script para manejar las funcionalidades del perfil de usuario

document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de perfil cargado');

    // ===========================================
    // ACTUALIZAR INFORMACIÓN PERSONAL
    // ===========================================
    const formInfoPersonal = document.getElementById('formInfoPersonal');
    
    if (formInfoPersonal) {
        formInfoPersonal.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Actualizando información personal');

            const nombre = document.getElementById('firstName').value.trim();
            const email = document.getElementById('email').value.trim();
            const telefono = document.getElementById('phone').value.trim();

            // Validaciones básicas
            if (!nombre || !email || !telefono) {
                mostrarAlerta('Todos los campos son obligatorios', 'warning');
                return;
            }

            // Mostrar spinner en el botón
            const btnSubmit = formInfoPersonal.querySelector('button[type="submit"]');
            const btnTextoOriginal = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            // Enviar datos
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('email', email);
            formData.append('telefono', telefono);

            fetch('actualizar_info.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnTextoOriginal;

                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    
                    // Actualizar el nombre en el encabezado si existe
                    const nombreHeader = document.querySelector('.profile-content h5, .mt-3.mb-0');
                    if (nombreHeader) {
                        nombreHeader.textContent = nombre;
                    }
                } else {
                    mostrarAlerta(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnTextoOriginal;
                mostrarAlerta('Error al actualizar la información. Por favor intenta de nuevo.', 'danger');
            });
        });
    }

    // ===========================================
    // CAMBIAR CONTRASEÑA
    // ===========================================
    const formCambiarPassword = document.getElementById('formCambiarPassword');
    
    if (formCambiarPassword) {
        formCambiarPassword.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Cambiando contraseña');

            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Validaciones
            if (!currentPassword || !newPassword || !confirmPassword) {
                mostrarAlerta('Todos los campos son obligatorios', 'warning');
                return;
            }

            if (newPassword !== confirmPassword) {
                mostrarAlerta('Las contraseñas nuevas no coinciden', 'warning');
                return;
            }

            if (newPassword.length < 8) {
                mostrarAlerta('La contraseña debe tener al menos 8 caracteres', 'warning');
                return;
            }

            // Validar complejidad
            const tieneMinuscula = /[a-z]/.test(newPassword);
            const tieneMayuscula = /[A-Z]/.test(newPassword);
            const tieneNumero = /[0-9]/.test(newPassword);
            const tieneEspecial = /[^A-Za-z0-9]/.test(newPassword);

            if (!tieneMinuscula || !tieneMayuscula || !tieneNumero || !tieneEspecial) {
                mostrarAlerta('La contraseña debe incluir mayúsculas, minúsculas, números y caracteres especiales', 'warning');
                return;
            }

            // Mostrar spinner en el botón
            const btnSubmit = formCambiarPassword.querySelector('button[type="submit"]');
            const btnTextoOriginal = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Actualizando...';

            // Enviar datos
            const formData = new FormData();
            formData.append('currentPassword', currentPassword);
            formData.append('newPassword', newPassword);
            formData.append('confirmPassword', confirmPassword);

            fetch('cambiar_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnTextoOriginal;

                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    // Limpiar formulario
                    formCambiarPassword.reset();
                } else {
                    mostrarAlerta(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnTextoOriginal;
                mostrarAlerta('Error al cambiar la contraseña. Por favor intenta de nuevo.', 'danger');
            });
        });

        // Mostrar/ocultar contraseñas
        agregarTogglePassword('currentPassword');
        agregarTogglePassword('newPassword');
        agregarTogglePassword('confirmPassword');
    }

    // ===========================================
    // ELIMINAR CUENTA
    // ===========================================
    const btnEliminarCuenta = document.getElementById('btnEliminarCuenta');
    
    if (btnEliminarCuenta) {
        btnEliminarCuenta.addEventListener('click', function() {
            console.log('Solicitando eliminar cuenta');
            mostrarModalEliminarCuenta();
        });
    }
});

// ===========================================
// FUNCIONES AUXILIARES
// ===========================================

function agregarTogglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const wrapper = input.parentElement;
    wrapper.style.position = 'relative';

    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'btn btn-sm position-absolute';
    toggleBtn.style.cssText = 'right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; padding: 0;';
    toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';

    toggleBtn.addEventListener('click', function() {
        if (input.type === 'password') {
            input.type = 'text';
            toggleBtn.innerHTML = '<i class="bi bi-eye-slash"></i>';
        } else {
            input.type = 'password';
            toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
        }
    });

    wrapper.appendChild(toggleBtn);
}

function mostrarModalEliminarCuenta() {
    // Crear modal de confirmación
    const modalHTML = `
        <div class="modal fade" id="modalEliminarCuenta" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Eliminar Cuenta
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>¡Advertencia!</strong> Esta acción es irreversible.
                        </div>
                        
                        <p>Al eliminar tu cuenta:</p>
                        <ul>
                            <li>Se eliminarán todos tus datos personales</li>
                            <li>Perderás el historial de pedidos</li>
                            <li>Se borrarán tus puntos de lealtad</li>
                            <li>No podrás recuperar esta cuenta</li>
                        </ul>

                        <form id="formEliminarCuenta">
                            <div class="mb-3">
                                <label for="passwordEliminar" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="passwordEliminar" required 
                                       placeholder="Ingresa tu contraseña para confirmar">
                            </div>

                            <div class="mb-3">
                                <label for="confirmacionEliminar" class="form-label">
                                    Escribe "ELIMINAR" para confirmar
                                </label>
                                <input type="text" class="form-control" id="confirmacionEliminar" required 
                                       placeholder="ELIMINAR">
                                <div class="form-text">
                                    Debes escribir exactamente "ELIMINAR" en mayúsculas
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmarEliminar">
                            Eliminar mi cuenta permanentemente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Agregar modal al DOM
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalEliminarCuenta'));
    modal.show();

    // Manejar confirmación
    document.getElementById('confirmarEliminar').addEventListener('click', function() {
        const password = document.getElementById('passwordEliminar').value;
        const confirmacion = document.getElementById('confirmacionEliminar').value;

        if (!password) {
            mostrarAlerta('Debes ingresar tu contraseña', 'warning');
            return;
        }

        if (confirmacion !== 'ELIMINAR') {
            mostrarAlerta('Debes escribir "ELIMINAR" para confirmar', 'warning');
            return;
        }

        // Mostrar spinner
        const btnEliminar = this;
        const btnTextoOriginal = btnEliminar.innerHTML;
        btnEliminar.disabled = true;
        btnEliminar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';

        // Enviar solicitud
        const formData = new FormData();
        formData.append('password', password);
        formData.append('confirmacion', confirmacion);

        fetch('eliminar_cuenta.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                mostrarAlerta(data.message, 'success');
                
                // Redirigir después de 2 segundos
                setTimeout(() => {
                    window.location.href = data.redirect || 'index.php';
                }, 2000);
            } else {
                btnEliminar.disabled = false;
                btnEliminar.innerHTML = btnTextoOriginal;
                mostrarAlerta(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = btnTextoOriginal;
            mostrarAlerta('Error al eliminar la cuenta. Por favor intenta de nuevo.', 'danger');
        });
    });

    // Limpiar modal al cerrar
    document.getElementById('modalEliminarCuenta').addEventListener('hidden.bs.modal', function() {
        modalContainer.remove();
    });
}

function mostrarAlerta(mensaje, tipo) {
    console.log('Mostrando alerta:', tipo, mensaje);
    
    // Remover alertas anteriores
    const alertasAnteriores = document.querySelectorAll('.alert.perfil-alert');
    alertasAnteriores.forEach(alerta => alerta.remove());
    
    // Crear elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 perfil-alert`;
    alerta.style.zIndex = '9999';
    alerta.style.maxWidth = '500px';
    
    const iconos = {
        'success': 'check-circle',
        'danger': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    
    const icono = iconos[tipo] || 'info-circle';
    
    alerta.innerHTML = `
        <i class="bi bi-${icono}-fill me-2"></i>
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