<?php 
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ServicioModel;

class Servicios extends BaseController
{
    public function index()
    {
        $servicioModel = new ServicioModel();
        $data['servicios'] = $servicioModel->findAll();
        $data['titulo'] = 'Gestión de Servicios';

        return view('admin/servicios/listado_view', $data);
    }

    public function crear()
    {
        $data['titulo'] = 'Añadir Nuevo Servicio';
        return view('admin/servicios/form_view', $data);
    }

    public function guardar()
    {
        // Validación (la puedes hacer más robusta si quieres)
        $reglas = [
            'nombre' => 'required|min_length[3]',
            'precio_base' => 'required|numeric',
        ];
        if (!$this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $servicioModel = new ServicioModel();
        $servicioModel->save([
            'nombre' => $this->request->getPost('nombre'),
            'precio_base' => $this->request->getPost('precio_base'),
            'min_personas' => $this->request->getPost('min_personas'),
            'tipo_cobro' => $this->request->getPost('tipo_cobro'),
        ]);

        return redirect()->to(site_url('admin/servicios'))->with('mensaje', 'Servicio guardado correctamente.');
    }

    public function eliminar()
    {
        // if ($this->request->getMethod() !== 'post') {
        //     return redirect()->to(site_url('admin/servicios'));
        // }

        $id = $this->request->getPost('id');
        if (empty($id)) {
            return redirect()->back()->with('error', 'ID de servicio no válido.');
        }

        $servicioModel = new ServicioModel();
        $servicioModel->delete($id);

        return redirect()->to(site_url('admin/servicios'))->with('mensaje', 'Servicio eliminado correctamente.');
    }

    public function editar($id = null)
    {
        $servicioModel = new ServicioModel();
        $data['servicio'] = $servicioModel->find($id);

        if (empty($data['servicio'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Servicio no encontrado.');
        }

        $data['titulo'] = 'Editar Servicio: ' . esc($data['servicio']['nombre']);
        
        // ¡Reutilizamos la misma vista del formulario!
        return view('admin/servicios/form_view', $data);
    }

    public function actualizar()
    {
        // if ($this->request->getMethod() !== 'post') {
        //     return redirect()->to(site_url('admin/servicios'));
        // }

        $id = $this->request->getPost('id');
        if (empty($id)) {
            return redirect()->back()->with('error', 'ID de servicio no válido.');
        }

        $servicioModel = new ServicioModel();
        $servicioModel->update($id, [
            'nombre' => $this->request->getPost('nombre'),
            'precio_base' => $this->request->getPost('precio_base'),
            'min_personas' => $this->request->getPost('min_personas'),
            'tipo_cobro' => $this->request->getPost('tipo_cobro'),
        ]);

        return redirect()->to(site_url('admin/servicios'))->with('mensaje', 'Servicio actualizado correctamente.');
    }
}