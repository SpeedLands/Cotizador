// public/js/cotizador.js
$(document).ready(function () {

    // --- LÓGICA DE FORMULARIO CONDICIONAL ---
    $('input[name="tipo_evento"]').on('change', function () {
        if ($(this).val() === 'Empresarial') {
            $('#campo_nombre_empresa').slideDown();
        } else {
            $('#campo_nombre_empresa').slideUp();
        }
    });

    $('#como_supiste').on('change', function () {
        if ($(this).val() === 'Otro') {
            $('#como_supiste_otro').slideDown();
        } else {
            $('#como_supiste_otro').slideUp();
        }
    });

    $('input[name="mesa_mantel"]').on('change', function () {
        if ($(this).val() === 'Otro') {
            $('#campo_mesa_mantel_otro').slideDown();
        } else {
            $('#campo_mesa_mantel_otro').slideUp();
        }
    });

    const $checkboxCafe = $('.servicio-checkbox[data-nombre-clave="estación_de_cafe"]');
    $checkboxCafe.on('change', function () {
        if ($(this).is(':checked')) {
            $('#campo_enchufe_cafe').slideDown();
        } else {
            $('#campo_enchufe_cafe').slideUp();
        }
    });

    // --- LÓGICA DEL COTIZADOR ---
    const calculateUrl = $('#cotizadorForm').data('calculate-url');

    const saveUrl = $('#cotizadorForm').data('save-url');

    function validarOpcionesPorInvitados() {
        const cantidadInvitados = parseInt($('#cantidad_invitados').val()) || 0;
        $('.servicio-checkbox').each(function () {
            const minPersonas = parseInt($(this).data('min-personas')) || 1;
            const $itemContainer = $(this).closest('.servicio-item');
            if (cantidadInvitados < minPersonas) {
                $(this).prop('disabled', true).prop('checked', false);
                $itemContainer.addClass('disabled');
            } else {
                $(this).prop('disabled', false);
                $itemContainer.removeClass('disabled');
            }
        });
        $('.servicio-checkbox:disabled').trigger('change');
    }

    function actualizarResumen() {
        $.ajax({
            type: "POST",
            url: calculateUrl, // Usamos la variable que lee el data-attribute
            data: $('#cotizadorForm').serialize(),
            dataType: "json",
            success: function (response) {
                let resumenHTML = '<p class="text-muted">Ajusta las opciones para ver el costo.</p>';
                if (response.items && response.items.length > 0) {
                    resumenHTML = '<table class="table table-sm"><tbody>';
                    response.items.forEach(item => {
                        resumenHTML += `<tr><td>${item.nombre}</td><td class="text-end">$${item.costo}</td></tr>`;
                    });
                    resumenHTML += `<tr class="fw-bold border-top"><td>Total Estimado</td><td class="text-end">$${response.total}</td></tr>`;
                    resumenHTML += '</tbody></table>';
                }
                $('#resumenCotizacion').html(resumenHTML);
            },
            error: function () {
                $('#resumenCotizacion').html('<p class="text-danger">Error al calcular.</p>');
            }
        });
    }

    $('#cantidad_invitados, input[name="litros_agua"]').on('input change', function () {
        validarOpcionesPorInvitados();
        actualizarResumen();
    });

    $('.servicio-checkbox').on('change', function () {
        if ($(this).data('tipo-cobro') === 'por_litro') {
            const $litrosInput = $('#litros_servicio_' + $(this).val());
            if ($(this).is(':checked')) {
                $litrosInput.removeClass('campo-oculto').val(1);
            } else {
                $litrosInput.addClass('campo-oculto').val('');
            }
        }
        actualizarResumen();
    });

    // --- MANEJO DEL ENVÍO DEL FORMULARIO ---
    $('#cotizadorForm').on('submit', function (e) {
        e.preventDefault(); // Prevenir el envío normal del formulario

        const $btn = $('#btnEnviarCotizacion');
        const $statusDiv = $('#form-status');

        $btn.prop('disabled', true).text('Enviando...');
        $statusDiv.html(''); // Limpiar mensajes previos

        $.ajax({
            type: "POST",
            url: saveUrl, // Asegúrate de que esto se renderice bien
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $('#cotizadorForm .col-md-7').slideUp(); // Ocultar el formulario
                    $btn.hide();
                    $statusDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                    $('html, body').animate({
                        scrollTop: $statusDiv.offset().top - 100 // -100 para dar un poco de espacio
                    }, 500);
                } else {
                    $statusDiv.html('<div class="alert alert-danger">' + (response.error || 'Ocurrió un error.') + '</div>');
                    $btn.prop('disabled', false).text('Reintentar Envío');
                }
            },
            error: function () {
                $statusDiv.html('<div class="alert alert-danger">Error de conexión. Por favor, revisa tu internet y vuelve a intentarlo.</div>');
                $btn.prop('disabled', false).text('Reintentar Envío');
            }
        });
    });

    // --- Carga Inicial ---
    validarOpcionesPorInvitados();
    actualizarResumen();
});