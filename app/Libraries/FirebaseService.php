<?php

namespace App\Libraries;

use Google\Client;

class FirebaseService
{
    private $token;

    public function __construct()
    {
        // Initialize the Google Client for Firebase
        $client = new Client();
        $client->setAuthConfig(WRITEPATH . 'uploads/mapolato-27709-firebase-adminsdk-fbsvc-b272d86b66.json');
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $this->token = $client->fetchAccessTokenWithAssertion();
    }

    // --- MÉTODO NUEVO PARA ENVIAR A TOPICS ---

    /**
     * Envía una notificación a un Topic específico.
     *
     * @param string $topic El nombre del topic (ej: 'admins')
     * @param string $title El título de la notificación
     * @param string $body El cuerpo del mensaje
     * @param array $data Datos adicionales para que la app los procese
     * @return array La respuesta de Firebase decodificada
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        // 1. Construimos el payload, usando 'topic' en lugar de 'token'.
        $messageData = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'android' => [
                    'notification' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                    ]
                ],
                // 2. Simplificamos la estructura de 'data'. FCM espera un mapa plano de strings.
                'data' => $data
            ]
        ];

        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://fcm.googleapis.com/v1/projects/mapolato-27709/messages:send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($messageData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token['access_token']
            ]
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            log_message('error', 'FCM cURL Error: ' . $error);
            return ['error' => $error];
        }

        log_message('info', 'FCM Response for topic ' . $topic . ': ' . $response);
        return json_decode($response, true);
    }

    // Esta funcion de Tokens es mas para web que para moviles por eso se cambio al uso de topics por que hace que los prcesos sean mas rapidos

    /**
     * Send Notification to Multiple Devices via Firebase Cloud Messaging (FCM)
     *
     * @param string $title Title of the notification
     * @param string $message Body of the notification
     * @param array $tokens List of Firebase device tokens to send notification to
     * @param array $data Array containing 'type' and 'data' fields
     * @param string|null $imageUrl URL of the image to include in the notification (optional)
     *
     * @return mixed Response from Firebase or error message
     * 
     * @example
     * $firebase = new FirebaseService();
     * $firebase->sendNotification(
     *     'Test Title',
     *     'Test Message',
     *     ['fqOzNBB...IdvA'],  // Firebase device token
     *     [
     *         'type' => 'test',
     *         'data' => ['key' => 'value']
     *     ]
     * );
     */
    public function sendNotification($title, $message, array $tokens, $data = [], $imageUrl = null)
    {
        $curl = curl_init();
        $responses = [];

        // BUCLE ACTIVADO para enviar a múltiples tokens
        foreach ($tokens as $token) {
            $messageData = [
                'message' => [
                    'token' => $token, // El token del dispositivo actual en el bucle
                    'notification' => [
                        'title' => $title,
                        'body' => $message
                    ],
                    'android' => [
                        'notification' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                        ]
                    ],
                    'data' => [
                        'type' => strval($data['type'] ?? ''),
                        'data' => json_encode($data['data'] ?? [])
                    ]
                ]
            ];

            if ($imageUrl) {
                $messageData['message']['notification']['image'] = $imageUrl;
            }

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://fcm.googleapis.com/v1/projects/mapolato-27709/messages:send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($messageData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token['access_token']
                ]
            ]);

            $response = curl_exec($curl);
            $responses[] = json_decode($response, true); // Guardamos la respuesta decodificada

            log_message('debug', 'FCM Request Payload for token ' . $token . ': ' . json_encode($messageData));
            log_message('info', 'FCM Response for token ' . $token . ': ' . $response);

            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                log_message('error', 'FCM cURL Error: ' . $error_msg);
                $responses[] = ['error' => $error_msg]; // Guardar el error también
            }
        }

        curl_close($curl);
        return $responses; // Devuelve un array con todas las respuestas
    }
}