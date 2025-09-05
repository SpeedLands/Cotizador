<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre_usuario', 
        'email', 
        'password_hash', 
        'refresh_token',
        'refresh_token_expires_at' 
    ];
}