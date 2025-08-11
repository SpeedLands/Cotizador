<?php

namespace App\Controllers;

use App\Models\ServicioModel; 
use App\Libraries\FirebaseService;

// este apartado esta pasando por una restructuracion completa

class Cotizador extends BaseController
{
    public function index()
    {
        try {
            $firebase = new FirebaseService();
            
            $titulo = "Prueba de Notificación";
            $mensaje = "¡El servicio de Firebase funciona correctamente!";
            
            $tokensDePrueba = ['dKSOFl4gSae8H7MGWb8E_X:APA91bFjU7zPO0Rjxhkd1DQxAVwjxPTz7mbSzqZLJFP_QXfY3W5-Dav8BTDdUaKzVthFcXe9ZiOOu2herw4BnzArzLCffgv5NM1XurJp1x2sV5TEG3s4GDA'];

            if (!empty($tokensDePrueba[0])) {
                $firebase->sendNotification($titulo, $mensaje, $tokensDePrueba);
                log_message('info', 'Notificación de prueba enviada.');
            }

        } catch (\Exception $e) {
            // Si la configuración de FirebaseService falla (ej: no encuentra el archivo JSON),
            // se registrará aquí. Revisa writable/logs/
            log_message('error', 'Error al inicializar FirebaseService: ' . $e->getMessage());
        }
        $servicioModel = new ServicioModel();
        $data['servicios'] = $servicioModel->findAll();
        return view('public/cotizador_view', $data);
    }

    public function calcular()
    {
        if ($this->request->isAJAX()) {
            $postData = $this->request->getPost();
            $cantidadInvitados = (int)($postData['cantidad_invitados'] ?? 1);
            $serviciosSeleccionadosIds = $postData['servicios'] ?? [];
            $litrosAgua = (int)($postData['litros_agua'] ?? 0);

            $subtotal = 0;
            $itemsCalculados = [];

            if (!empty($serviciosSeleccionadosIds)) {
                $servicioModel = new ServicioModel();
                $serviciosInfo = $servicioModel->whereIn('id', $serviciosSeleccionadosIds)->findAll();

                foreach ($serviciosInfo as $servicio) {
                    $costoItem = 0;
                    
                    // Doble validación: no cotizar si no cumple el mínimo de personas
                    if ($cantidadInvitados < $servicio['min_personas']) {
                        continue; 
                    }

                    if ($servicio['tipo_cobro'] == 'por_persona') {
                        $costoItem = $servicio['precio_base'] * $cantidadInvitados;
                    } 
                    elseif ($servicio['tipo_cobro'] == 'por_litro') {
                        $costoItem = $servicio['precio_base'] * $litrosAgua;
                    } 
                    else { 
                        $costoItem = $servicio['precio_base'];
                    }

                    $subtotal += $costoItem;
                    $itemsCalculados[] = [
                        'nombre' => $servicio['nombre'],
                        'costo' => number_format($costoItem, 2)
                    ];
                }
            }
            
            $total = $subtotal;

            $respuesta = [
                'subtotal' => number_format($subtotal, 2),
                'total' => number_format($total, 2),
                'items' => $itemsCalculados
            ];
            
            return $this->response->setJSON($respuesta);
        }
    }

    public function guardar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        // --- 1. CÁLCULO DEL COSTO BASE (Tu lógica fiable) ---
        $postData = $this->request->getPost();
        $cantidadInvitados = (int)($postData['cantidad_invitados'] ?? 1);
        $serviciosSeleccionadosIds = $postData['servicios'] ?? [];
        $litrosAgua = (int)($postData['litros_agua'] ?? 0);
        $costoBase = 0;
        
        if (!empty($serviciosSeleccionadosIds)) {
            $servicioModel = new \App\Models\ServicioModel();
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

        // --- 2. RECOPILAR DATOS DE PREGUNTAS ABIERTAS PARA IA ---
        $notasLogistica = "Dificultad de montaje: " . ($postData['dificultad_montaje'] ?? 'No especificada') . "\n";
        $notasLogistica .= "Requisitos adicionales: " . ($postData['requisitos_adicionales'] ?? 'Ninguno') . "\n";
        $notasLogistica .= "Otros servicios no listados: " . ($postData['servicios_otros'] ?? 'Ninguno') . "\n";
        $notasLogistica .= "Mesa y mantel (detalle 'otro'): " . ($postData['mesa_mantel_otro'] ?? 'No aplica') . "\n";
        $notasLogistica .= "Restricciones alimenticias: " . ($postData['restricciones'] ?? 'Ninguna') . "\n";


        // esta esta por ser cambiado por la libreria de PHP-ML ignorar
        // --- 3. LLAMADA ENFOCADA A GEMINI ---
        $apiKey = "AIzaSyCWI88ZVXHIpuYj2OsR_Hk35jbgKh6HdFs"; 
        $costoAdicionalIA = 0;
        $justificacionIA = 'Sin costos adicionales por logística.';

        // Solo llamar a la IA si hay notas relevantes
        if (trim(str_replace('No especificada', '', str_replace('Ninguno', '', str_replace('No aplica', '', $notasLogistica))))) {
            
            $system_instruction = "Eres un analista de costos de logística para una empresa de catering. Te daré un costo base de un evento y notas del cliente. Tu ÚNICA tarea es estimar el costo ADICIONAL en Pesos Mexicanos (MXN) basado en las complejidades logísticas descritas. Tu respuesta DEBE ser un objeto JSON con dos claves: 'costo_adicional' (un número, sin comas ni símbolos) y 'justificacion' (un texto breve explicando el porqué del costo). Si no hay complejidad, devuelve 0 en 'costo_adicional'. Ejemplo de respuesta: {\"costo_adicional\": 500, \"justificacion\": \"Costo por montaje en segundo piso y necesidad de extensión eléctrica.\"}";
            
            $user_prompt = "Costo base del evento: $" . number_format($costoBase, 2) . " MXN para " . $cantidadInvitados . " invitados.\n";
            $user_prompt .= "Analiza las siguientes notas logísticas y de requisitos:\n" . $notasLogistica;

            $payload = [
                'contents' => [['parts' => [['text' => $user_prompt]]]],
                'system_instruction' => ['parts' => [['text' => $system_instruction]]],
                'generationConfig' => ['response_mime_type' => 'application/json'] // ¡Pedimos JSON directamente!
            ];
            
            try {
                $client = \Config\Services::curlrequest(['timeout' => 20]);
                $apiResponse = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey, [
                    'json' => $payload,
                    'http_errors' => false
                ]);

                if ($apiResponse->getStatusCode() == 200) {
                    $responseBody = json_decode($apiResponse->getBody(), true);
                    $iaResponseText = $responseBody['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                    $iaData = json_decode($iaResponseText, true);

                    $costoAdicionalIA = (float)($iaData['costo_adicional'] ?? 0);
                    $justificacionIA = $iaData['justificacion'] ?? 'No se pudo obtener justificación de la IA.';
                }
            } catch (\Exception $e) {
                // No detenemos el proceso, solo registramos el error de la IA
                log_message('error', 'Error en la llamada a Gemini: ' . $e->getMessage());
                $justificacionIA = 'Error de conexión con el servicio de IA. Se requiere revisión manual.';
            }
        }
        
        // --- 4. GUARDAR TODO EN LA BASE DE DATOS ---
        $cotizacionModel = new \App\Models\CotizacionModel();
        $cotizacionServiciosModel = new \App\Models\CotizacionServiciosModel();

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
        $db = \Config\Database::connect();
        $db->transStart();
        
        $cotizacionId = $cotizacionModel->insert($datosCotizacion, true);

        if ($cotizacionId && !empty($serviciosSeleccionadosIds)) {
            foreach ($serviciosSeleccionadosIds as $servicioId) {
                $cotizacionServiciosModel->insert([
                    'cotizacion_id' => $cotizacionId,
                    'servicio_id'   => $servicioId
                ]);
            }
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo guardar la cotización. Por favor, intenta de nuevo.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => '¡Cotización enviada con éxito! Nos pondremos en contacto contigo a la brevedad.']);
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