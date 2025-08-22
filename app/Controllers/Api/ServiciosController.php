<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class ServiciosController extends ResourceController
{
    protected $modelName = 'App\Models\ServicioModel';
    protected $format    = 'json';

    /**
     * Devuelve una lista de todos los servicios.
     * Corresponde a: GET /api/v1/servicios
     */
    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    /**
     * Devuelve los datos de un único servicio.
     * Corresponde a: GET /api/v1/servicios/{id}
     */
    public function show($id = null)
    {
        $servicio = $this->model->find($id);
        if (!$servicio) {
            return $this->failNotFound('No se encontró el servicio con ID: ' . $id);
        }
        return $this->respond($servicio);
    }

    /**
     * Crea un nuevo servicio.
     * Corresponde a: POST /api/v1/servicios
     */
    public function create()
    {
        $json = $this->request->getJSON(true);

        // --- Validación ---
        // Es crucial validar los datos que llegan a la API.
        $rules = [
            'nombre'       => 'required|min_length[3]',
            'precio_base'  => 'required|numeric',
            'tipo_cobro'   => 'required|in_list[fijo,por_persona,por_litro]',
            'min_personas' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400); // 400 Bad Request
        }

        // Si la validación pasa, insertamos los datos
        $id = $this->model->insert($json);

        if ($this->model->errors()) {
            return $this->fail($this->model->errors());
        }

        if ($id === false) {
            return $this->failServerError('No se pudo crear el servicio.');
        }
        
        $servicioCreado = $this->model->find($id);

        return $this->respondCreated([
            'status' => 'success', 
            'message' => 'Servicio creado exitosamente.',
            'servicio' => $servicioCreado
        ]);
    }

    /**
     * Actualiza un servicio existente.
     * Corresponde a: PUT /api/v1/servicios/{id}
     */
    public function update($id = null)
    {
        // Primero, verificar si el servicio existe
        $servicioExistente = $this->model->find($id);
        if (!$servicioExistente) {
            return $this->failNotFound('No se encontró el servicio con ID: ' . $id);
        }

        $json = $this->request->getJSON(true);

        if ($this->model->update($id, $json) === false) {
            return $this->fail($this->model->errors());
        }

        $servicioActualizado = $this->model->find($id);

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'Servicio actualizado exitosamente.',
            'servicio' => $servicioActualizado
        ]);
    }

    /**
     * Elimina un servicio.
     * Corresponde a: DELETE /api/v1/servicios/{id}
     */
    public function delete($id = null)
    {
        // Primero, verificar si el servicio existe
        $servicioExistente = $this->model->find($id);
        if (!$servicioExistente) {
            return $this->failNotFound('No se encontró el servicio con ID: ' . $id);
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Servicio eliminado exitosamente.'
            ]);
        } else {
            return $this->failServerError('No se pudo eliminar el servicio.');
        }
    }
}