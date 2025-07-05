<?php

namespace App\Models;

use CodeIgniter\Model;

class CotizacionModel extends Model
{
    protected $table = 'cotizaciones';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nombre_cliente',
        'telefono',
        'direccion_evento',
        'fecha_evento',
        'hora_inicio',
        'hora_servicio',
        'tipo_evento',
        'numero_invitados',
        'preferencias',
        'detalles_otros',
        'mesa_mantel',
        'personal_servicio',
        'acceso_cafe',
        'dificultad_montaje',
        'tipo_consumidores',
        'restricciones_dieteticas',
        'requerimientos_adicionales',
        'presupuesto_rango',
        'como_conocio',
        'presupuesto_total',
        'desglose',
        'fecha_creacion',
        'estado'
    ];

    protected $validationRules = [
        'estado' => 'required|in_list[abierto,confirmado,pagado,cancelado]',
        'fecha_evento' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'estado' => [
            'in_list' => 'El estado debe ser uno de: abierto, confirmado, pagado o cancelado.'
        ]
    ];

    protected $beforeInsert = ['sanitizeData'];
    protected $beforeUpdate = ['sanitizeData'];

    protected function sanitizeData(array $data)
    {
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $data['data'][$key] = is_string($value) ? strip_tags(trim($value)) : $value;
            }
        }
        return $data;
    }

    /**
     * Verifica si ya existe una cotización agendada en la fecha dada.
     *
     * @param string $fecha (formato: YYYY-MM-DD)
     * @return bool
     */
    public function fechaOcupada(string $fecha): bool
    {
        return $this->where('fecha_evento', $fecha)
                    ->whereIn('estado', ['abierto', 'confirmado', 'pagado'])
                    ->countAllResults() > 0;
    }

    /**
     * Obtiene una lista de fechas que ya están ocupadas por una cotización activa.
     *
     * @return array Lista de fechas en formato YYYY-MM-DD
     */
    public function fechasOcupadas(): array
    {
        $resultados = $this->select('fecha_evento')
            ->whereIn('estado', ['abierto', 'confirmado', 'pagado'])
            ->groupBy('fecha_evento')
            ->findAll();

        return array_column($resultados, 'fecha_evento');
    }
}
