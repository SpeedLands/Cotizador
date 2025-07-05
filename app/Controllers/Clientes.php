<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Clientes extends BaseController
{
    public function index()
    {
        // Helper de URL para base_url() y site_url() en las vistas
        helper('url');

        $data = [
            'page_title' => 'Solicitud de Cotización Eventos Mapolato'
        ];
        return view('cotizacion/formulario', $data); 
    }
}
