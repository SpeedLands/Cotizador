<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

    <!-- Mensajes Flash -->
    <?php if (session()->get('mensaje')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= session()->get('mensaje') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->get('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->get('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><?= esc($titulo) ?></h1>
            <p class="text-muted mb-0">Cotización #<?= esc($cotizacion['id']) ?> - Solicitada el <?= date('d/m/Y', strtotime($cotizacion['fecha_creacion'])) ?></p>
        </div>
        <div class="btn-group">
            <a href="<?= site_url('admin/cotizaciones/editar/' . $cotizacion['id']) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <a href="<?= site_url('admin/cotizaciones') ?>" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Detalles -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Información del Cliente</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4"><i class="bi bi-person-fill text-muted me-2"></i>Nombre</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['nombre_completo']) ?></dd>
                        
                        <dt class="col-sm-4"><i class="bi bi-whatsapp text-muted me-2"></i>WhatsApp</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['whatsapp']) ?></dd>

                        <?php if(!empty($cotizacion['nombre_empresa'])): ?>
                            <dt class="col-sm-4"><i class="bi bi-building text-muted me-2"></i>Empresa</dt>
                            <dd class="col-sm-8"><?= esc($cotizacion['nombre_empresa']) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                 <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Detalles del Evento</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4"><i class="bi bi-tag-fill text-muted me-2"></i>Tipo de Evento</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['tipo_evento']) ?></dd>

                        <dt class="col-sm-4"><i class="bi bi-calendar-check text-muted me-2"></i>Fecha y Hora</dt>
                        <dd class="col-sm-8"><?= date('d/m/Y', strtotime($cotizacion['fecha_evento'])) ?> a las <?= date('h:i A', strtotime($cotizacion['hora_evento'])) ?></dd>
                        
                        <dt class="col-sm-4"><i class="bi bi-people-fill text-muted me-2"></i>Invitados</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['cantidad_invitados']) ?></dd>
                        
                        <dt class="col-sm-4"><i class="bi bi-geo-alt-fill text-muted me-2"></i>Dirección</dt>
                        <dd class="col-sm-8"><?= nl2br(esc($cotizacion['direccion_evento'])) ?></dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                 <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Logística y Requisitos</h5>
                </div>
                <div class="card-body">
                   <dl class="row">
                        <dt class="col-sm-4"><i class="bi bi-tools text-muted me-2"></i>Montaje</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['dificultad_montaje'] ?? 'No especificado') ?></dd>
                        
                        <dt class="col-sm-4"><i class="bi bi-card-text text-muted me-2"></i>Adicionales</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['requisitos_adicionales'] ?? 'No especificado') ?></dd>
                        
                        <dt class="col-sm-4"><i class="bi bi-shield-check text-muted me-2"></i>Restricciones</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['restricciones'] ?? 'No especificado') ?></dd>

                        <dt class="col-sm-4"><i class="bi bi-plus-square-dotted text-muted me-2"></i>Otros Servicios</dt>
                        <dd class="col-sm-8"><?= esc($cotizacion['servicios_otros'] ?? 'No especificado') ?></dd>
                   </dl>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Estado, Finanzas y Acciones -->
        <div class="col-lg-5">
            <?php
                $statusInfo = [
                    'Pendiente' => ['color' => 'warning', 'icon' => 'bi-clock-history'],
                    'En Revisión' => ['color' => 'info', 'icon' => 'bi-search'],
                    'Contactado' => ['color' => 'primary', 'icon' => 'bi-telephone-outbound-fill'],
                    'Confirmado' => ['color' => 'success', 'icon' => 'bi-check-circle-fill'],
                    'Cancelado' => ['color' => 'danger', 'icon' => 'bi-x-circle-fill'],
                ];
                $currentStatus = $cotizacion['status'] ?? 'Pendiente';
                $info = $statusInfo[$currentStatus];
            ?>
            <div class="card text-white bg-<?= $info['color'] ?> shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <i class="bi <?= $info['icon'] ?> display-4"></i>
                    <h4 class="card-title mt-2 mb-0">Estado: <?= esc($currentStatus) ?></h4>
                </div>
            </div>

             <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Resumen Financiero</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Costo Base</span>
                        <strong>$<?= number_format($cotizacion['total_base'], 2) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Ajuste por IA</span>
                        <span>$<?= number_format($cotizacion['costo_adicional_ia'], 2) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                        <span class="fw-bold">Total Estimado</span>
                        <strong class="fs-4 text-primary">$<?= number_format($cotizacion['total_estimado'], 2) ?></strong>
                    </li>
                </ul>
                <div class="card-body">
                    <p class="card-text small text-muted"><strong>Justificación de IA:</strong> <?= esc($cotizacion['justificacion_ia']) ?></p>
                    <hr>
                    <form action="<?= site_url('admin/cotizaciones/actualizar-estado') ?>" method="post" class="d-flex gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="cotizacion_id" value="<?= esc($cotizacion['id']) ?>">
                        <select name="status" id="status" class="form-select form-select-sm" aria-label="Cambiar estado">
                            <option>-- Cambiar estado --</option>
                            <?php foreach(array_keys($statusInfo) as $status): ?>
                                <option value="<?= $status ?>" <?= $currentStatus == $status ? 'disabled' : '' ?>><?= $status ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                    </form>
                </div>
            </div>

            <!-- Tarjeta de servicios seleccionados -->
            <div class="card shadow-sm border-0">
                 <div class="card-header bg-white border-0 pt-3">
                    <h5 class="mb-0"><i class="bi bi-card-checklist me-2"></i>Servicios Seleccionados</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if(!empty($servicios_seleccionados)): ?>
                        <?php foreach($servicios_seleccionados as $servicio): ?>
                            <li class="list-group-item"><i class="bi bi-check text-success me-2"></i><?= esc($servicio['nombre']) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted">No se seleccionaron servicios principales.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>