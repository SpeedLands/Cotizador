<?php 
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{

    public function index()
    {
        $cotizacionModel = new \App\Models\CotizacionModel();

        // 1. Obtener los KPIs (Indicadores Clave)
        $data['pendientes'] = $cotizacionModel->contarPorEstado('Pendiente');
        $data['confirmadas_mes'] = $this->contarConfirmadasMesActual($cotizacionModel);
        $data['ingresos_mes'] = $cotizacionModel->ingresosConfirmadosPorMes(date('Y'), date('m'));
        
        // --- ¡AQUÍ AÑADIMOS EL NUEVO KPI! ---
        $data['kpi_conversion'] = $cotizacionModel->getConversionRateKpi();
        
        // 2. Obtener las últimas 5 cotizaciones
        $data['ultimas_cotizaciones'] = $cotizacionModel->getUltimasCotizaciones(5);

        // 3. Datos para la gráfica
        $data['grafica_ingresos'] = $cotizacionModel->getIngresosUltimosMeses(6);
        $data['grafica_ingresos_json'] = json_encode($data['grafica_ingresos']);

        $stats_canal_origen = $cotizacionModel->getStatsPorCanalOrigen();
        $data['stats_canal_origen_json'] = json_encode($stats_canal_origen);

        $stats_tipo_evento = $cotizacionModel->getStatsPorTipoEvento();
        $data['stats_tipo_evento_json'] = json_encode($stats_tipo_evento);

        $data['titulo'] = 'Dashboard Principal';

        return view('admin/dashboard_view', $data);
    }

    /**
     * Función privada para contar las confirmadas del mes actual.
     * Es más limpio que poner toda la lógica en index().
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