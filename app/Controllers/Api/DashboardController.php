<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CotizacionModel;

class DashboardController extends ResourceController
{
    protected $format = 'json';

    /**
     * Devuelve todos los datos necesarios para construir el dashboard en la app.
     * Corresponde a: GET /api/v1/dashboard
     */
    public function index()
    {
        $cotizacionModel = new CotizacionModel();
        
        // Creamos un array que contendrá toda la respuesta de la API
        $responseData = [];

        // 1. Agrupamos los KPIs (Indicadores Clave) en un objeto
        $responseData['kpis'] = [
            'pendientes'      => $cotizacionModel->contarPorEstado('Pendiente'),
            'confirmadas_mes' => $this->contarConfirmadasMesActual($cotizacionModel),
            'ingresos_mes'    => $cotizacionModel->ingresosConfirmadosPorMes(date('Y'), date('m')),
            'conversion_rate' => $cotizacionModel->getConversionRateKpi()
        ];
        
        // 2. Obtenemos las últimas 5 cotizaciones
        $responseData['ultimas_cotizaciones'] = $cotizacionModel->getUltimasCotizaciones(5);

        // 3. Agrupamos los datos para las gráficas en otro objeto
        $responseData['graficas'] = [
            'ingresos_ultimos_meses' => $cotizacionModel->getIngresosUltimosMeses(6),
            'por_canal_origen'       => $cotizacionModel->getStatsPorCanalOrigen(),
            'por_tipo_evento'        => $cotizacionModel->getStatsPorTipoEvento()
        ];

        // Usamos el helper "respond" para devolver todo el array como una respuesta JSON
        return $this->respond($responseData);
    }

    /**
     * Función privada para contar las confirmadas del mes actual.
     * La copiamos directamente de tu controlador de admin.
     */
    private function contarConfirmadasMesActual($model)
    {
        $year = date('Y');
        $month = date('m');

        return $model->where('status', 'Confirmado')
                    ->where('YEAR(fecha_evento)', $year)
                    ->where('MONTH(fecha_evento)', $month)
                    ->countAllResults();
    }
}