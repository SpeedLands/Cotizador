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
        $data['confirmadas_mes'] = $this->contarConfirmadasMesActual($cotizacionModel); // Usaremos una función helper
        $data['ingresos_mes'] = $cotizacionModel->ingresosConfirmadosPorMes(date('Y'), date('m'));
        
        // 2. Obtener las últimas 5 cotizaciones
        $data['ultimas_cotizaciones'] = $cotizacionModel->getUltimasCotizaciones(5);

        $data['pendientes'] = $cotizacionModel->contarPorEstado('Pendiente');

        $data['grafica_ingresos'] = $cotizacionModel->getIngresosUltimosMeses(6);
        // Pasamos los datos a la vista en formato JSON para que JavaScript pueda leerlos fácilmente
        $data['grafica_ingresos_json'] = json_encode($data['grafica_ingresos']);

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