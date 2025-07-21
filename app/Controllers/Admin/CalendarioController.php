<?php 
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CotizacionModel;

class CalendarioController extends BaseController
{
    // Este método muestra la página principal con el calendario vacío
    public function index()
    {
        $data['titulo'] = 'Calendario de Eventos';
        return view('admin/calendario/index_view', $data);
    }

    // Este método servirá como la fuente de datos (API) para FullCalendar
    public function eventos()
    {
        $cotizacionModel = new CotizacionModel();
        
        // Obtenemos solo las cotizaciones con estado 'Confirmado'
        $eventosConfirmados = $cotizacionModel->where('status', 'Confirmado')->findAll();

        $eventosParaCalendario = [];
        foreach ($eventosConfirmados as $evento) {
            // Asegurarse de que hay fecha de evento para no causar errores
            if (empty($evento['fecha_evento'])) {
                continue;
            }

            $eventosParaCalendario[] = [
                'title' => $evento['nombre_completo'],
                'start' => $evento['fecha_evento'],
                'url'   => site_url('admin/cotizaciones/ver/' . $evento['id']),
                'backgroundColor' => '#28a745',
                'borderColor' => '#28a745'
            ];
        }

        // Devolvemos el JSON directamente
        return $this->response->setJSON($eventosParaCalendario);
    }
}