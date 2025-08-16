<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ServicioModel; 
use App\Models\UsuarioModel;
use App\Models\CotizacionModel;
use App\Models\NotificationModel; 
use App\Models\CotizacionServiciosModel;
use App\Libraries\FirebaseService;
use App\Libraries\LogisticsAIService;
use Config\Services;

class Cotizador extends BaseController
{
    public function index()
    {
        $servicioModel = new ServicioModel();
        $data['servicios'] = $servicioModel->findAll();
        return view('public/cotizador_view', $data);
    }

    public function guardar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $postData = $this->request->getPost();
        $cantidadInvitados = (int)($postData['cantidad_invitados'] ?? 1);
        $serviciosSeleccionadosIds = $postData['servicios'] ?? [];
        $litrosAgua = ceil($cantidadInvitados / 6);
        $costoBase = 0;
        
        if (!empty($serviciosSeleccionadosIds)) {
            $servicioModel = new ServicioModel();
            $serviciosInfo = $servicioModel->whereIn('id', $serviciosSeleccionadosIds)->findAll();
            foreach ($serviciosInfo as $servicio) {
                if ($cantidadInvitados < $servicio['min_personas']) continue;
                if ($servicio['tipo_cobro'] == 'por_persona') {
                    $costoBase += $servicio['precio_base'] * $cantidadInvitados;
                } elseif ($servicio['tipo_cobro'] == 'por_litro') {
                    $costoBase += $servicio['precio_base'] * $litrosAgua;
                } else {
                    $costoBase += $servicio['precio_base'];
                }
            }
        }

        $logisticsService = new LogisticsAIService();

        $prediction = $logisticsService->predict($postData);
        $costoAdicionalIA = $prediction['costo'];
        $justificacionIA = $prediction['justificacion'];
        
        // --- GUARDAR TODO EN LA BASE DE DATOS ---
        $cotizacionModel = new CotizacionModel();
        $cotizacionServiciosModel = new CotizacionServiciosModel();

        $datosCotizacion = [
            // Datos del cliente
            'nombre_completo' => $postData['nombre_completo'] ?? null,
            'whatsapp' => $postData['whatsapp'] ?? null,
            'como_supiste' => $postData['como_supiste'] ?? null,
            'como_supiste_otro' => $postData['como_supiste_otro'] ?? null,
            
            // Detalles del evento
            'tipo_evento' => $postData['tipo_evento'] ?? null,
            'nombre_empresa' => $postData['nombre_empresa'] ?? null,
            'direccion_evento' => $postData['direccion_evento'] ?? null,
            'fecha_evento' => $postData['fecha_evento'] ?? null,
            'hora_evento' => $postData['hora_evento'] ?? null,
            'horario_consumo' => $postData['horario_consumo'] ?? null,
            'cantidad_invitados' => $cantidadInvitados,
            
            // Logística y requisitos
            'servicios_otros' => $postData['servicios_otros'] ?? null,
            'mesa_mantel' => $postData['mesa_mantel'] ?? null,
            'mesa_mantel_otro' => $postData['mesa_mantel_otro'] ?? null,
            'personal_servicio' => $postData['personal_servicio'] ?? null,
            'acceso_enchufe' => $postData['acceso_enchufe'] ?? null,
            'dificultad_montaje' => $postData['dificultad_montaje'] ?? null,
            'tipo_consumidores' => $postData['tipo_consumidores'] ?? null,
            'restricciones' => $postData['restricciones'] ?? null,
            'requisitos_adicionales' => $postData['requisitos_adicionales'] ?? null,
            'presupuesto' => $postData['presupuesto'] ?? null,
            
            // Costos y estado (calculados por el servidor)
            'total_base' => $costoBase,
            'costo_adicional_ia' => $costoAdicionalIA,
            'justificacion_ia' => $justificacionIA,
            'total_estimado' => $costoBase + $costoAdicionalIA,
            'status' => 'Pendiente'
        ];

        // Usamos una transacción para asegurar la integridad de los datos
        $db = db_connect();
        $db->transStart();
        
        $cotizacionId = $cotizacionModel->insert($datosCotizacion, true);

        if (!$cotizacionId) {
            $db->transComplete();
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Fallo al insertar la cotización principal.']);
        }

        $serviciosSeleccionadosIds = $postData['servicios'] ?? [];
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
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'No se pudo guardar la cotización en la base de datos.']);
        }

        $nombreCliente = $postData['nombre_completo'] ?? 'un cliente';
        
        $title = "Nueva Cotización Recibida";
        $body = "De: $nombreCliente. Toca para ver los detalles.";
        $notificationPayload = [
            'action_url' => "/cotizaciones/{$cotizacionId}",
            'notification_type' => 'nueva_cotizacion',
            // El 'data' para el push debe ser un mapa de strings
            'push_data' => [
                'type' => 'nueva_cotizacion',
                'cotizacion_id' => (string)$cotizacionId
            ]
        ];

        $userModel = new UsuarioModel();
        $admins = $userModel->findAll(); 

        if (!empty($admins)) {
            $notificationModel = new NotificationModel();

            foreach ($admins as $admin) {
                $notificationModel->insert([
                    'user_id'           => $admin['id'], // El ID del admin
                    'title'             => $title,
                    'body'              => $body,
                    'is_read'           => 0, // Por defecto no leída
                    'action_url'        => $notificationPayload['action_url'],
                    'notification_type' => $notificationPayload['notification_type']
                ]);
            }

        }

        try {
            $firebase = new FirebaseService();
            $firebase->sendToTopic(
                'admins', // El topic de todos los administradores
                $title,
                $body,
                $notificationPayload['push_data']
            );
            log_message('info', 'Notificación Push enviada al topic "admins".');
        } catch (\Exception $e) {
            log_message('error', 'Fallo al enviar notificación Push al topic: ' . $e->getMessage());
            // No detenemos el proceso, solo lo registramos. La notificación ya está en la DB.
        }

        return $this->response->setJSON([
            'success' => true, 
            'message' => '¡Cotización enviada con éxito!', 
            'cotizacion_id' => $cotizacionId
        ])->setStatusCode(201);
    }

    public function fechasOcupadas()
    {
        $cotizacionModel = new \App\Models\CotizacionModel();
        
        // Buscamos solo la columna 'fecha_evento' de las cotizaciones 'Confirmado'
        $fechasDb = $cotizacionModel->select('fecha_evento')
                                    ->where('status', 'Confirmado')
                                    ->findAll();
        
        // `array_column` es una forma eficiente de obtener solo los valores de una columna
        // El resultado será un array simple como: ['2025-10-15', '2025-11-01', ...]
        $fechasOcupadas = array_column($fechasDb, 'fecha_evento');

        return $this->response->setJSON($fechasOcupadas);
    }
}