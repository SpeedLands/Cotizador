<?php

namespace App\Controllers;

use App\Models\ServicioModel; 

class Cotizador extends BaseController
{
    public function index()
    {
        $servicioModel = new ServicioModel();
        $data['servicios'] = $servicioModel->findAll();
        return view('cotizador_simple', $data);
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
}