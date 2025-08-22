<?php 
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CotizacionModel;
use App\Traits\CotizacionLogicTrait;

class Cotizaciones extends BaseController
{
    use CotizacionLogicTrait;

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

        $datosCotizacion = $this->_prepararDatosCotizacion($postData);

        if (isset($postData['status'])) {
            $datosCotizacion['status'] = $postData['status'];
        }

        $cotizacionModel = new CotizacionModel();
        $cotizacionServiciosModel = new \App\Models\CotizacionServiciosModel();
        $db = \Config\Database::connect();

        $db->transStart();

        $cotizacionModel->update($id, $datosCotizacion);

        $cantidadInvitados = (int)($postData['cantidad_invitados'] ?? 1);
        $serviciosSeleccionadosIds = $postData['servicios'] ?? [];

        $serviciosSeleccionadosIds = $postData['servicios'] ?? [];
        $cotizacionServiciosModel->where('cotizacion_id', $id)->delete();

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