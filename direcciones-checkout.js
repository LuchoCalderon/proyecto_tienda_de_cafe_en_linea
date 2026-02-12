/**
 * Gestión de direcciones en el checkout
 */

class DireccionesCheckout {
    constructor() {
        this.direcciones = [];
        this.direccionSeleccionada = null;
        this.usandoDireccionGuardada = false;
        this.basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    }

    async init() {
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
                // Si no hay direcciones o hay un error, mostrar formulario manual
                this.mostrarFormularioManual();
            }
        } catch (error) {
            console.error('Error al cargar direcciones:', error);
            // En caso de error, mostrar formulario manual
            this.mostrarFormularioManual();
        }
    }

    renderizarDirecciones() {
        const container = document.getElementById('direccionesGuardadas');
        
        if (!container) {
            console.warn('Contenedor de direcciones no encontrado');
            return;
        }

        if (this.direcciones.length === 0) {
            this.mostrarFormularioManual();
            return;
        }

        // Encontrar la dirección predeterminada
        const predeterminada = this.direcciones.find(d => d.esPredeterminada == '1');
        const primeraSeleccionada = predeterminada || this.direcciones[0];

        container.innerHTML = `
            <h6 class="mb-3">Direcciones guardadas</h6>
            ${this.direcciones.map((dir, index) => this.crearRadioDireccion(dir, dir.id === primeraSeleccionada.id)).join('')}
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.direccionesCheckout.mostrarFormularioNuevaDireccion()">
                    <i class="bi bi-plus-circle"></i> Usar otra dirección
                </button>
            </div>
        `;

        // Seleccionar la dirección predeterminada automáticamente
        this.direccionSeleccionada = primeraSeleccionada.id;
        this.usandoDireccionGuardada = true;
    }

    crearRadioDireccion(dir, checked = false) {
        const esPredeterminada = dir.esPredeterminada == '1';
        const alias = dir.alias ? `<strong>${this.escape(dir.alias)}</strong><br>` : '';
        
        return `
            <div class="direccion-radio mb-3" onclick="window.direccionesCheckout.seleccionarDireccion(${dir.id})">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="direccionGuardada" 
                        id="dir${dir.id}" 
                        value="${dir.id}"
                        ${checked ? 'checked' : ''}
                        onchange="window.direccionesCheckout.seleccionarDireccion(${dir.id})"
                    >
                    <label class="form-check-label direccion-info w-100" for="dir${dir.id}">
                        ${esPredeterminada ? '<span class="badge bg-secondary mb-1">Predeterminada</span><br>' : ''}
                        ${alias}
                        ${this.escape(dir.calle)}${dir.apartamento ? ', ' + this.escape(dir.apartamento) : ''}<br>
                        <small class="text-muted">
                            ${this.escape(dir.ciudad)}, ${this.escape(dir.departamento)} - CP: ${this.escape(dir.codigoPostal)}
                        </small>
                        ${dir.instrucciones ? `<br><small class="text-muted"><i class="bi bi-info-circle"></i> ${this.escape(dir.instrucciones)}</small>` : ''}
                    </label>
                </div>
            </div>
        `;
    }

    seleccionarDireccion(id) {
        this.direccionSeleccionada = id;
        this.usandoDireccionGuardada = true;
        
        // Asegurar que el radio button esté marcado
        const radio = document.getElementById(`dir${id}`);
        if (radio) {
            radio.checked = true;
        }
    }

    mostrarFormularioManual() {
        const container = document.getElementById('direccionesGuardadas');
        if (container) {
            container.innerHTML = '<p class="text-muted">No tienes direcciones guardadas.</p>';
        }
        
        const formulario = document.getElementById('formularioDireccionManual');
        if (formulario) {
            formulario.style.display = 'block';
        }
        
        this.usandoDireccionGuardada = false;
    }

    mostrarFormularioNuevaDireccion() {
        const formulario = document.getElementById('formularioDireccionManual');
        if (formulario) {
            formulario.style.display = 'block';
        }
        
        // Desmarcar direcciones guardadas
        const radios = document.querySelectorAll('input[name="direccionGuardada"]');
        radios.forEach(r => r.checked = false);
        
        this.usandoDireccionGuardada = false;
        this.direccionSeleccionada = null;
    }

    validar() {
        if (this.usandoDireccionGuardada && this.direccionSeleccionada) {
            return true;
        }
        
        // Validar formulario manual
        const formulario = document.getElementById('formularioDireccionManual');
        if (formulario && formulario.style.display !== 'none') {
            const campos = ['firstName', 'lastName', 'address', 'city', 'state', 'zip', 'phone'];
            for (const campo of campos) {
                const elemento = document.getElementById(campo);
                if (elemento && !elemento.value.trim()) {
                    elemento.focus();
                    return false;
                }
            }
            return true;
        }
        
        return false;
    }

    async getDatosParaEnvio() {
        if (this.usandoDireccionGuardada && this.direccionSeleccionada) {
            // Obtener datos de la dirección seleccionada
            const direccion = this.direcciones.find(d => d.id === this.direccionSeleccionada);
            if (direccion) {
                return {
                    tipo: 'guardada',
                    direccionId: direccion.id,
                    calle: direccion.calle,
                    apartamento: direccion.apartamento,
                    ciudad: direccion.ciudad,
                    departamento: direccion.departamento,
                    codigoPostal: direccion.codigoPostal,
                    instrucciones: direccion.instrucciones,
                    alias: direccion.alias
                };
            }
        }
        
        // Obtener datos del formulario manual
        return {
            tipo: 'manual',
            nombres: document.getElementById('firstName')?.value || '',
            apellidos: document.getElementById('lastName')?.value || '',
            calle: document.getElementById('address')?.value || '',
            apartamento: document.getElementById('addressDetails')?.value || '',
            ciudad: document.getElementById('city')?.value || '',
            departamento: document.getElementById('state')?.value || '',
            codigoPostal: document.getElementById('zip')?.value || '',
            telefono: document.getElementById('phone')?.value || '',
            guardar: document.getElementById('saveAddress')?.checked || false
        };
    }

    escape(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text ? String(text).replace(/[&<>"']/g, m => map[m]) : '';
    }
}

// Crear instancia global e inicializar
const direccionesCheckout = new DireccionesCheckout();

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => direccionesCheckout.init());
} else {
    direccionesCheckout.init();
}
