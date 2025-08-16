<?php 
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CotizacionModel;

class Cotizaciones extends BaseController
{
    public function index()
    {
        $cotizacionModel = new CotizacionModel();
        
        // Obtenemos todas las cotizaciones, ordenadas por la fecha del evento
        $data['cotizaciones'] = $cotizacionModel->orderBy('fecha_evento', 'ASC')->findAll();

        $data['titulo'] = 'Listado de Cotizaciones'; // Para el título de la página

        return view('admin/cotizaciones/listado_view', $data);
    }

    public function ver($id = null)
    {
        $cotizacionModel = new CotizacionModel();
        // Usamos nuestro modelo para encontrar la cotización por su ID.
        $data['cotizacion'] = $cotizacionModel->find($id);

        // También necesitamos los servicios que se seleccionaron para esta cotización.
        // Aquí es donde la tabla pivote entra en juego.
        $db = \Config\Database::connect();
        $builder = $db->table('cotizacion_servicios cs');
        $builder->select('s.nombre, s.precio_base, s.tipo_cobro');
        $builder->join('servicios s', 's.id = cs.servicio_id');
        $builder->where('cs.cotizacion_id', $id);
        $query = $builder->get();
        $data['servicios_seleccionados'] = $query->getResultArray();

        // Si no encontramos la cotización, mostramos un error 404.
        if (empty($data['cotizacion'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se pudo encontrar la cotización con ID: ' . $id);
        }

        $data['titulo'] = 'Detalle de Cotización #' . $id;

        return view('admin/cotizaciones/detalle_view', $data);
    }

    public function actualizarEstado()
    {
        // Solo aceptamos peticiones POST para seguridad
        // if ($this->request->getMethod() !== 'post') {
        //     return redirect()->to(site_url('admin/cotizaciones'));
        // }

        $cotizacionModel = new CotizacionModel();
        
        $id = $this->request->getPost('cotizacion_id');
        $nuevoEstado = $this->request->getPost('status');

        // Validamos que tengamos los datos necesarios
        if (empty($id) || empty($nuevoEstado)) {
            return redirect()->back()->with('error', 'Faltan datos para actualizar el estado.');
        }

        // Usamos el método update del modelo
        $cotizacionModel->update($id, [
            'status' => $nuevoEstado
        ]);

        // Redirigimos de vuelta a la página de detalle con un mensaje de éxito
        // with('mensaje', ...) crea un "flashdata", un mensaje que solo dura una petición.
        return redirect()->to(site_url('admin/cotizaciones/ver/' . $id))
                        ->with('mensaje', 'El estado de la cotización ha sido actualizado con éxito.');
    }

    public function eliminar()
    {
        // if ($this->request->getMethod() !== 'post') {
        //     return redirect()->to(site_url('admin/cotizaciones'));
        // }

        $id = $this->request->getPost('cotizacion_id');
        if (empty($id)) {
            return redirect()->back()->with('error', 'No se proporcionó un ID de cotización.');
        }

        $cotizacionModel = new CotizacionModel();
        // El modelo se encarga de todo. Gracias a "ON DELETE CASCADE" en la base de datos,
        // los registros en la tabla pivote `cotizacion_servicios` también se borrarán.
        $cotizacionModel->delete($id);

        return redirect()->to(site_url('admin/cotizaciones'))
                        ->with('mensaje', 'La cotización #' . $id . ' ha sido eliminada con éxito.');
    }

    public function editar($id = null)
    {
        $cotizacionModel = new CotizacionModel();
        $servicioModel = new \App\Models\ServicioModel();
        $cotizacionServiciosModel = new \App\Models\CotizacionServiciosModel();

        $data['cotizacion'] = $cotizacionModel->find($id);
        if (empty($data['cotizacion'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se pudo encontrar la cotización.');
        }

        // Pasamos todos los servicios para que el formulario se pueda construir
        $data['servicios'] = $servicioModel->findAll();

        $serviciosSeleccionados = $cotizacionServiciosModel->where('cotizacion_id', $id)->findAll();
        $data['servicios_seleccionados_ids'] = array_column($serviciosSeleccionados, 'servicio_id');

        $fechasDb = $cotizacionModel->select('fecha_evento')
                                    ->where('status', 'Confirmado')
                                    ->findAll();
        $fechasOcupadas = array_column($fechasDb, 'fecha_evento');
        $fechaActualDeLaCotizacion = $data['cotizacion']['fecha_evento'];
        $fechasFiltradas = array_filter($fechasOcupadas, function ($fecha) use ($fechaActualDeLaCotizacion) {
            return $fecha !== $fechaActualDeLaCotizacion;
        });
        $data['fechas_deshabilitadas_json'] = json_encode(array_values($fechasFiltradas));

        $data['titulo'] = 'Editando Cotización #' . $id;
        return view('admin/cotizaciones/editar_view', $data);
    }

    public function actualizar()
    {
        $id = $this->request->getPost('cotizacion_id');
        $postData = $this->request->getPost();

        if (empty($id)) {
            return redirect()->back()->with('error', 'ID de cotización no válido.');
        }

        $cantidadInvitados = (int)($postData['cantidad_invitados'] ?? 1);
        $serviciosSeleccionadosIds = $postData['servicios'] ?? [];
        
        // Recalculamos los litros basados en la regla de negocio
        $litrosAgua = ceil($cantidadInvitados / 6);
        
        $costoBase = 0;
        
        if (!empty($serviciosSeleccionadosIds)) {
            $servicioModel = new \App\Models\ServicioModel();
            $serviciosInfo = $servicioModel->whereIn('id', $serviciosSeleccionadosIds)->findAll();
            
            foreach ($serviciosInfo as $servicio) {
                // Omitir servicios que no cumplen el mínimo de personas
                if ($cantidadInvitados < $servicio['min_personas']) continue;

                switch ($servicio['tipo_cobro']) {
                    case 'por_persona':
                        $costoBase += $servicio['precio_base'] * $cantidadInvitados;
                        break;
                    case 'por_litro':
                        $costoBase += $servicio['precio_base'] * $litrosAgua;
                        break;
                    default: // 'fijo'
                        $costoBase += $servicio['precio_base'];
                        break;
                }
            }
        }

        $logisticsService = new \App\Libraries\LogisticsAIService();
        $prediction = $logisticsService->predict($postData);
        $costoAdicionalIA = $prediction['costo'];
        $justificacionIA = $prediction['justificacion'];

        $datosCotizacion = [
            // Datos del cliente y evento (_form_cliente_evento)
            'nombre_completo'    => $postData['nombre_completo'] ?? null,
            'whatsapp'           => $postData['whatsapp'] ?? null,
            'tipo_evento'        => $postData['tipo_evento'] ?? null,
            'nombre_empresa'     => $postData['nombre_empresa'] ?? null,
            'direccion_evento'   => $postData['direccion_evento'] ?? null,
            'fecha_evento'       => $postData['fecha_evento'] ?? null,
            'hora_evento'        => $postData['hora_evento'] ?? null,
            'horario_consumo'    => $postData['horario_consumo'] ?? null,
            
            // Datos de servicios (_form_servicios)
            'cantidad_invitados' => $cantidadInvitados,
            'servicios_otros'    => $postData['servicios_otros'] ?? null,

            // Detalles finales (_form_detalles_finales)
            'como_supiste'          => $postData['como_supiste'] ?? null,
            'como_supiste_otro'     => $postData['como_supiste_otro'] ?? null,
            'mesa_mantel'           => $postData['mesa_mantel'] ?? null,
            'mesa_mantel_otro'      => $postData['mesa_mantel_otro'] ?? null,
            'personal_servicio'     => $postData['personal_servicio'] ?? null,
            'acceso_enchufe'        => $postData['acceso_enchufe'] ?? null,
            'dificultad_montaje'    => $postData['dificultad_montaje'] ?? null,
            'tipo_consumidores'     => $postData['tipo_consumidores'] ?? null,
            'restricciones'         => $postData['restricciones'] ?? null,
            'requisitos_adicionales'=> $postData['requisitos_adicionales'] ?? null,
            'presupuesto'           => $postData['presupuesto'] ?? null,

            // Costos Recalculados
            'total_base'         => $costoBase,
            'costo_adicional_ia' => $costoAdicionalIA,
            'justificacion_ia'   => $justificacionIA,
            'total_estimado'     => $costoBase + $costoAdicionalIA,
        ];

        $cotizacionModel = new CotizacionModel();
        $cotizacionServiciosModel = new \App\Models\CotizacionServiciosModel();
        $db = \Config\Database::connect();

        $db->transStart();

        // 1. Actualizar la tabla principal `cotizaciones` con todos los nuevos datos
        $cotizacionModel->update($id, $datosCotizacion);

        // 2. Borrar las entradas antiguas de servicios para esta cotización
        $cotizacionServiciosModel->where('cotizacion_id', $id)->delete();

        // 3. Insertar las nuevas entradas de servicios seleccionados (si las hay)
        if (!empty($serviciosSeleccionadosIds)) {
            $nuevosServicios = [];
            foreach ($serviciosSeleccionadosIds as $servicioId) {
                $nuevosServicios[] = [
                    'cotizacion_id' => $id,
                    'servicio_id'   => $servicioId
                ];
            }
            $cotizacionServiciosModel->insertBatch($nuevosServicios);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Hubo un error al guardar los cambios.');
        }

        return redirect()->to(site_url('admin/cotizaciones/ver/' . $id))
                        ->with('mensaje', 'La cotización se ha actualizado correctamente.');
    }
}