<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CateringBudget extends Controller
{
    protected $helpers = ['form']; 

    public function __construct()
    {
        
    }

    public function fechasOcupadas()
    {
        $model = new \App\Models\CotizacionModel();
        $fechas = $model->fechasOcupadas();

        return $this->response->setJSON(['fechas' => $fechas]);
    }

    public function estimate()
    {
        $apiKey = "AIzaSyCWI88ZVXHIpuYj2OsR_Hk35jbgKh6HdFs";

        if (empty($apiKey)) {
            log_message('error', 'Gemini API Key no está configurada o sigue siendo el placeholder.');
            return $this->response
                        ->setStatusCode(500)
                        ->setJSON(['error' => 'Error de configuración del servidor: API Key no disponible.']);
        }

        
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response
                        ->setStatusCode(400) 
                        ->setJSON(['error' => 'Entrada JSON inválida o vacía.']);
        }

        $model = new \App\Models\CotizacionModel();
        $fechaEvento = $data['eventDate'] ?? null;

        // Verifica si ya hay un evento para la misma fecha
        if ($fechaEvento && $model->fechaOcupada($fechaEvento)) {
            return $this->response
                        ->setStatusCode(409) // 409 Conflict
                        ->setJSON(['error' => 'Ya existe un evento programado para esta fecha. Por favor selecciona otra.']);
        }

       
        $system_instruction_text = "Eres un experto estimador de presupuestos de catering. Tu tarea es analizar los detalles de un evento y las preferencias de catering.
Comienza tu respuesta DIRECTAMENTE con el 'Presupuesto Estimado Total'. Esta debe ser una única línea que contenga solo el número del presupuesto en Pesos Mexicanos (MXN), sin ningún texto adicional, moneda o formato en esta línea.
Inmediatamente después de la línea del total, proporciona un salto de línea.
Luego, incluye una sección titulada exactamente así: '**Desglose Detallado de Costos:**'.
Bajo este título, detalla cada costo como un ítem de lista, comenzando cada ítem con un asterisco y un espacio ('* ').
Dentro de los ítems del desglose, utiliza negritas (encerrando el texto entre '**') para resaltar los conceptos clave y los subtotales.
Proporciona una estimación conservadora pero realista. Toda la respuesta debe estar en español. No incluyas introducciones ni conclusiones fuera de la estructura solicitada (total y desglose).";

       
        $user_prompt = "Por favor, genera un presupuesto basado en los siguientes detalles del evento:\n\n";
        $user_prompt .= "Detalles del Evento:\n";
        $user_prompt .= "Cómo supieron de nosotros: " . ($data['howDidYouHear'] ?? 'No especificado') . "\n";
        $user_prompt .= "Nombre Completo: " . ($data['fullName'] ?? 'No especificado') . "\n";
        $user_prompt .= "Número de Teléfono: " . ($data['phoneNumber'] ?? 'No especificado') . "\n";
        $user_prompt .= "Dirección del Evento: " . ($data['eventAddress'] ?? 'No especificada') . "\n";
        $user_prompt .= "Fecha del Evento: " . ($data['eventDate'] ?? 'No especificada') . "\n";
        $user_prompt .= "Hora de Inicio: " . ($data['eventStartTime'] ?? 'No especificada') . "\n";
        $user_prompt .= "Hora del Servicio de Comida: " . ($data['foodServiceTime'] ?? 'No especificada') . "\n";
        $user_prompt .= "Número de Invitados: " . ($data['numberOfGuests'] ?? 0) . "\n";
        $user_prompt .= "Preferencias de Cotización: " . (isset($data['quotationPreferences']) && is_array($data['quotationPreferences']) ? implode(', ', $data['quotationPreferences']) : 'No especificadas') . "\n";
        $user_prompt .= "Otros Detalles: " . ($data['otherQuotationDetails'] ?? 'Ninguno') . "\n";
        $user_prompt .= "Tipo de Evento: " . ($data['eventType'] ?? 'No especificado') . "\n";
        $user_prompt .= "Mesa y Mantel: " . ($data['tableAndMantel'] ?? 'No especificado') . "\n";
        $user_prompt .= "Personal de Servicio: " . ($data['servingStaff'] ?? 'No especificado') . "\n";
        $user_prompt .= "Acceso para Servicio de Café: " . ($data['coffeeServiceAccess'] ?? 'No especificado') . "\n";
        $user_prompt .= "Dificultad de Montaje: " . ($data['setupDifficulty'] ?? 'No especificada') . "\n";
        $user_prompt .= "Tipo de Consumidores: " . (isset($data['consumerType']) && is_array($data['consumerType']) ? implode(', ', $data['consumerType']) : 'No especificado') . "\n";
        $user_prompt .= "Restricciones Dietéticas: " . ($data['dietaryRestrictions'] ?? 'Ninguna') . "\n";
        $user_prompt .= "Requisitos Adicionales: " . ($data['additionalRequirements'] ?? 'Ninguno') . "\n";
        $user_prompt .= "Rango de Presupuesto (informativo para el cliente, no una restricción para ti): " . ($data['budgetRange'] ?? 'No especificado') . "\n\n";

        
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $system_instruction_text]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        ['text' => $user_prompt]
                    ]
                ]
            ],
            // 'generationConfig' => [
            //   'temperature' => 0.7,
            //   'maxOutputTokens' => 2048,
            // ]
        ];

        $client = \Config\Services::curlrequest([
            'timeout' => 30, // Tiempo de espera en segundos
        ]);

        $responseText = 'Error al generar presupuesto.'; // Mensaje de error por defecto
        $statusCode = 500; // Código de estado por defecto para errores internos

        try {
            $apiResponse = $client->post($url, [
                'json' => $payload, // Esto automáticamente establece Content-Type: application/json y codifica el payload
                'http_errors' => false, // Para manejar errores HTTP manualmente
            ]);

            $httpcode = $apiResponse->getStatusCode();
            $responseBody = $apiResponse->getBody(); // Obtiene el cuerpo como string

            if ($httpcode >= 200 && $httpcode < 300) {
                $json = json_decode($responseBody, true);
                if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                    $responseText = $json['candidates'][0]['content']['parts'][0]['text'];

                    // Extraer presupuesto total (primera línea del texto)
                    $lineas = explode("\n", $responseText);
                    $presupuestoTotal = floatval(str_replace(',', '', trim($lineas[0]))); // Asume que es numérico

                    $model = new \App\Models\CotizacionModel();
                    $model->insert([
                        'nombre_cliente' => $data['fullName'] ?? '',
                        'telefono' => $data['phoneNumber'] ?? '',
                        'direccion_evento' => $data['eventAddress'] ?? '',
                        'fecha_evento' => $data['eventDate'] ?? null,
                        'hora_inicio' => $data['eventStartTime'] ?? null,
                        'hora_servicio' => $data['foodServiceTime'] ?? null,
                        'tipo_evento' => $data['eventType'] ?? '',
                        'numero_invitados' => $data['numberOfGuests'] ?? 0,
                        'preferencias' => isset($data['quotationPreferences']) ? implode(', ', $data['quotationPreferences']) : '',
                        'detalles_otros' => $data['otherQuotationDetails'] ?? '',
                        'mesa_mantel' => $data['tableAndMantel'] ?? '',
                        'personal_servicio' => $data['servingStaff'] ?? '',
                        'acceso_cafe' => $data['coffeeServiceAccess'] ?? '',
                        'dificultad_montaje' => $data['setupDifficulty'] ?? '',
                        'tipo_consumidores' => isset($data['consumerType']) ? implode(', ', $data['consumerType']) : '',
                        'restricciones_dieteticas' => $data['dietaryRestrictions'] ?? '',
                        'requerimientos_adicionales' => $data['additionalRequirements'] ?? '',
                        'presupuesto_rango' => $data['budgetRange'] ?? '',
                        'como_conocio' => $data['howDidYouHear'] ?? '',
                        'presupuesto_total' => $presupuestoTotal,
                        'desglose' => $responseText,
                        'estado' => 'abierto'
                    ]);

                    $statusCode = 200;
                } elseif (isset($json['error']['message'])) {
                    $responseText = 'Error de API Gemini: ' . $json['error']['message'];
                    $statusCode = 400; // O 500 dependiendo del error de Gemini
                    log_message('error', "Gemini API Error: " . $json['error']['message']);
                } else {
                    $responseText = "Respuesta inesperada de la API. Código: {$httpcode}. Respuesta: " . $responseBody;
                    $statusCode = 500;
                    log_message('error', "Respuesta inesperada de Gemini API. Código: {$httpcode}. Respuesta: " . $responseBody);
                }
            } else {
                // Error HTTP (4xx, 5xx) de la API de Gemini
                $responseText = "Error HTTP {$httpcode} al contactar la API de Gemini. Detalles: " . $responseBody;
                $statusCode = $httpcode; // Usar el código de error de la API
                log_message('error', "Error HTTP {$httpcode} de Gemini API: " . $responseBody);
            }
        } catch (\Exception $e) {
            // Error en la conexión o cliente HTTP
            $responseText = 'Error de conexión: ' . $e->getMessage();
            $statusCode = 503; // Service Unavailable
            log_message('error', 'Excepción en CURLRequest: ' . $e->getMessage());
        }

        // Devolver respuesta JSON usando el objeto Response de CI4
        if ($statusCode === 200) {
            return $this->response->setJSON(['mensaje' => $responseText]);
        } else {
            return $this->response
                        ->setStatusCode($statusCode)
                        ->setJSON(['error' => $responseText]);
        }
    }
}