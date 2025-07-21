<?= $this->extend('admin/layout') ?>

<?= $this->section('styles') ?>
    <!-- CSS para DataTables y su integración con Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.2/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.0/css/buttons.bootstrap5.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= esc($titulo) ?></h1>
        <a href="<?= site_url('admin/servicios/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Añadir Servicio
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaServicios" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Servicio</th>
                            <th>Precio Base</th>
                            <th>Tipo de Cobro</th>
                            <th class="text-center">Mín. Personas</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicios as $servicio): ?>
                            <tr>
                                <td><?= esc($servicio['id']) ?></td>
                                <td><strong><?= esc($servicio['nombre']) ?></strong></td>
                                <td data-order="<?= $servicio['precio_base'] ?>">
                                    $<?= number_format($servicio['precio_base'], 2) ?>
                                </td>
                                <td>
                                    <?php
                                        $cobro = esc($servicio['tipo_cobro']);
                                        if ($cobro == 'por_persona'):
                                            echo '<span class="badge rounded-pill bg-primary">Por Persona</span>';
                                        elseif ($cobro == 'por_litro'):
                                            echo '<span class="badge rounded-pill bg-info text-dark">Por Litro</span>';
                                        else:
                                            echo '<span class="badge rounded-pill bg-secondary">Fijo</span>';
                                        endif;
                                    ?>
                                </td>
                                <td class="text-center"><?= esc($servicio['min_personas']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= site_url('admin/servicios/editar/' . $servicio['id']) ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="eliminarServicio(<?= $servicio['id'] ?>)" data-bs-toggle="tooltip" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form id="form-eliminar" action="<?= site_url('admin/servicios/eliminar') ?>" method="post" class="d-none">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="id-eliminar">
            </form>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- Scripts para DataTables (jQuery es un requisito) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.html5.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.print.js"></script>

    <script>
        // Función para manejar la eliminación
        function eliminarServicio(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este servicio?')) {
                document.getElementById('id-eliminar').value = id;
                document.getElementById('form-eliminar').submit();
            }
        }

        $(document).ready(function() {
            // Inicialización de DataTables
            $('#tablaServicios').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.2/i18n/es-MX.json',
                },
                responsive: true,
                order: [[0, 'asc']], // Ordenar por id
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: '<i class="bi bi-files"></i> Copiar',
                        titleAttr: 'Copiar',
                        className: 'btn-secondary'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        titleAttr: 'Exportar a Excel',
                        className: 'btn-success'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        titleAttr: 'Imprimir',
                        className: 'btn-info'
                    }
                ],
                // Desactivar ordenamiento en la columna de acciones
                columnDefs: [
                    { 
                        targets: -1, 
                        orderable: false,
                        searchable: false 
                    }
                ]
            });

            // Inicializar tooltips de Bootstrap
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
    </script>
<?= $this->endSection() ?>