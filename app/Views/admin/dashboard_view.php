<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

    <div class="mb-4">
        <h1 class="h3">Dashboard</h1>
        <p class="text-muted">¡Bienvenido de nuevo, <?= esc(session()->get('nombre_usuario')) ?>! Este es el resumen de tu negocio.</p>
    </div>

        <div class="row">
        <!-- Tarjeta: Cotizaciones Pendientes -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title text-muted mb-1">Pendientes</h5>
                            <p class="card-text h2 mb-0"><?= esc($pendientes) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Eventos Confirmados (Mes) -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-calendar-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title text-muted mb-1">Confirmados (Mes)</h5>
                            <p class="card-text h2 mb-0"><?= esc($confirmadas_mes) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta: Ingresos del Mes -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                             <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title text-muted mb-1">Ingresos (Mes)</h5>
                            <p class="card-text h2 mb-0">$<?= number_format($ingresos_mes, 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================== -->
        <!-- ==         ¡AQUÍ ESTÁ LA NUEVA TARJETA!         == -->
        <!-- ================================================== -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                             <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-bullseye fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title text-muted mb-1">Tasa de Conversión</h5>
                            <p class="card-text h2 mb-0"><?= number_format($kpi_conversion['tasa'], 1) ?>%</p>
                            <small class="text-muted"><?= $kpi_conversion['confirmadas'] ?> de <?= $kpi_conversion['total'] ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna para la Gráfica de Ingresos -->
        <div class="col-lg-7 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Ingresos Confirmados (Últimos 6 Meses)</h5>
                </div>
                <div class="card-body">
                    <!-- El canvas ahora es responsive dentro de su contenedor -->
                    <div style="position: relative; height:300px">
                        <canvas id="graficaIngresos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna para las Últimas Cotizaciones -->
        <div class="col-lg-5 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Últimas Cotizaciones</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($ultimas_cotizaciones)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr class="table-light">
                                        <th class="ps-3">Cliente</th>
                                        <th>Estado</th>
                                        <th class="text-end pe-3">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas_cotizaciones as $cotizacion): ?>
                                        <tr class="align-middle">
                                            <td class="ps-3">
                                                <strong><?= esc($cotizacion['nombre_completo']) ?></strong>
                                                <small class="d-block text-muted"><?= date('d M Y', strtotime($cotizacion['fecha_evento'])) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                    $badge_class = 'bg-secondary';
                                                    switch ($cotizacion['status']) {
                                                        case 'Pendiente': $badge_class = 'bg-warning text-dark'; break;
                                                        case 'Confirmado': $badge_class = 'bg-success'; break;
                                                        case 'Cancelado': $badge_class = 'bg-danger'; break;
                                                        default: $badge_class = 'bg-info text-dark'; break;
                                                    }
                                                ?>
                                                <span class="badge rounded-pill <?= $badge_class ?>"><?= esc($cotizacion['status']) ?></span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="<?= site_url('admin/cotizaciones/ver/' . $cotizacion['id']) ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Ver Detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <p class="text-muted mb-0">No hay cotizaciones recientes para mostrar.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($ultimas_cotizaciones)): ?>
                <div class="card-footer bg-white text-center border-0">
                     <a href="<?= site_url('admin/cotizaciones') ?>" class="text-primary">Ver todas las cotizaciones</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfica: Canal de Origen -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Canal de Origen de Clientes</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position: relative; height:250px; width:250px">
                        <canvas id="graficaCanalOrigen"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica: Tipo de Evento -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Distribución por Tipo de Evento</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position: relative; height:250px; width:250px">
                        <canvas id="graficaTipoEvento"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- INICIALIZAR TOOLTIPS DE BOOTSTRAP ---
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // --- CONFIGURACIÓN DE LA GRÁFICA ---
        const datosGrafica = <?= $grafica_ingresos_json ?>;
        const ctx = document.getElementById('graficaIngresos').getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');   
        gradient.addColorStop(1, 'rgba(54, 162, 235, 0.2)');

        const graficaIngresos = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datosGrafica.labels,
                datasets: [{
                    label: 'Ingresos Mensuales',
                    data: datosGrafica.data,
                    backgroundColor: gradient, // Usar el gradiente
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(54, 162, 235, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + new Intl.NumberFormat('es-MX').format(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false // Oculta las líneas de la cuadrícula del eje X
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleFont: { size: 14 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });

        const datosCanalOrigen = <?= $stats_canal_origen_json ?>;
        const ctxCanal = document.getElementById('graficaCanalOrigen');

        if (ctxCanal && datosCanalOrigen.data.length > 0) {
            new Chart(ctxCanal, {
                type: 'doughnut', // Gráfica de dona
                data: {
                    labels: datosCanalOrigen.labels,
                    datasets: [{
                        data: datosCanalOrigen.data,
                        backgroundColor: [ // Paleta de colores atractiva
                            '#4e73df', 
                            '#1cc88a', 
                            '#36b9cc', 
                            '#f6c23e', 
                            '#e74a3b',
                            '#858796'
                        ],
                        hoverBackgroundColor: [
                            '#2e59d9', 
                            '#17a673', 
                            '#2c9faf', 
                            '#dda20a', 
                            '#be2617',
                            '#60616f'
                        ],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom', // Mover las etiquetas abajo
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.chart.getDatasetMeta(0).total;
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                },
            });
        } else if (ctxCanal) {
            // Mensaje si no hay datos
            ctxCanal.getContext('2d').fillText("No hay datos de origen para mostrar.", 10, 50);
        }

        const datosTipoEvento = <?= $stats_tipo_evento_json ?>;
        const ctxTipoEvento = document.getElementById('graficaTipoEvento');

        if (ctxTipoEvento && datosTipoEvento.data.length > 0) {
            new Chart(ctxTipoEvento, {
                type: 'doughnut',
                data: {
                    labels: datosTipoEvento.labels,
                    datasets: [{
                        data: datosTipoEvento.data,
                        backgroundColor: [ // Usamos una paleta de colores similar para consistencia
                            '#4e73df', 
                            '#1cc88a', 
                            '#36b9cc', 
                            '#f6c23e', 
                            '#e74a3b'
                        ],
                        hoverBackgroundColor: [
                            '#2e59d9', 
                            '#17a673', 
                            '#2c9faf', 
                            '#dda20a', 
                            '#be2617'
                        ],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: { // Reutilizamos las mismas opciones para un look consistente
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.chart.getDatasetMeta(0).total;
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                },
            });
        } else if (ctxTipoEvento) {
            ctxTipoEvento.getContext('2d').fillText("No hay datos de eventos para mostrar.", 10, 50);
        }

    });
</script>

<?= $this->endSection() ?>