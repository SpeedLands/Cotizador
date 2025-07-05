<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">📊 Dashboard de Cotizaciones</h1>

    <form method="get" class="row mb-4">
        <div class="col-md-3">
            <label for="anio">Año:</label>
            <select name="anio" class="form-select">
                <?php for ($y = 2023; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= ($anio == $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="mes">Mes:</label>
            <select name="mes" class="form-select">
                <option value="">Todos</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($mes == $m) ? 'selected' : '' ?>>
                        <?= ucfirst(strftime('%B', mktime(0, 0, 0, $m, 10))) ?>
                    </option>
                <?php endfor ?>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
        <div class="col-md-3 align-self-end text-end">
            <a href="<?= base_url("admin/dashboard/exportar?anio=$anio&mes=$mes") ?>" class="btn btn-outline-success me-2">📥 Excel</a>
            <a href="<?= base_url("admin/dashboard/pdf?anio=$anio&mes=$mes") ?>" class="btn btn-outline-danger">📄 PDF</a>
        </div>
    </form>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Cotizaciones</h5>
                    <p class="card-text fs-2"><?= $total ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Eventos Confirmados</h5>
                    <p class="card-text fs-2"><?= $confirmados ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-dark">
                <div class="card-body">
                    <h5 class="card-title">Tasa de Conversión</h5>
                    <p class="card-text fs-2"><?= $conversion ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <h3>📅 Ingresos por Mes</h3>
    <canvas id="graficoIngresos" height="100"></canvas>

    <h3 class="mt-5">📁 Tipos de Evento</h3>
    <canvas id="graficoTipos" height="100"></canvas>
</div>

<script>
const ingresosCtx = document.getElementById('graficoIngresos');
new Chart(ingresosCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($ingresos, 'mes')) ?>,
        datasets: [
            {
                label: 'Ingresos Reales (MXN)',
                data: <?= json_encode(array_map(fn($i) => floatval($i['total']), $ingresos)) ?>,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 123, 255, 0.3)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Meta Proyectada (MXN)',
                data: <?= json_encode(array_fill(0, count($ingresos), $metaMensual)) ?>,
                borderColor: 'green',
                borderDash: [5, 5],
                pointStyle: 'cross',
                fill: false
            }
        ]
    }
});

const tiposCtx = document.getElementById('graficoTipos');
new Chart(tiposCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($tipos, 'tipo_evento')) ?>,
        datasets: [{
            label: 'Eventos por Tipo',
            data: <?= json_encode(array_map(fn($t) => intval($t['cantidad']), $tipos)) ?>,
            backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107', '#6f42c1', '#17a2b8']
        }]
    }
});
</script>
</body>
</html>
