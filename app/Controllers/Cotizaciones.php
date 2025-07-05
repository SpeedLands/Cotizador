<?php

namespace App\Controllers;

use App\Models\CotizacionModel;

class Cotizaciones extends BaseController
{
    public function index()
    {
        $model = new CotizacionModel();
        $data['cotizaciones'] = $model->orderBy('fecha_creacion', 'DESC')->findAll();

        return view('admin/cotizaciones/index', $data);
    }

    public function detalle($id)
    {
        $model = new CotizacionModel();
        $cotizacion = $model->find($id);

        if (!$cotizacion) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Cotización no encontrada");
        }

        return view('admin/cotizaciones/detalle', ['c' => $cotizacion]);
    }

    public function cambiarEstado()
    {
        $id = $this->request->getPost('id');
        $estado = $this->request->getPost('estado');

        if (!$id || !$estado) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Datos incompletos.']);
        }

        $model = new \App\Models\CotizacionModel();

        try {
            $model->update($id, ['estado' => $estado]);
            return $this->response->setJSON(['mensaje' => 'Estado actualizado correctamente.']);
        } catch (\Exception $e) {
            log_message('error', 'Error al actualizar estado: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error interno al actualizar estado.']);
        }
    }

    
}
