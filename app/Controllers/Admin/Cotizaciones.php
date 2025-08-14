<?php 
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CotizacionModel;

class Cotizaciones extends BaseController
{
    public function index()
    {
        $cotizacionModel = new CotizacionModel();
        
        // Obtenemos todas las cotizaciones, ordenadas por la más reciente primero
        $data['cotizaciones'] = $cotizacionModel->orderBy('fecha_creacion', 'DESC')->findAll();

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

        $data['cotizacion'] = $cotizacionModel->find($id);
        if (empty($data['cotizacion'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se pudo encontrar la cotización.');
        }

        // Pasamos todos los servicios para que el formulario se pueda construir
        $data['servicios'] = $servicioModel->findAll();
        
        // También pasamos los IDs de los servicios que ya están seleccionados
        $db = \Config\Database::connect();
        $builder = $db->table('cotizacion_servicios');
        $builder->select('servicio_id');
        $builder->where('cotizacion_id', $id);
        $query = $builder->get()->getResultArray();
        // `array_column` es una forma útil de obtener solo los valores de una columna
        $data['servicios_seleccionados_ids'] = array_column($query, 'servicio_id');

        $data['titulo'] = 'Editando Cotización #' . $id;
        return view('admin/cotizaciones/editar_view', $data);
    }

    public function actualizar()
    {
        // if ($this->request->getMethod() !== 'post') {
        //     return redirect()->to(site_url('admin/cotizaciones'));
        // }

        $id = $this->request->getPost('cotizacion_id');
        $postData = $this->request->getPost();

        if (empty($id)) {
            return redirect()->back()->with('error', 'ID de cotización no válido.');
        }

        // Preparamos los datos para la tabla principal `cotizaciones`
        // (Omitimos los datos de costos, ya que esos no deberían editarse manualmente aquí)
        $datosCotizacion = [
            'nombre_completo' => $postData['nombre_completo'],
            'whatsapp' => $postData['whatsapp'],
            // ... (campos del formulario que se puedan actualizar)
        ];

        // Preparamos los datos de los servicios seleccionados
        $serviciosSeleccionadosIds = $postData['servicios'] ?? [];

        $cotizacionModel = new CotizacionModel();
        $cotizacionServiciosModel = new \App\Models\CotizacionServiciosModel();
        $db = \Config\Database::connect();

        // Usamos una transacción para garantizar que todo se ejecute correctamente o nada lo haga.
        $db->transStart();

        // 1. Actualizar la tabla principal de cotizaciones
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