<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use \Firebase\JWT\JWT; // <-- Importa la librería JWT

class AuthController extends BaseController
{
    // MÉTODO DE LOGIN PARA LA API
    public function login()
    {
        // Usamos getJSON() para leer el cuerpo de la petición JSON
        $json = $this->request->getJSON();
        
        // Verificamos si los datos esperados existen
        if (!isset($json->email) || !isset($json->password)) {
             return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Faltan los campos email o password'
            ])->setStatusCode(400); // 400 Bad Request
        }

        $email = $json->email;
        $password = $json->password;

        $model = new UsuarioModel();
        $user = $model->where('email', $email)->first();

        // Si el usuario no existe o la contraseña es incorrecta
        if (is_null($user) || !password_verify($password, $user['password_hash'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Credenciales inválidas'
            ])->setStatusCode(401); // 401 Unauthorized
        }

        // --- Generar el Token JWT ---
        $key = getenv('jwt.secretkey'); // Obtiene la clave desde .env
        $iat = time(); // Tiempo en que se emitió el token
        $exp = $iat + 3600 * 24; // El token expira en 24 horas

        $payload = [
            'iss' => 'mapolato-api', // Quién emitió el token
            'aud' => 'mapolato-app', // Para quién es el token
            'iat' => $iat,
            'exp' => $exp,
            'user_id' => $user['id'], // Datos que queremos incluir
            'email' => $user['email'],
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        // --- Respuesta exitosa con el token y datos del usuario ---
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['nombre_usuario'], // Asegúrate que este campo exista en tu DB
                'email' => $user['email'],
            ]
        ])->setStatusCode(200);
    }

    // MÉTODO DE REGISTRO PARA LA API
    public function register()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|valid_email|is_unique[usuarios.email]',
            'password' => 'required|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Datos de registro inválidos',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400); // 400 Bad Request
        }

        $model = new UsuarioModel();
        $data = [
            'nombre_usuario' => $this->request->getVar('name'),
            'email' => $this->request->getVar('email'),
            'password_hash' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT)
        ];

        if ($model->save($data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Usuario registrado correctamente'
            ])->setStatusCode(201); // 201 Created
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No se pudo registrar al usuario'
        ])->setStatusCode(500); // Internal Server Error
    }
}