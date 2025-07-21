<?php

namespace App\Controllers;

use App\Models\UsuarioModel; // Crearemos este modelo en un momento

class AuthController extends BaseController
{
    public function login()
    {
        // Si el usuario ya está logueado, lo redirigimos al dashboard de admin
        if (session()->get('isLoggedIn')) {
            return redirect()->to('admin');
        }
        return view('auth/login_view');
    }

    public function attemptLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $model = new UsuarioModel();
        $user = $model->where('email', $email)->first();

        // Verificamos si el usuario existe y si la contraseña es correcta
        if ($user && password_verify($password, $user['password_hash'])) {
            // La contraseña es correcta, creamos la sesión
            $sessionData = [
                'user_id'       => $user['id'],
                'nombre_usuario'=> $user['nombre_usuario'],
                'email'         => $user['email'],
                'isLoggedIn'    => true,
            ];
            session()->set($sessionData);
            
            // Redirigimos al panel de administración
            return redirect()->to('admin');
        } else {
            // Credenciales incorrectas, volvemos al login con un mensaje de error
            return redirect()->back()->with('error', 'Email o contraseña incorrectos.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}