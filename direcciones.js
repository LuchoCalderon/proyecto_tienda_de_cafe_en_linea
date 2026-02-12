/**
 * Gestión simple de direcciones
 */

class GestorDirecciones {
    constructor() {
        this.direcciones = [];
        this.direccionAEliminar = null;
        this.modoEdicion = false;
        this.basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
        this.modalDireccion = null;
        this.modalEliminar = null;
    }

    async init() {
        this.modalDireccion = new bootstrap.Modal(document.getElementById('modalDireccion'));
        this.modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));
        
        document.getElementById('formDireccion').addEventListener('submit', (e) => this.handleSubmit(e));
        document.getElementById('modalDireccion').addEventListener('hidden.bs.modal', () => this.resetFormulario());
        
        await this.cargarDirecciones();
    }

    async cargarDirecciones() {
        try {
            const response = await fetch(this.basePath + 'php/direcciones_api.php?action=listar');
            const data = await response.json();
            
            if (data.success) {
                this.direcciones = data.data;
                this.renderizarDirecciones();
            } else {
                this.mostrarAlerta('Error al cargar direcciones: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarAlerta('Error al cargar las direcciones', 'danger');
        }
    }

    renderizarDirecciones() {
        const container = document.getElementById('direccionesContainer');
        
        if (this.direcciones.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-geo-alt display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No tienes direcciones guardadas</h4>
                    <p class="text-muted">Agrega tu primera dirección</p>
                    <button class="btn btn-primary mt-3" onclick="gestorDirecciones.mostrarModalAgregar()">
                        <i class="bi bi-plus-circle"></i> Agregar Dirección
                    </button>
                </div>
            `;
            return;
        }

        container.innerHTML = this.direcciones.map(dir => this.crearTarjeta(dir)).join('');
    }

    crearTarjeta(dir) {
        const esPredeterminada = dir.esPredeterminada == '1';
        const alias = dir.alias ? `<h5 class="card-title mb-2"><i class="bi bi-tag"></i> ${this.escape(dir.alias)}</h5>` : '';
        
        return `
            <div class="col-md-6 mb-4">
                <div class="card address-card h-100 ${esPredeterminada ? 'default' : ''}">
                    <div class="card-body">
                        ${esPredeterminada ? '<span class="badge badge-default mb-2"><i class="bi bi-star-fill"></i> Predeterminada</span>' : ''}
                        ${alias}
                        <p class="card-text mb-2">
                            <i class="bi bi-geo-alt-fill"></i> ${this.escape(dir.calle)}
                            ${dir.apartamento ? ', ' + this.escape(dir.apartamento) : ''}
                        </p>
                        <p class="card-text text-muted mb-1">
                            ${this.escape(dir.ciudad)}, ${this.escape(dir.departamento)}
                        </p>
                        <p class="card-text text-muted mb-1">
                            CP: ${this.escape(dir.codigoPostal)}
                        </p>
                        ${dir.instrucciones ? `<p class="card-text text-muted small mt-2"><i class="bi bi-info-circle"></i> ${this.escape(dir.instrucciones)}</p>` : ''}
                    </div>
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                ${!esPredeterminada ? `
                                    <button class="btn btn-sm btn-outline-primary btn-action" onclick="gestorDirecciones.establecerPredeterminada(${dir.id})" title="Marcar como predeterminada">
                                        <i class="bi bi-star"></i>
                                    </button>
                                ` : ''}
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary btn-action me-1" onclick="gestorDirecciones.editarDireccion(${dir.id})" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-action" onclick="gestorDirecciones.mostrarModalEliminar(${dir.id})" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    mostrarModalAgregar() {
        this.modoEdicion = false;
        this.resetFormulario();
        document.getElementById('modalDireccionLabel').textContent = 'Agregar Dirección';
        this.modalDireccion.show();
    }

    async editarDireccion(id) {
        try {
            const response = await fetch(this.basePath + `php/direcciones_api.php?action=obtener&id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                this.modoEdicion = true;
                const dir = data.data;
                
                document.getElementById('direccionId').value = dir.id;
                document.getElementById('alias').value = dir.alias || '';
                document.getElementById('calle').value = dir.calle || '';
                document.getElementById('apartamento').value = dir.apartamento || '';
                document.getElementById('instrucciones').value = dir.instrucciones || '';
                document.getElementById('ciudad').value = dir.ciudad || '';
                document.getElementById('departamento').value = dir.departamento || '';
                document.getElementById('codigoPostal').value = dir.codigoPostal || '';
                document.getElementById('esPredeterminada').checked = dir.esPredeterminada == '1';
                
                document.getElementById('modalDireccionLabel').textContent = 'Editar Dirección';
                this.modalDireccion.show();
            } else {
                this.mostrarAlerta('Error al cargar la dirección', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarAlerta('Error al cargar la dirección', 'danger');
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const action = this.modoEdicion ? 'actualizar' : 'crear';
        formData.append('action', action);
        
        try {
            const response = await fetch(this.basePath + 'php/direcciones_api.php', {
                method: 'POST',
                body: formData
            });
            
            // Primero obtener el texto de la respuesta
            const responseText = await response.text();
            console.log('Respuesta del servidor:', responseText);
            
            // Intentar parsear como JSON
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Error al parsear JSON:', parseError);
                console.error('Respuesta recibida:', responseText.substring(0, 500));
                this.mostrarAlerta('Error del servidor. Revisa la consola para más detalles.', 'danger');
                return;
            }
            
            if (data.success) {
                this.mostrarAlerta(
                    this.modoEdicion ? 'Dirección actualizada' : 'Dirección agregada',
                    'success'
                );
                this.modalDireccion.hide();
                await this.cargarDirecciones();
            } else {
                this.mostrarAlerta('Error: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarAlerta('Error al guardar: ' + error.message, 'danger');
        }
    }

    mostrarModalEliminar(id) {
        this.direccionAEliminar = id;
        this.modalEliminar.show();
    }

    async confirmarEliminar() {
        if (!this.direccionAEliminar) return;
        
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id', this.direccionAEliminar);
        
        try {
            const response = await fetch(this.basePath + 'php/direcciones_api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta('Dirección eliminada', 'success');
                this.modalEliminar.hide();
                this.direccionAEliminar = null;
                await this.cargarDirecciones();
            } else {
                this.mostrarAlerta('Error: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarAlerta('Error al eliminar', 'danger');
        }
    }

    async establecerPredeterminada(id) {
        const formData = new FormData();
        formData.append('action', 'predeterminada');
        formData.append('id', id);
        
        try {
            const response = await fetch(this.basePath + 'php/direcciones_api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta('Dirección predeterminada actualizada', 'success');
                await this.cargarDirecciones();
            } else {
                this.mostrarAlerta('Error: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarAlerta('Error', 'danger');
        }
    }

    resetFormulario() {
        document.getElementById('formDireccion').reset();
        document.getElementById('direccionId').value = '';
        this.modoEdicion = false;
    }

    mostrarAlerta(mensaje, tipo = 'info') {
        const container = document.getElementById('alertContainer');
        const alertId = 'alert-' + Date.now();
        
        container.innerHTML = `
            <div id="${alertId}" class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        setTimeout(() => {
            const el = document.getElementById(alertId);
            if (el) new bootstrap.Alert(el).close();
        }, 5000);
    }

    escape(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text ? String(text).replace(/[&<>"']/g, m => map[m]) : '';
    }
}

// Crear instancia global e inicializar
window.gestorDirecciones = new GestorDirecciones();

// Asegurar que las funciones globales estén disponibles
window.mostrarModalAgregar = function() {
    gestorDirecciones.mostrarModalAgregar();
};

window.confirmarEliminar = function() {
    gestorDirecciones.confirmarEliminar();
};

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => gestorDirecciones.init());
} else {
    gestorDirecciones.init();
}