<?php

namespace App\Models;

use CodeIgniter\Model;

class CotizacionModel extends Model
{
    protected $table = 'cotizaciones'; // Nombre de tu tabla de cotizaciones
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

    /**
     * Cuenta las cotizaciones según su estado.
     * @param string $status El estado a contar (ej. 'Pendiente', 'Confirmado')
     * @return int El número de cotizaciones con ese estado.
     */
    public function contarPorEstado($status)
    {
        return $this->where('status', $status)->countAllResults();
    }

    /**
     * Obtiene los ingresos totales de las cotizaciones confirmadas en un mes específico.
     * @param int $year El año (ej. 2024)
     * @param int $month El mes (ej. 7)
     * @return float La suma del total_estimado.
     */
    public function ingresosConfirmadosPorMes($year, $month)
    {
        return $this->selectSum('total_estimado', 'ingresos_totales')
                    ->where('status', 'Confirmado')
                    ->where('YEAR(fecha_evento)', $year)
                    ->where('MONTH(fecha_evento)', $month)
                    ->get()
                    ->getRow()
                    ->ingresos_totales ?? 0;
    }

    /**
     * Obtiene las N cotizaciones más recientes.
     * @param int $limit El número de cotizaciones a obtener.
     * @return array La lista de cotizaciones.
     */
    public function getUltimasCotizaciones($limit = 5)
    {
        return $this->orderBy('fecha_creacion', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Obtiene los ingresos totales confirmados para los últimos N meses.
     * @param int $numeroDeMeses Cuántos meses hacia atrás consultar.
     * @return array Un array con 'labels' para los meses y 'data' para los ingresos.
     */
    public function getIngresosUltimosMeses($numeroDeMeses = 6)
    {
        $resultados = [
            'labels' => [],
            'data'   => [],
        ];

        for ($i = $numeroDeMeses - 1; $i >= 0; $i--) {
            // Obtenemos la fecha para cada uno de los últimos meses
            $fecha = new \DateTime("first day of -$i months");
            $year = $fecha->format('Y');
            $month = $fecha->format('m');

            // Formateamos la etiqueta para la gráfica (ej. "Jun '24")
            $resultados['labels'][] = $fecha->format('M \'y');
            
            // Usamos el método que ya teníamos para obtener los ingresos de ese mes
            $ingresos = $this->ingresosConfirmadosPorMes($year, $month);
            $resultados['data'][] = (float)$ingresos;
        }

        return $resultados;
    }
}