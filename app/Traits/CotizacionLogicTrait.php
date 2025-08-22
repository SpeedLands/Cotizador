<?php
// app/Traits/CotizacionLogicTrait.php

namespace App\Traits;

use App\Models\ServicioModel;
use App\Models\UsuarioModel;
use App\Models\CotizacionModel;
use App\Models\NotificationModel;
use App\Models\CotizacionServiciosModel;
use App\Libraries\FirebaseService;
use App\Libraries\LogisticsAIService;

trait CotizacionLogicTrait
{
    /**
     * Método para centralizar el cálculo de costos y la preparación de datos.
     * Es reutilizable para crear y actualizar.
     *
     * @param array $data Los datos de la cotización.
     * @return array El array de datos listo para la base de datos.
     */
    protected function _prepararDatosCotizacion(array $data): array
    {
        // --- CÁLCULO DE COSTOS ---
        $cantidadInvitados = (int)($data['cantidad_invitados'] ?? 1);
        $serviciosSeleccionadosIds = $data['servicios'] ?? [];
        $litrosAgua = ceil($cantidadInvitados / 6);
        $costoBase = 0;

        if (!empty($serviciosSeleccionadosIds)) {
            $servicioModel = new ServicioModel();
            $serviciosInfo = $servicioModel->whereIn('id', $serviciosSeleccionadosIds)->findAll();
            foreach ($serviciosInfo as $servicio) {
                if ($cantidadInvitados < $servicio['min_personas']) continue;
                
                switch ($servicio['tipo_cobro']) {
                    case 'por_persona':
                        $costoBase += (float)$servicio['precio_base'] * $cantidadInvitados;
                        break;
                    case 'por_litro':
                        $costoBase += (float)$servicio['precio_base'] * $litrosAgua;
                        break;
                    default: // 'fijo'
                        $costoBase += (float)$servicio['precio_base'];
                        break;
                }
            }
        }

        $logisticsService = new LogisticsAIService();
        $prediction = $logisticsService->predict($data);
        $costoAdicionalIA = $prediction['costo'];
        $justificacionIA = $prediction['justificacion'];

        return [
            'nombre_completo' => $data['nombre_completo'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'como_supiste' => $data['como_supiste'] ?? null,
            'como_supiste_otro' => $data['como_supiste_otro'] ?? null,
            'tipo_evento' => $data['tipo_evento'] ?? null,
            'nombre_empresa' => $data['nombre_empresa'] ?? null,
            'direccion_evento' => $data['direccion_evento'] ?? null,
            'fecha_evento' => $data['fecha_evento'] ?? null,
            'hora_evento' => $data['hora_evento'] ?? null,
            'horario_consumo' => $data['horario_consumo'] ?? null,
            'cantidad_invitados' => $cantidadInvitados,
            'servicios_otros' => $data['servicios_otros'] ?? null,
            'mesa_mantel' => $data['mesa_mantel'] ?? null,
            'mesa_mantel_otro' => $data['mesa_mantel_otro'] ?? null,
            'personal_servicio' => $data['personal_servicio'] ?? null,
            'acceso_enchufe' => $data['acceso_enchufe'] ?? null,
            'dificultad_montaje' => $data['dificultad_montaje'] ?? null,
            'tipo_consumidores' => $data['tipo_consumidores'] ?? null,
            'restricciones' => $data['restricciones'] ?? null,
            'requisitos_adicionales' => $data['requisitos_adicionales'] ?? null,
            'presupuesto' => $data['presupuesto'] ?? null,
            'total_base' => $costoBase,
            'costo_adicional_ia' => $costoAdicionalIA,
            'justificacion_ia' => $justificacionIA,
            'total_estimado' => $costoBase + $costoAdicionalIA,
            'guest_token' => $data['guest_token'] ?? null,
            // El 'status' no se incluye aquí para que pueda ser manejado por separado
        ];
    }

    /**
     * Lógica centralizada para procesar y guardar una cotización.
     * Acepta un array de datos, lo que lo hace reutilizable por la API (JSON) y el controlador web (POST).
     *
     * @param array $data Los datos de la cotización.
     * @return array Un array con el resultado: ['success' => bool, 'message' => string, 'id' => int|null]
     */
    protected function _procesarYGuardarCotizacion(array $data): array
    {
        $datosCotizacion = $this->_prepararDatosCotizacion($data);
        $datosCotizacion['status'] = 'Pendiente'; // Status inicial
        $serviciosSeleccionadosIds = $data['servicios'] ?? [];

        // --- TRANSACCIÓN DE BASE DE DATOS ---
        $cotizacionModel = new CotizacionModel();
        $cotizacionServiciosModel = new CotizacionServiciosModel();
        $db = db_connect();
        $db->transStart();
        
        $cotizacionId = $cotizacionModel->insert($datosCotizacion, true);

        if (!$cotizacionId) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Fallo al insertar la cotización principal.'];
        }

        if (!empty($serviciosSeleccionadosIds)) {
            foreach ($serviciosSeleccionadosIds as $servicioId) {
                $cotizacionServiciosModel->insert([
                    'cotizacion_id' => $cotizacionId,
                    'servicio_id'   => $servicioId
                ]);
            }
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
             return ['success' => false, 'message' => 'No se pudo guardar la cotización en la base de datos.'];
        }

        // --- LÓGICA DE NOTIFICACIONES ---
        $this->_enviarNotificaciones($cotizacionId, $data['nombre_completo'] ?? 'un cliente');

        return ['success' => true, 'message' => '¡Cotización enviada con éxito!', 'id' => $cotizacionId];
    }

    /**
     * Lógica encapsulada para enviar notificaciones.
     */
    private function _enviarNotificaciones(int $cotizacionId, string $nombreCliente)
    {
        $title = "Nueva Cotización Recibida";
        $body = "De: $nombreCliente. Toca para ver los detalles.";
        $notificationPayload = [
            'action_url' => "/cotizacion/{$cotizacionId}",
            'notification_type' => 'nueva_cotizacion',
            'push_data' => ['type' => 'nueva_cotizacion', 'cotizacion_id' => (string)$cotizacionId]
        ];

        $userModel = new UsuarioModel();
        $admins = $userModel->findAll(); 
        if (!empty($admins)) {
            $notificationModel = new NotificationModel();
            foreach ($admins as $admin) {
                $notificationModel->insert([
                    'user_id' => $admin['id'],
                    'title' => $title,
                    'body' => $body,
                    'action_url' => $notificationPayload['action_url'],
                    'notification_type' => $notificationPayload['notification_type']
                ]);
            }
        }
        
        try {
            $firebase = new FirebaseService();
            $firebase->sendToTopic('admins', $title, $body, $notificationPayload['push_data']);
        } catch (\Exception $e) {
            log_message('error', 'Fallo al enviar notificación Push: ' . $e->getMessage());
        }
    }
}