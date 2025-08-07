<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ServicioModel;

class ServiciosController extends BaseController
{
    public function index()
    {
        try {
            $servicioModel = new ServicioModel();

            $servicios = $servicioModel->findAll();

            $serviciosFormateados = array_map(function($servicio) {
                $servicio['id'] = (int) $servicio['id']; // Convertir id a entero
                $servicio['precio_base'] = (float) $servicio['precio_base']; // Convertir precio a flotante/decimal
                $servicio['min_personas'] = (int) $servicio['min_personas']; // Convertir min_personas a entero
                return $servicio;
            }, $servicios);
            
            return $this->response->setJSON($serviciosFormateados)->setStatusCode(200);

        } catch (\Exception $e) {
            
            log_message('error', 'Error en ServiciosController::index: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Ocurrió un error en el servidor al obtener los servicios.'
            ])->setStatusCode(500);
        }
    }

}