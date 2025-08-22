<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CotizacionModel;

class CalendarioController extends ResourceController
{
    protected $format = 'json';

    /**
     * Devuelve los eventos confirmados para mostrarlos en un calendario.
     * Corresponde a: GET /api/v1/calendario/eventos
     */
    public function index()
    {
        $cotizacionModel = new CotizacionModel();
        
        // Obtenemos solo las cotizaciones con estado 'Confirmado'
        $eventosConfirmados = $cotizacionModel->where('status', 'Confirmado')->findAll();

        $eventosParaApi = [];
        foreach ($eventosConfirmados as $evento) {
            // Nos aseguramos de que el evento tenga una fecha
            if (empty($evento['fecha_evento'])) {
                continue;
            }

            $eventosParaApi[] = [
                // El ID es crucial para que la app pueda solicitar más detalles
                'id'    => $evento['id'], 
                // El título del evento (nombre del cliente)
                'title' => $evento['nombre_completo'],
                // La fecha del evento. 'start' es un nombre estándar para calendarios.
                'start' => $evento['fecha_evento'] 
            ];
        }

        // Devolvemos la lista de eventos en formato JSON
        return $this->respond($eventosParaApi);
    }
}