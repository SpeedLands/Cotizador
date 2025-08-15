<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ServicioModel;
use App\Models\UsuarioModel;
use App\Models\CotizacionModel;
use App\Models\NotificationModel;
use App\Models\CotizacionServiciosModel;
use App\Libraries\FirebaseService;
use App\Libraries\LogisticsAIService;
use Config\Services;

class CotizacionesController extends BaseController
{
    /**
     * Lista todas las cotizaciones.
     * Ordena por fecha de creación descendente (las más nuevas primero).
     */
    public function index()
    {
        $cotizacionModel = new CotizacionModel();
        
        // Usamos findAll() para obtener todas las cotizaciones.
        // orderBy() las ordena para que las más recientes aparezcan primero.
        $cotizaciones = $cotizacionModel->orderBy('created_at', 'DESC')->findAll();

        if (empty($cotizaciones)) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [] // Devuelve un array vacío si no hay cotizaciones
            ])->setStatusCode(200);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $cotizaciones
        ])->setStatusCode(200);
    }


    public function fechasOcupadas()
    {
        $cotizacionModel = new CotizacionModel();
        $fechasDb = $cotizacionModel->select('fecha_evento')
                                    ->where('status', 'Confirmado')
                                    ->findAll();
        $fechasOcupadas = array_column($fechasDb, 'fecha_evento');

        return $this->response->setJSON($fechasOcupadas);
    }

    public function guardar()
    {
        $json = $this->request->getJSON(true);

        if (empty($json)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió un payload JSON válido.'])->setStatusCode(400);
        }

        $validation = Services::validation();
        $validation->setRules([
            'nombre_completo'     => 'required|min_length[3]',
            'whatsapp'            => 'required|min_length[10]',
            'fecha_evento'        => 'required|valid_date',
            'hora_evento'         => 'required',
            'cantidad_invitados'  => 'required|is_natural_no_zero',
            'servicios'           => 'required|is_array'
        ]);

        if (!$validation->run($json)) {
            return $this->response
                        ->setJSON(['status' => 'error', 'message' => 'Datos inválidos.', 'errors' => $validation->getErrors()])
                        ->setStatusCode(400);
        }

        $cantidadInvitados = (int)($json['cantidad_invitados'] ?? 1);
        $serviciosSeleccionadosIds = $json['servicios'] ?? [];
        $litrosAgua = (int)($json['litros_agua'] ?? 0);
        $costoBase = 0;
        
        if (!empty($serviciosSeleccionadosIds)) {
            $servicioModel = new ServicioModel();
            $serviciosInfo = $servicioModel->whereIn('id', $serviciosSeleccionadosIds)->findAll();
            foreach ($serviciosInfo as $servicio) {
                if ($cantidadInvitados < $servicio['min_personas']) continue;
                if ($servicio['tipo_cobro'] == 'por_persona') {
                    $costoBase += (float)$servicio['precio_base'] * $cantidadInvitados;
                } elseif ($servicio['tipo_cobro'] == 'por_litro') {
                    $costoBase += (float)$servicio['precio_base'] * $litrosAgua;
                } else {
                    $costoBase += (float)$servicio['precio_base'];
                }
            }
        }

        $logisticsService = new LogisticsAIService();

        $prediction = $logisticsService->predict($json);
        $costoAdicionalIA = $prediction['costo'];
        $justificacionIA = $prediction['justificacion'];
        
        
        $cotizacionModel = new CotizacionModel();
        $cotizacionServiciosModel = new CotizacionServiciosModel();

        $datosCotizacion = [
            'nombre_completo' => $json['nombre_completo'] ?? null,
            'whatsapp' => $json['whatsapp'] ?? null,
            'como_supiste' => $json['como_supiste'] ?? null,
            'como_supiste_otro' => $json['como_supiste_otro'] ?? null,
            'tipo_evento' => $json['tipo_evento'] ?? null,
            'nombre_empresa' => $json['nombre_empresa'] ?? null,
            'direccion_evento' => $json['direccion_evento'] ?? null,
            'fecha_evento' => $json['fecha_evento'] ?? null,
            'hora_evento' => $json['hora_evento'] ?? null,
            'horario_consumo' => $json['horario_consumo'] ?? null,
            'cantidad_invitados' => $cantidadInvitados,
            'servicios_otros' => $json['servicios_otros'] ?? null,
            'mesa_mantel' => $json['mesa_mantel'] ?? null,
            'mesa_mantel_otro' => $json['mesa_mantel_otro'] ?? null,
            'personal_servicio' => $json['personal_servicio'] ?? null,
            'acceso_enchufe' => $json['acceso_enchufe'] ?? null,
            'dificultad_montaje' => $json['dificultad_montaje'] ?? null,
            'tipo_consumidores' => $json['tipo_consumidores'] ?? null,
            'restricciones' => $json['restricciones'] ?? null,
            'requisitos_adicionales' => $json['requisitos_adicionales'] ?? null,
            'presupuesto' => $json['presupuesto'] ?? null,
            'total_base' => $costoBase,
            'costo_adicional_ia' => $costoAdicionalIA,
            'justificacion_ia' => $justificacionIA,
            'total_estimado' => $costoBase + $costoAdicionalIA,
            'status' => 'Pendiente'
        ];

        $db = db_connect();
        $db->transStart();
        
        $cotizacionId = $cotizacionModel->insert($datosCotizacion, true);

        if (!$cotizacionId) {
            $db->transComplete(); // Finalizamos la transacción fallida
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Fallo al insertar la cotización principal.']);
        }

        $serviciosSeleccionadosIds = $json['servicios'] ?? [];
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

        $nombreCliente = $json['nombre_completo'] ?? 'un cliente';
        
        $title = "Nueva Cotización Recibida";
        $body = "De: $nombreCliente. Toca para ver los detalles.";
        
        // Datos que se guardarán en la DB y se enviarán en el push
        $notificationPayload = [
            'action_url' => "/cotizaciones/{$cotizacionId}",
            'notification_type' => 'nueva_cotizacion',
            // El 'data' para el push debe ser un mapa de strings
            'push_data' => [
                'type' => 'nueva_cotizacion',
                'cotizacion_id' => (string)$cotizacionId
            ]
        ];

        // --- 3. GUARDADO DE NOTIFICACIONES EN LA BASE DE DATOS ---
        // Obtenemos todos los usuarios (que son los admins)
        $userModel = new UsuarioModel();
        $admins = $userModel->findAll(); 

        if (!empty($admins)) {
            $notificationModel = new NotificationModel();
            
            // Creamos un registro de notificación para CADA administrador
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
        
        // --- 4. ENVÍO DE LA NOTIFICACIÓN PUSH AL TOPIC ---
        // Esto se hace una sola vez, después de guardar todo en la DB.
        try {
            $firebase = new FirebaseService();
            $firebase->sendToTopic(
                'admins', // El topic de todos los administradores
                $title,
                $body,
                $notificationPayload['push_data']
            );
        } catch (\Exception $e) {
            log_message('error', 'Fallo al enviar notificación Push al topic: ' . $e->getMessage());
            // No detenemos el proceso, solo lo registramos. La notificación ya está en la DB.
        }
        

        return $this->response->setJSON([
            'status' => 'success', 
            'message' => '¡Cotización enviada con éxito!', 
            'cotizacion_id' => $cotizacionId
        ])->setStatusCode(201);
    }
}