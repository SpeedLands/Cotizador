<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class NotificationsController extends ResourceController
{
    protected $modelName = 'App\Models\NotificationModel';
    protected $format    = 'json';

    /**
     * Lista las notificaciones del usuario autenticado.
     * Corresponde a: GET /api/v1/notifications
     */
    public function index()
    {
        // ASUNCIÓN IMPORTANTE: Tu filtro de autenticación de API debe hacer
        // que el ID del usuario actual esté disponible.
        // Usaremos auth()->id() como ejemplo (común en CodeIgniter Shield).
        // Adapta esto a cómo obtienes el ID del usuario en tu sistema de auth.
        $userId = auth()->id();

        if (!$userId) {
            return $this->failUnauthorized('Se requiere autenticación para acceder a este recurso.');
        }

        $notifications = $this->model
                                ->where('user_id', $userId)
                                ->orderBy('created_at', 'DESC') // Las más nuevas primero
                                ->findAll();

        return $this->respond($notifications);
    }

    /**
     * Actualiza una notificación, principalmente para marcarla como leída.
     * Corresponde a: PUT /api/v1/notifications/{id}
     */
    public function update($id = null)
    {
        $userId = auth()->id();
        if (!$userId) {
            return $this->failUnauthorized();
        }

        // 1. Buscar la notificación
        $notification = $this->model->find($id);
        if (!$notification) {
            return $this->failNotFound('No se encontró la notificación.');
        }

        // 2. ¡VERIFICACIÓN DE SEGURIDAD! Asegurarse de que el usuario es el dueño.
        if ($notification['user_id'] != $userId) {
            return $this->failForbidden('No tienes permiso para modificar este recurso.');
        }

        // 3. Actualizar el estado a "leída" (is_read = 1)
        if ($this->model->update($id, ['is_read' => 1])) {
            return $this->respondUpdated(['status' => 'success', 'message' => 'Notificación marcada como leída.']);
        } else {
            return $this->failServerError('No se pudo actualizar la notificación.');
        }
    }

    /**
     * Elimina una notificación del usuario.
     * Corresponde a: DELETE /api/v1/notifications/{id}
     */
    public function delete($id = null)
    {
        $userId = auth()->id();
        if (!$userId) {
            return $this->failUnauthorized();
        }

        // 1. Buscar la notificación
        $notification = $this->model->find($id);
        if (!$notification) {
            return $this->failNotFound('No se encontró la notificación.');
        }

        // 2. ¡VERIFICACIÓN DE SEGURIDAD! Asegurarse de que el usuario es el dueño.
        if ($notification['user_id'] != $userId) {
            return $this->failForbidden('No tienes permiso para eliminar este recurso.');
        }

        // 3. Eliminar la notificación
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['status' => 'success', 'message' => 'Notificación eliminada.']);
        } else {
            return $this->failServerError('No se pudo eliminar la notificación.');
        }
    }
}