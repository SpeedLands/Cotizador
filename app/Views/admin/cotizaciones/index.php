<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Cotizaciones - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">📋 Panel de Cotizaciones</h1>

    <div class="row mb-3">
        <div class="col-md-3">
            <label>Fecha desde:</label>
            <input type="date" id="fechaDesde" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Fecha hasta:</label>
            <input type="date" id="fechaHasta" class="form-control">
        </div>
        <div class="col-md-3">
        <label>Estado:</label>
        <select id="estadoFiltro" class="form-select">
            <option value="">Todos</option>
            <option value="abierto">Abierto</option>
            <option value="confirmado">Confirmado</option>
            <option value="cancelado">Cancelado</option>
            <option value="pagado">Pagado</option>
        </select>
    </div>
    </div>

    <?php if (empty($cotizaciones)) : ?>
        <div class="alert alert-info">No hay cotizaciones registradas aún.</div>
    <?php else : ?>
        <table id="tablaCotizaciones" class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha Evento</th>
                    <th>Invitados</th>
                    <th>Presupuesto</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cotizaciones as $c) : ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= esc($c['nombre_cliente']) ?></td>
                        <td><?= esc($c['fecha_evento']) ?></td>
                        <td><?= esc($c['numero_invitados']) ?></td>
                        <td>$<?= number_format($c['presupuesto_total'], 2) ?></td>
                        <?php
                            $estado = $c['estado'];
                            $badge = match ($estado) {
                                'confirmado' => 'bg-success',
                                'cancelado' => 'bg-danger',
                                'pagado'     => 'bg-primary',
                                'abierto'    => 'bg-warning',
                                default      => 'bg-secondary'
                            };
                        ?>
                        <td><span class="badge <?= $badge ?>"><?= esc($estado) ?></span></td>
                        <td><?= esc($c['fecha_creacion']) ?></td>
                        <td>
                            <a href="<?= base_url('admin/cotizaciones/' . $c['id']) ?>" class="btn btn-sm btn-primary">Ver</a>
                            <button class="btn btn-sm btn-warning btnEstado" data-id="<?= $c['id'] ?>" data-estado="<?= $c['estado'] ?>">Cambiar Estado</button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jQuery y DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Export Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {
    var tabla =  $('#tablaCotizaciones').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"] ]
    });


    // Filtro por rango de fechas
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var min = $('#fechaDesde').val();
            var max = $('#fechaHasta').val();
            var fechaEvento = data[2]; // Columna "Fecha Evento"

            if (!min && !max) return true;

            if (min && fechaEvento < min) return false;
            if (max && fechaEvento > max) return false;

            return true;
        }
    );

    $('#fechaDesde, #fechaHasta').on('change', function () {
        tabla.draw();
    });

    // Filtro por estado
    $('#estadoFiltro').on('change', function () {
        tabla.column(5).search(this.value).draw(); // Columna 5 = Estado
    });

    // Cambiar estado con SweetAlert
    $('.btnEstado').on('click', function () {
        var id = $(this).data('id');
        var actual = $(this).data('estado');

        Swal.fire({
            title: 'Cambiar Estado',
            text: 'Estado actual: ' + actual,
            input: 'select',
            inputOptions: {
                'abierto': 'Abierto',
                'confirmado': 'Confirmado',
                'cancelado': 'Cancelado',
                'pagado': 'Pagado'
            },
            inputPlaceholder: 'Selecciona nuevo estado',
            showCancelButton: true,
            confirmButtonText: 'Guardar'
        }).then((result) => {
            if (result.isConfirmed) {
                const nuevoEstado = result.value;

                // Petición AJAX para actualizar estado
                $.post('<?= base_url('admin/cotizaciones/cambiar-estado') ?>', {
                    id: id,
                    estado: nuevoEstado
                }, function (respuesta) {
                    Swal.fire('¡Listo!', respuesta.mensaje, 'success').then(() => location.reload()).fail(function (xhr) {
    Swal.fire('Error', 'No se pudo actualizar el estado. ' + xhr.responseText, 'error');
});
                });
            }
        });
    });
});
</script>
</body>
</html>
