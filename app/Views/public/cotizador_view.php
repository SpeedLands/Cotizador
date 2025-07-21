<!-- app/Views/public/cotizador_view.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Cotizador para Eventos - Mapolato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .servicio-item { display: flex; align-items: center; padding: 10px; border-radius: 8px; transition: background-color 0.3s; }
        .servicio-item.disabled { background-color: #f8f9fa; opacity: 0.6; cursor: not-allowed; }
        .servicio-item.disabled .form-check-label { color: #6c757d; }
        .campo-oculto { display: none; }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <!-- La URL para AJAX ahora está en el form, para que el JS externo pueda leerla -->
    <form id="cotizadorForm" data-calculate-url="<?= site_url('cotizador/calcular') ?>" data-save-url="<?= site_url('cotizador/guardar') ?>" data-fechas-url="<?= site_url('cotizador/fechas-ocupadas') ?>">
        <div class="row">
            <!-- Columna del Formulario -->
            <div class="col-md-7">
                <h2>Crea tu Cotización</h2>
                <p class="text-muted">Completa los siguientes campos para obtener una propuesta detallada.</p>
                <hr>

                <!-- === INCLUYENDO LOS PARCIALES === -->
                <?= $this->include('public/partials/_form_cliente_evento') ?>
                <?= $this->include('public/partials/_form_servicios') ?>
                <?= $this->include('public/partials/_form_detalles_finales') ?>

            </div>

            <!-- Columna del Resumen -->
            <div class="col-md-5">
                <div class="position-sticky" style="top: 2rem;">
                    <h4>Resumen de Cotización</h4>
                    <div id="resumenCotizacion" class="p-3 bg-light border rounded">
                        <p class="text-muted">Ajusta las opciones para ver el costo.</p>
                    </div>
                    <button type="submit" id="btnEnviarCotizacion" class="btn btn-primary w-100 mt-3">
                        Solicitar Cotización Formal
                    </button>
                    <div id="form-status" class="mt-2"></div> <!-- Para mostrar mensajes -->
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>s
<script src="<?= base_url('js/cotizador.js') ?>"></script> 
</body>
</html>