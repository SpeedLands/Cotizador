<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * NotificationModel
 * 
 * Este modelo se conecta a la tabla `notifications` y nos permite
 * interactuar con ella (leer, insertar, actualizar, borrar notificaciones).
 */
class NotificationModel extends Model
{
    /**
     * El nombre de la tabla en tu base de datos.
     * @var string
     */
    protected $table = 'notifications';

    /**
     * La clave primaria de la tabla.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * El tipo de dato que devolverán las consultas (array, object, etc.).
     * Usamos 'array' para ser consistentes con el resto de la API.
     * @var string
     */
    protected $returnType = 'array';

    /**
     * Esta es la configuración más importante para la seguridad.
     * Lista TODOS los campos que se pueden escribir desde la aplicación.
     * Si un campo no está aquí, no se podrá insertar ni actualizar.
     * Basado en la imagen de tu tabla, estos son los campos correctos.
     * @var array
     */
    protected $allowedFields = [
        'user_id',
        'title',
        'body',
        'is_read',
        'action_url',
        'notification_type'
    ];

    /**
     * Especifica si el modelo debe manejar automáticamente las columnas
     * `created_at` y `updated_at`.
     * 
     * Lo ponemos en `false` porque, según tu imagen, la base de datos ya se encarga
     * de poner el valor por defecto en `created_at` (DEFAULT current_timestamp()).
     * @var bool
     */
    protected $useTimestamps = false;
}