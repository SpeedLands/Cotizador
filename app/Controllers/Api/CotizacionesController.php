<?php

namespace App\Controllers\Api;

use App\Traits\CotizacionLogicTrait;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class CotizacionesController extends ResourceController
{
    use CotizacionLogicTrait;

    protected $modelName = 'App\Models\CotizacionModel';
    protected $format    = 'json';

    /**
     * Lista TODAS las cotizaciones.
     * ACCESO: Solo para administradores autenticados (protegido por el filtro 'api-auth' en las rutas).
     */
    public function index()
    {
        $cotizaciones = $this->model->orderBy('fecha_evento', 'ASC')->findAll();
        return $this->respond($cotizaciones);
    }

    /**
     * Muestra una cotización específica.
     * ACCESO: Admins (con JWT) o Invitados (con Guest Token válido).
     */
    public function show($id = null)
    {
        $cotizacion = $this->model->find($id);
        if (!$cotizacion) {
            return $this->failNotFound('No se encontró la cotización con ID: ' . $id);
        }

        // --- LÓGICA DE AUTORIZACIÓN ---
        $isAdmin = auth()->id(); // Asumimos que tu helper de auth devuelve el ID si el JWT es válido.

        if (!$isAdmin) {
            // RUTA DEL INVITADO: No hay JWT, así que debe proporcionar un Guest Token.
            if (empty($cotizacion['guest_token'])) {
                return $this->failUnauthorized('Se requiere autenticación para acceder a este recurso.');
            }

            $tokenEnviado = $this->request->getHeaderLine('X-Guest-Token');
            if ($tokenEnviado !== $cotizacion['guest_token']) {
                return $this->failForbidden('Acceso no autorizado a esta cotización.');
            }
        }
        // Si es Admin, el filtro 'api-auth' ya le dio acceso, así que puede continuar.

        // --- LÓGICA DE LA RESPUESTA (si la autorización pasó) ---
        $db = db_connect();
        $builder = $db->table('cotizacion_servicios cs');
        $builder->select('s.id, s.nombre, s.precio_base, s.tipo_cobro');
        $builder->join('servicios s', 's.id = cs.servicio_id');
        $builder->where('cs.cotizacion_id', $id);
        $cotizacion['servicios_seleccionados'] = $builder->get()->getResultArray();

        return $this->respond($cotizacion);
    }

    /**
     * Crea una nueva cotización para un invitado.
     * ACCESO: Público. Devuelve un Guest Token para futuras ediciones.
     */
    public function create()
    {
        $json = $this->request->getJSON(true);
        if (empty($json)) {
            return $this->fail('No se recibió un payload JSON válido.', 400);
        }

        // Validación de los datos de entrada
        $validation = Services::validation();
        $validation->setRules([
            'nombre_completo'     => 'required|min_length[3]',
            'whatsapp'            => 'required|min_length[10]',
            'fecha_evento'        => 'required|valid_date',
            'cantidad_invitados'  => 'required|is_natural_no_zero',
            'servicios'           => 'required|is_array'
        ]);

        if (!$validation->run($json)) {
            return $this->fail($validation->getErrors(), 400);
        }

        // Generamos un token seguro y único para este invitado
        $guestToken = bin2hex(random_bytes(32));
        $json['guest_token'] = $guestToken;

        // Usamos el Trait para procesar y guardar todo
        $resultado = $this->_procesarYGuardarCotizacion($json);

        if ($resultado['success']) {
            // Devolvemos el ID y el Guest Token a la app móvil
            return $this->respondCreated([
                'status' => 'success',
                'message' => $resultado['message'],
                'cotizacion_id' => $resultado['id'],
                'guest_token' => $guestToken
            ]);
        } else {
            return $this->failServerError($resultado['message']);
        }
    }

    /**
     * Actualiza una cotización existente.
     * ACCESO: Admins (con JWT) o Invitados (con Guest Token válido).
     */
    public function update($id = null)
    {
        $cotizacion = $this->model->find($id);
        if (!$cotizacion) {
            return $this->failNotFound('No se encontró la cotización con ID: ' . $id);
        }

        // --- LÓGICA DE AUTORIZACIÓN ---
        $isAdmin = auth()->id();

        if (!$isAdmin) {
            // RUTA DEL INVITADO
            if (empty($cotizacion['guest_token'])) {
                return $this->failUnauthorized('Se requiere autenticación para modificar este recurso.');
            }
            $tokenEnviado = $this->request->getHeaderLine('X-Guest-Token');
            if ($tokenEnviado !== $cotizacion['guest_token']) {
                return $this->failForbidden('Acceso no autorizado para modificar esta cotización.');
            }
        }
        // Si es Admin, puede continuar.

        // --- LÓGICA DE ACTUALIZACIÓN (si la autorización pasó) ---
        $json = $this->request->getJSON(true);
        if (empty($json)) {
            return $this->fail('No se recibió un payload JSON válido.', 400);
        }

        $datosCotizacion = $this->_prepararDatosCotizacion($json);
        
        // Un admin puede cambiar el status, un invitado no debería.
        if (isset($json['status']) && $isAdmin) {
            $datosCotizacion['status'] = $json['status'];
        }

        $cotizacionServiciosModel = new \App\Models\CotizacionServiciosModel();
        $serviciosSeleccionadosIds = $json['servicios'] ?? [];
        $db = db_connect();

        $db->transStart();
        $this->model->update($id, $datosCotizacion);
        $cotizacionServiciosModel->where('cotizacion_id', $id)->delete();
        if (!empty($serviciosSeleccionadosIds)) {
            $nuevosServicios = [];
            foreach ($serviciosSeleccionadosIds as $servicioId) {
                $nuevosServicios[] = ['cotizacion_id' => $id, 'servicio_id' => $servicioId];
            }
            $cotizacionServiciosModel->insertBatch($nuevosServicios);
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Hubo un error al guardar los cambios.');
        }

        return $this->respondUpdated(['status' => 'success', 'message' => 'Cotización actualizada correctamente.']);
    }

    /**
     * Elimina una cotización.
     * ACCESO: Solo para administradores autenticados.
     */
    public function delete($id = null)
    {
        // Aunque la ruta está protegida, una doble verificación nunca está de más.
        if (!auth()->id()) {
            return $this->failUnauthorized('Solo los administradores pueden eliminar cotizaciones.');
        }

        $cotizacion = $this->model->find($id);
        if (!$cotizacion) {
            return $this->failNotFound('No se encontró la cotización con ID: ' . $id);
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted(['status' => 'success', 'message' => 'Cotización eliminada correctamente.']);
        } else {
            return $this->failServerError('No se pudo eliminar la cotización.');
        }
    }

    /**
     * Devuelve las fechas confirmadas.
     * ACCESO: Público.
     */
    public function fechasOcupadas()
    {
        $fechasDb = $this->model->select('fecha_evento')
                                ->where('status', 'Confirmado')
                                ->findAll();
        $fechasOcupadas = array_column($fechasDb, 'fecha_evento');

        return $this->respond($fechasOcupadas);
    }
}