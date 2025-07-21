<?php

namespace App\Models;

use CodeIgniter\Model;

class CotizacionServiciosModel extends Model
{
    protected $table = 'cotizacion_servicios'; // Nombre de tu tabla pivote
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'cotizacion_id',
        'servicio_id'
    ];
}