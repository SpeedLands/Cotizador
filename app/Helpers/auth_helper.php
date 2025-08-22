<?php

if (!function_exists('auth')) {
    /**
     * Devuelve una instancia del servicio de autenticación.
     * @return \App\Services\AuthService
     */
    function auth()
    {
        return service('auth');
    }
}