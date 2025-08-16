// public/js/cotizador.js

document.addEventListener('DOMContentLoaded', function () {

    // --- 1. LÓGICA DE FORMULARIO CONDICIONAL ---
    // Función genérica para mostrar/ocultar campos basados en un disparador
    const setupConditionalField = (triggerSelector, targetSelector, showCondition) => {
        const triggerElements = document.querySelectorAll(triggerSelector);
        const targetElement = document.querySelector(targetSelector);
        if (triggerElements.length === 0 || !targetElement) return;

        const updateVisibility = () => {
            let shouldShow = false;
            triggerElements.forEach(el => {
                if (el.checked && el.value === showCondition) {
                    shouldShow = true;
                }
            });
            targetElement.style.display = shouldShow ? 'block' : 'none';
        };

        triggerElements.forEach(el => el.addEventListener('change', updateVisibility));
        updateVisibility(); // Ejecutar al cargar la página
    };

    // Ocultar/mostrar campo "Nombre de Empresa"
    setupConditionalField('input[name="tipo_evento"]', '#campo_nombre_empresa', 'Empresarial');

    // Ocultar/mostrar campo "Otro" para "Cómo supiste"
    const comoSupisteSelect = document.getElementById('como_supiste');
    const comoSupisteOtro = document.getElementById('como_supiste_otro');
    if (comoSupisteSelect && comoSupisteOtro) {
        const toggleComoSupiste = () => {
            comoSupisteOtro.style.display = comoSupisteSelect.value === 'Otro' ? 'block' : 'none';
        };
        comoSupisteSelect.addEventListener('change', toggleComoSupiste);
        toggleComoSupiste();
    }

    // Ocultar/mostrar campo "Otro" para "Mesa y Mantel"
    setupConditionalField('input[name="mesa_mantel"]', '#campo_mesa_mantel_otro', 'Otro');

    // Ocultar/mostrar campo de enchufe para Estación de Café
    const checkboxCafe = document.querySelector('.servicio-checkbox[data-nombre-clave="estación_de_cafe"]');
    if (checkboxCafe) {
        const campoEnchufe = document.querySelector('#campo_enchufe_cafe');
        if (campoEnchufe) {
            const toggleEnchufe = () => {
                campoEnchufe.style.display = checkboxCafe.checked ? 'block' : 'none';
            };
            checkboxCafe.addEventListener('change', toggleEnchufe);
            toggleEnchufe();
        }
    }


    // --- 2. LÓGICA DEL COTIZADOR EN TIEMPO REAL ---
    const form = document.getElementById('cotizadorForm');
    if (!form) return;

    const cantidadInvitadosInput = document.getElementById('cantidad_invitados');
    const checkboxes = document.querySelectorAll('.servicio-checkbox');
    const radiosModalidad = document.querySelectorAll('.modalidad-radio');
    const resumenContainer = document.getElementById('resumenCotizacion');

    function recalcularTotal() {
        if (!cantidadInvitadosInput) return;

        const cantidadInvitados = parseInt(cantidadInvitadosInput.value) || 0;
        const litrosAgua = Math.ceil(cantidadInvitados / 6);
        let subtotal = 0;
        let itemsHtml = '';

        // Lógica para CHECKBOXES (Servicios normales)
        checkboxes.forEach(cb => {
            const itemDiv = cb.closest('.servicio-item');
            const minPersonas = parseInt(cb.dataset.minPersonas);

            if (cantidadInvitados < minPersonas) {
                cb.disabled = true;
                cb.checked = false;
                itemDiv.classList.add('disabled');
            } else {
                cb.disabled = false;
                itemDiv.classList.remove('disabled');
            }

            if (cb.checked) {
                const tipoCobro = cb.dataset.tipoCobro;
                const precioBase = parseFloat(cb.dataset.precioBase);
                const nombre = cb.nextElementSibling.querySelector('strong').textContent;
                let costoItem = 0;

                switch (tipoCobro) {
                    case 'por_persona': costoItem = precioBase * cantidadInvitados; break;
                    case 'por_litro': costoItem = precioBase * litrosAgua; break;
                    default: costoItem = precioBase; break;
                }

                subtotal += costoItem;
                let detalleItem = (tipoCobro === 'por_litro') ? ` (${litrosAgua} L)` : '';
                itemsHtml += `<li class="d-flex justify-content-between"><span>${nombre}${detalleItem}</span> <span>$${costoItem.toFixed(2)}</span></li>`;
            }
        });

        // Lógica para RADIO BUTTONS (Modalidades)
        const modalidadSeleccionada = document.querySelector('.modalidad-radio:checked');
        if (modalidadSeleccionada) {
            const precioBase = parseFloat(modalidadSeleccionada.dataset.precioBase);
            const nombre = modalidadSeleccionada.nextElementSibling.querySelector('strong').textContent;

            subtotal += precioBase;
            // Solo añadir al resumen si tiene costo o si es el único item
            if (precioBase > 0 || itemsHtml === '') {
                itemsHtml += `<li class="d-flex justify-content-between"><span>Modalidad: ${nombre}</span> <span>$${precioBase.toFixed(2)}</span></li>`;
            }
        }

        // Actualizar el resumen en la UI
        if (itemsHtml === '') {
            resumenContainer.innerHTML = '<p class="text-muted">Ajusta las opciones para ver el costo.</p>';
        } else {
            resumenContainer.innerHTML = `
                <ul class="list-unstyled mb-0">${itemsHtml}</ul>
                <hr>
                <p class="h5 text-end"><strong>Total Estimado: $${subtotal.toFixed(2)}</strong></p>
            `;
        }
    }

    // Asignar los "escuchadores" de eventos
    cantidadInvitadosInput.addEventListener('input', recalcularTotal);
    checkboxes.forEach(cb => cb.addEventListener('change', recalcularTotal));
    radiosModalidad.forEach(radio => radio.addEventListener('change', recalcularTotal));

    // Ejecutar el cálculo una vez al cargar la página
    recalcularTotal();

    const fechaInput = document.getElementById('fecha_evento');
    const fechasUrl = form.dataset.fechasUrl; // Leemos la URL desde el form

    if (fechaInput && fechasUrl) {
        // 1. Hacemos una petición para obtener las fechas ocupadas
        fetch(fechasUrl)
            .then(response => response.json())
            .then(fechasOcupadas => {
                // 2. Una vez que tenemos las fechas, inicializamos Flatpickr
                flatpickr(fechaInput, {
                    locale: "es", // Usar el idioma español
                    dateFormat: "Y-m-d", // Formato que se envía al servidor
                    altInput: true, // Muestra un formato amigable al usuario
                    altFormat: "F j, Y", // ej: "Agosto 16, 2025"

                    // --- ¡AQUÍ ESTÁ LA MAGIA! ---
                    minDate: "today", // No permite seleccionar fechas pasadas
                    disable: fechasOcupadas, // Deshabilita las fechas que vienen del servidor
                });
            })
            .catch(error => {
                // Si falla la carga de fechas, al menos bloqueamos las pasadas
                console.error("Error al cargar las fechas ocupadas:", error);
                flatpickr(fechaInput, {
                    locale: "es",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "F j, Y",
                    minDate: "today",
                });
            });
    }


    // --- 3. MANEJO DEL ENVÍO DEL FORMULARIO (AJAX) ---
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const btn = document.getElementById('btnEnviarCotizacion');
        const statusDiv = document.getElementById('form-status');
        const saveUrl = form.dataset.saveUrl;

        btn.disabled = true;
        btn.textContent = 'Enviando...';
        statusDiv.innerHTML = '';

        const formData = new FormData(form);

        fetch(saveUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) {
                    // Si el servidor responde con un error (ej. 500), capturarlo
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.querySelector('#cotizadorForm .col-md-7').style.display = 'none';
                    btn.style.display = 'none';
                    statusDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    window.scrollTo({ top: statusDiv.offsetTop - 100, behavior: 'smooth' });
                } else {
                    statusDiv.innerHTML = `<div class="alert alert-danger">${data.message || 'Ocurrió un error al procesar la solicitud.'}</div>`;
                    btn.disabled = false;
                    btn.textContent = 'Reintentar Envío';
                }
            })
            .catch(error => {
                console.error('Error en el envío del formulario:', error);
                statusDiv.innerHTML = '<div class="alert alert-danger">Error de conexión. Por favor, revisa tu internet y vuelve a intentarlo.</div>';
                btn.disabled = false;
                btn.textContent = 'Reintentar Envío';
            });
    });
});