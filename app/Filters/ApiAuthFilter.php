<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $key = getenv('jwt.secretkey');
        $header = $request->getHeaderLine('Authorization');
        $token = null;

        // 1. Extraer el token del encabezado
        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        // 2. Si no hay token, rechazar el acceso
        if (is_null($token)) {
            return Services::response()
                ->setJSON(['status' => 'error', 'message' => 'Token de autenticación no proporcionado.'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // 3. Validar el token
        try {
            // JWT::decode() verificará la firma y la fecha de expiración.
            // Si algo falla, lanzará una excepción.
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // --- ¡AQUÍ ESTÁ LA MAGIA! ---
            // Si el token es válido, obtenemos el ID del usuario del payload
            // y lo hacemos disponible para el resto de la aplicación.
            // Usaremos un servicio compartido para esto.
            $authService = service('auth');
            $authService->setUserId($decoded->user_id);

        } catch (\Exception $e) {
            // Si el token es inválido (expirado, firma incorrecta, etc.)
            return Services::response()
                ->setJSON(['status' => 'error', 'message' => 'Token inválido o expirado.'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No necesitamos hacer nada después de que el controlador se ejecute.
    }
}