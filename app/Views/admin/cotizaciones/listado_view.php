<?= $this->extend('admin/layout') ?>

<?= $this->section('styles') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.0/css/buttons.bootstrap5.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= esc($titulo) ?></h1>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if (!empty($cotizaciones)): ?>
                <div class="table-responsive">
                    <table id="tablaCotizaciones" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha Evento</th>
                                <th>Invitados</th>
                                <th>Total Est.</th>
                                <th>Estado</th>
                                <th>Solicitud</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cotizaciones as $cotizacion): ?>
                                <tr>
                                    <td><?= esc($cotizacion['id']) ?></td>
                                    <td>
                                        <strong><?= esc($cotizacion['nombre_completo']) ?></strong>
                                        <small class="d-block text-muted"><?= esc($cotizacion['whatsapp']) ?></small>
                                    </td>
                                    <td data-order="<?= strtotime($cotizacion['fecha_evento']) ?>">
                                        <?= date('d/m/Y', strtotime($cotizacion['fecha_evento'])) ?>
                                    </td>
                                    <td class="text-center"><?= esc($cotizacion['cantidad_invitados']) ?></td>
                                    <td data-order="<?= $cotizacion['total_estimado'] ?>">
                                        $<?= number_format($cotizacion['total_estimado'], 2) ?>
                                    </td>
                                    <td>
                                        <?php
                                            $badge_class = 'bg-secondary';
                                            switch ($cotizacion['status']) {
                                                case 'Pendiente': $badge_class = 'bg-warning text-dark'; break;
                                                case 'En Revisión': $badge_class = 'bg-info text-dark'; break;
                                                case 'Contactado': $badge_class = 'bg-primary'; break;
                                                case 'Confirmado': $badge_class = 'bg-success'; break;
                                                case 'Cancelado': $badge_class = 'bg-danger'; break;
                                            }
                                        ?>
                                        <span class="badge rounded-pill <?= $badge_class ?>"><?= esc($cotizacion['status']) ?></span>
                                    </td>
                                    <td data-order="<?= strtotime($cotizacion['fecha_creacion']) ?>">
                                        <?= date('d/m/Y', strtotime($cotizacion['fecha_creacion'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="<?= site_url('admin/cotizaciones/ver/' . $cotizacion['id']) ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Ver Detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= site_url('admin/cotizaciones/editar/' . $cotizacion['id']) ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="eliminarCotizacion(<?= $cotizacion['id'] ?>)" data-bs-toggle="tooltip" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <form id="form-eliminar" action="<?= site_url('admin/cotizaciones/eliminar') ?>" method="post" class="d-none">
                    <?= csrf_field() ?>
                    <input type="hidden" name="cotizacion_id" id="id-eliminar">
                </form>
            <?php else: ?>
                <div class="alert alert-info text-center" role="alert">
                    No se han encontrado cotizaciones. ¿Por qué no <a href="<?= site_url('admin/cotizaciones/crear') ?>">creas una nueva</a>?
                </div>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- jQuery (DataTables lo necesita) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables Core y extensiones -->
    <script src="https://cdn.datatables.net/2.1.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>
    
    <!-- Botones y sus dependencias (para exportar a Excel, PDF, etc.) -->
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.html5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.print.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.colVis.js"></script>

    <script>
        // Función para manejar la eliminación de forma más limpia
        function eliminarCotizacion(id) {
            if (confirm('¿Estás realmente seguro de que quieres eliminar esta cotización? Esta acción no se puede deshacer.')) {
                document.getElementById('id-eliminar').value = id;
                document.getElementById('form-eliminar').submit();
            }
        }

        $(document).ready(function() {
            // Inicialización de DataTables
            $('#tablaCotizaciones').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.2/i18n/es-MX.json',
                },
                responsive: true,
                order: [[0, 'desc']],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: '<i class="bi bi-files"></i> Copiar',
                        titleAttr: 'Copiar al portapapeles',
                        className: 'btn-secondary'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        titleAttr: 'Exportar a Excel',
                        className: 'btn-success'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        titleAttr: 'Exportar a PDF',
                        className: 'btn-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        titleAttr: 'Imprimir tabla',
                        className: 'btn-info'
                    }
                ],
                columnDefs: [
                    { 
                        targets: -1, // La última columna
                        orderable: false,
                        searchable: false 
                    }
                ]
            });

            // Inicializar los tooltips de Bootstrap
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
    </script>
<?= $this->endSection() ?>