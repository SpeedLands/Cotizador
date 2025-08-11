<?php

// probalemnete esto ya no exista en el futuro

namespace App\Controllers\Admin;

use App\Libraries\FirebaseService;

class NotificationController extends BaseController
{
    public function sendTestNotification()
    {
        // Crea una instancia de tu servicio
        $firebase = new FirebaseService();

        // Define los detalles de la notificación
        $title = '¡Hola desde el Servidor!';
        $message = 'Esta es una notificación de prueba enviada con PHP.';

        // 4. Obtén los tokens de los dispositivos de tu base de datos
        //    (Aquí los ponemos directamente para el ejemplo)
        $deviceTokens = [
            'token_del_dispositivo_1_obtenido_de_tu_app',
            'token_del_dispositivo_2_obtenido_de_tu_app'
            // ...puedes añadir más tokens aquí
        ];

        // 5. (Opcional) Define datos adicionales para que tu app los procese
        //    Por ejemplo, para abrir una pantalla específica.
        $customData = [
            'type' => 'new_message', // Un identificador para tu app
            'data' => [
                'message_id' => '12345',
                'sender' => 'Admin'
            ]
        ];

        // 6. Llama al método para enviar la notificación
        $response = $firebase->sendNotification(
            $title,
            $message,
            $deviceTokens,
            $customData
        );

        // 7. (Opcional) Muestra la respuesta de Firebase para depurar
        echo "Respuesta de Firebase: <pre>";
        print_r($response);
        echo "</pre>";
    }
}