<?php

namespace App\Controllers;

use App\Traits\CotizacionLogicTrait;
use App\Models\ServicioModel;

class Cotizador extends BaseController
{
    use CotizacionLogicTrait; 

    public function index()
    {
        $servicioModel = new ServicioModel();
        $data['servicios'] = $servicioModel->findAll();
        return view('public/cotizador_view', $data);
    }

    /**
     * Guarda una nueva cotización y la asocia a la sesión del visitante.
     */
    public function guardar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $postData = $this->request->getPost();
        $session = session();

        // --- ¡AQUÍ ESTÁ LA LÓGICA DE SESIÓN PARA LA WEB! ---
        // 1. Añadimos el ID de la sesión del visitante a los datos que se guardarán.
        //    El Trait ya está preparado para recibir esto.
        $postData['session_id'] = $session->get('session_id');

        // 2. Llamamos a la lógica centralizada del Trait
        $resultado = $this->_procesarYGuardarCotizacion($postData);

        if ($resultado['success']) {
            // 3. Guardamos el ID de la nueva cotización en la sesión del visitante.
            //    Esto nos permitirá encontrarla más tarde.
            $session->set('mi_cotizacion_id', $resultado['id']);

            return $this->response->setJSON([
                'success' => true, 
                'message' => $resultado['message'], 
                'cotizacion_id' => $resultado['id']
            ])->setStatusCode(201);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $resultado['message']]);
        }
    }

    /**
     * Permite a un visitante ver la cotización asociada a su sesión.
     */
    public function verMiCotizacion()
    {
        $session = session();
        $cotizacionId = $session->get('mi_cotizacion_id');

        if (!$cotizacionId) {
            return redirect()->to('/')->with('error', 'No tienes una cotización activa.');
        }

        $cotizacionModel = new \App\Models\CotizacionModel();
        $cotizacion = $cotizacionModel->find($cotizacionId);

        // --- CONTROL DE SEGURIDAD CRÍTICO ---
        // Verificamos que la cotización exista Y que el session_id guardado en la DB
        // coincida con el session_id actual del visitante.
        if (!$cotizacion || $cotizacion['session_id'] !== $session->get('session_id')) {
            $session->remove('mi_cotizacion_id');
            return redirect()->to('/')->with('error', 'No se pudo encontrar tu cotización.');
        }

        $data['cotizacion'] = $cotizacion;
        $data['titulo'] = 'Resumen de tu Cotización';
        
        // Necesitarás crear esta vista para mostrar los detalles
        return view('public/mi_cotizacion_view', $data); 
    }

    public function fechasOcupadas()
    {
        $cotizacionModel = new \App\Models\CotizacionModel();
        $fechasDb = $cotizacionModel->select('fecha_evento')
                                    ->where('status', 'Confirmado')
                                    ->findAll();
        $fechasOcupadas = array_column($fechasDb, 'fecha_evento');

        return $this->response->setJSON($fechasOcupadas);
    }
}