<!DOCTYPE html>
<html lang="es">
<head>
    <title>Cotizador Simplificado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilo para los ítems y para cuando están deshabilitados */
        .servicio-item { display: flex; align-items: center; padding: 10px; border-radius: 8px; transition: background-color 0.3s; }
        .servicio-item.disabled { background-color: #f8f9fa; opacity: 0.6; }
        .servicio-item.disabled .form-check-label { color: #6c757d; }
    </style>
</head>
<body>
<div class="container mt-5">
    <form id="cotizadorForm">
        <div class="row">
            <!-- Columna del Formulario -->
            <div class="col-md-7">
                <h2>Crea tu Cotización</h2>
                <div class="mb-4">
                    <label for="cantidad_invitados" class="form-label fw-bold fs-5">1. Cantidad de Invitados</label>
                    <input type="number" name="cantidad_invitados" id="cantidad_invitados" class="form-control" value="1" min="1">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold fs-5">2. ¿Qué Gustas en tu Cotización?</label>
                    <?php foreach ($servicios as $servicio): ?>
                        <div class="servicio-item" id="item-<?= $servicio['id'] ?>">
                            <input class="form-check-input me-3 servicio-checkbox" 
                                   type="checkbox" 
                                   name="servicios[]" 
                                   value="<?= $servicio['id'] ?>" 
                                   id="servicio_<?= $servicio['id'] ?>"
                                   data-min-personas="<?= $servicio['min_personas'] ?>"
                                   data-tipo-cobro="<?= $servicio['tipo_cobro'] ?>">
                            
                            <label class="form-check-label flex-grow-1" for="servicio_<?= $servicio['id'] ?>">
                                <strong><?= htmlspecialchars($servicio['nombre']) ?></strong>
                                <?php if ($servicio['min_personas'] > 1): ?>
                                    <small class="d-block text-muted">Requiere mínimo <?= $servicio['min_personas'] ?> invitados.</small>
                                <?php endif; ?>
                            </label>

                            <!-- Campo especial para litros de agua, inicialmente oculto -->
                            <?php if ($servicio['tipo_cobro'] == 'por_litro'): ?>
                                <input type="number" name="litros_agua" class="form-control form-control-sm ms-2" 
                                       style="display:none; width: 100px;" 
                                       placeholder="Litros" min="1"
                                       id="litros_servicio_<?= $servicio['id'] ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Columna del Resumen -->
            <div class="col-md-5">
                <div class="position-sticky" style="top: 2rem;">
                    <h4>Resumen de Cotización</h4>
                    <div id="resumenCotizacion" class="p-3 bg-light border rounded">
                        <p class="text-muted">Ajusta las opciones para ver el costo.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    function validarOpcionesPorInvitados() {
        const cantidadInvitados = parseInt($('#cantidad_invitados').val()) || 0;

        $('.servicio-checkbox').each(function() {
            const minPersonas = parseInt($(this).data('min-personas'));
            const $itemContainer = $(this).closest('.servicio-item');

            if (cantidadInvitados < minPersonas) {
                $(this).prop('disabled', true);
                $(this).prop('checked', false); // Desmarca si estaba marcado
                $itemContainer.addClass('disabled');
            } else {
                $(this).prop('disabled', false);
                $itemContainer.removeClass('disabled');
            }
        });
        
        // Disparar un 'change' para que los campos de litros se oculten si es necesario
        $('.servicio-checkbox:disabled').trigger('change');
    }

    function actualizarResumen() {
        $.ajax({
            type: "POST",
            url: "<?= site_url('cotizador/calcular') ?>",
            data: $('#cotizadorForm').serialize(),
            dataType: "json",
            success: function(response) {
                let resumenHTML = '<p class="text-muted">Ajusta las opciones para ver el costo.</p>';
                if (response.items && response.items.length > 0) {
                    resumenHTML = '<table class="table table-sm"><tbody>';
                    response.items.forEach(item => {
                        resumenHTML += `<tr><td>${item.nombre}</td><td class="text-end">$${item.costo}</td></tr>`;
                    });
                    resumenHTML += `<tr class="fw-bold border-top"><td >Total Estimado</td><td class="text-end">$${response.total}</td></tr>`;
                    resumenHTML += '</tbody></table>';
                }
                $('#resumenCotizacion').html(resumenHTML);
            },
            error: function() {
                $('#resumenCotizacion').html('<p class="text-danger">Error al calcular.</p>');
            }
        });
    }

    // EVENTO 1: Cuando cambia la cantidad de invitados
    $('#cantidad_invitados').on('keyup change', function() {
        validarOpcionesPorInvitados();
        actualizarResumen();
    });

    // EVENTO 2: Cuando se marca o desmarca un servicio
    $('.servicio-checkbox').on('change', function() {
        // Lógica para mostrar/ocultar el input de litros
        if ($(this).data('tipo-cobro') === 'por_litro') {
            const $litrosInput = $('#litros_servicio_' + $(this).val());
            if ($(this).is(':checked')) {
                $litrosInput.show().val(1); // Muestra y pone 1 por defecto
            } else {
                $litrosInput.hide().val(''); // Oculta y limpia el valor
            }
        }
        actualizarResumen();
    });
    
    // EVENTO 3: Cuando se cambian los litros
    $('input[name="litros_agua"]').on('keyup change', function() {
        actualizarResumen();
    });

    // --- Carga Inicial ---
    validarOpcionesPorInvitados();
    actualizarResumen();
});
</script>
</body>
</html>