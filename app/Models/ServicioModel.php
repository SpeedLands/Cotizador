<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'precio_base', 'tipo_cobro', 'min_personas', 'imagen_url'];

    public function getServicios()
    {
        return $this->findAll();
    }
}