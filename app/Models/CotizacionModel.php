<?php

namespace App\Models;

use CodeIgniter\Model;

class CotizacionModel extends Model
{
    protected $table = 'cotizaciones';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'nombre_completo', 'whatsapp', 'tipo_evento', 'nombre_empresa', 
        'direccion_evento', 'fecha_evento', 'hora_evento', 'horario_consumo',
        'cantidad_invitados', 'servicios_otros', 'mesa_mantel', 'mesa_mantel_otro',
        'personal_servicio', 'acceso_enchufe', 'dificultad_montaje', 
        'tipo_consumidores', 'restricciones', 'requisitos_adicionales',
        'presupuesto', 'como_supiste', 'como_supiste_otro',
        'total_base', 'costo_adicional_ia', 'justificacion_ia',
        'total_estimado', 'status'
    ];

    // Habilitar timestamps
    // protected $useTimestamps = true;
    // protected $createdField  = 'fecha_creacion'; // Ajusta si tu campo se llama así
    // protected $updatedField  = 'updated_at';

    /**
     * Cuenta las cotizaciones según su estado.
     */
    public function contarPorEstado(string $status): int
    {
        return $this->where('status', $status)->countAllResults();
    }

    /**
     * Obtiene los ingresos totales de las cotizaciones confirmadas en un mes específico.
     */
    public function ingresosConfirmadosPorMes(int $year, int $month): float
    {
        $resultado = $this->selectSum('total_estimado')
                          ->where('status', 'Confirmado')
                          ->where('YEAR(fecha_evento)', $year)
                          ->where('MONTH(fecha_evento)', $month)
                          ->get()
                          ->getRow();

        return (float)($resultado->total_estimado ?? 0);
    }

    /**
     * Obtiene las N cotizaciones más recientes.
     */
    public function getUltimasCotizaciones(int $limit = 5): array
    {
        return $this->orderBy('fecha_creacion', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * VERSIÓN OPTIMIZADA: Obtiene los ingresos totales confirmados para los últimos N meses.
     * Realiza una única consulta a la base de datos.
     */
    public function getIngresosUltimosMeses(int $numeroDeMeses = 6): array
    {
        $datosGrafica = [
            'labels' => [],
            'data'   => [],
        ];
        $ingresosPorMes = [];

        // 1. Preparamos un array con los últimos N meses, inicializados en 0
        for ($i = 0; $i < $numeroDeMeses; $i++) {
            $fecha = strtotime("-$i months");
            // Para que los meses salgan en español, configuramos el locale
            setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish');
            $mesNombre = strftime('%B', $fecha); // Nombre completo del mes
            $mesAno = date('Y-m', $fecha);
            
            $datosGrafica['labels'][] = ucfirst($mesNombre);
            $ingresosPorMes[$mesAno] = 0;
        }

        // 2. Obtenemos los datos reales de la DB en UNA SOLA CONSULTA
        $fechaLimite = date('Y-m-01', strtotime("-" . ($numeroDeMeses - 1) . " months"));
        $resultados = $this->select("SUM(total_estimado) as total, DATE_FORMAT(fecha_evento, '%Y-%m') as mes_ano")
                           ->where('status', 'Confirmado')
                           ->where('fecha_evento >=', $fechaLimite)
                           ->groupBy("DATE_FORMAT(fecha_evento, '%Y-%m')")
                           ->get()
                           ->getResultArray();

        // 3. Llenamos nuestro array con los datos de la DB
        foreach ($resultados as $row) {
            if (isset($ingresosPorMes[$row['mes_ano']])) {
                $ingresosPorMes[$row['mes_ano']] = (float)$row['total'];
            }
        }

        // 4. Asignamos los datos al array final y los invertimos para el orden cronológico correcto
        $datosGrafica['data'] = array_values($ingresosPorMes);
        $datosGrafica['labels'] = array_reverse($datosGrafica['labels']);
        $datosGrafica['data'] = array_reverse($datosGrafica['data']);

        return $datosGrafica;
    }

    /**
     * Calcula los KPIs para la tasa de conversión.
     * @return array Un array con el total de cotizaciones, las confirmadas y la tasa.
     */
    public function getConversionRateKpi(): array
    {
        // 1. Contamos el total de cotizaciones que no estén canceladas (opcional, pero recomendado)
        $totalCotizaciones = $this->where('status !=', 'Cancelado')->countAllResults();

        // 2. Contamos las que se consideran una conversión exitosa
        $estadosExitosos = ['Confirmado', 'Pagado Parcial', 'Pagado Total'];
        $totalConfirmadas = $this->whereIn('status', $estadosExitosos)->countAllResults();

        // 3. Calculamos la tasa, evitando la división por cero
        $tasa = 0;
        if ($totalCotizaciones > 0) {
            $tasa = ($totalConfirmadas / $totalCotizaciones) * 100;
        }

        return [
            'total'       => $totalCotizaciones,
            'confirmadas' => $totalConfirmadas,
            'tasa'        => round($tasa, 2) // Redondeamos a 2 decimales
        ];
    }

    /**
     * Obtiene estadísticas agrupadas por el canal de origen (cómo supieron de nosotros).
     * @return array Un array formateado para Chart.js con 'labels' y 'data'.
     */
    public function getStatsPorCanalOrigen(): array
    {
        // 1. Hacemos la consulta a la base de datos
        $query = $this->select('como_supiste, COUNT(id) as total')
                      ->where('status !=', 'Cancelado') // Opcional: excluir canceladas
                      ->groupBy('como_supiste')
                      ->orderBy('total', 'DESC') // Mostrar los canales más populares primero
                      ->findAll();

        // 2. Preparamos los datos para la gráfica
        $stats = [
            'labels' => [],
            'data'   => [],
        ];

        foreach ($query as $row) {
            // Si el campo está vacío o nulo, lo agrupamos como 'No especificado'
            $label = empty($row['como_supiste']) ? 'No especificado' : $row['como_supiste'];
            $stats['labels'][] = $label;
            $stats['data'][] = (int)$row['total'];
        }

        return $stats;
    }

    /**
     * Obtiene estadísticas agrupadas por el tipo de evento.
     * @return array Un array formateado para Chart.js con 'labels' y 'data'.
     */
    public function getStatsPorTipoEvento(): array
    {
        // 1. Hacemos la consulta a la base de datos
        $query = $this->select('tipo_evento, COUNT(id) as total')
                      ->where('status !=', 'Cancelado')
                      ->groupBy('tipo_evento')
                      ->orderBy('total', 'DESC')
                      ->findAll();

        // 2. Preparamos los datos para la gráfica
        $stats = [
            'labels' => [],
            'data'   => [],
        ];

        foreach ($query as $row) {
            $label = empty($row['tipo_evento']) ? 'No especificado' : $row['tipo_evento'];
            $stats['labels'][] = $label;
            $stats['data'][] = (int)$row['total'];
        }

        return $stats;
    }
}