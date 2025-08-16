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
            'min_personas' => 'permit_empty|numeric',
            'imagen' => [ // Reglas para la imagen
                'label' => 'Imagen',
                'rules' => 'permit_empty|is_image[imagen]|mime_in[imagen,image/jpg,image/jpeg,image/png]|max_size[imagen,2048]',
            ]
        ];
        if (!$this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- LÓGICA PARA MANEJAR LA IMAGEN ---
        $img = $this->request->getFile('imagen');
        $rutaImagen = null;

        if ($img && $img->isValid() && !$img->hasMoved()) {
            $nuevoNombre = $img->getRandomName(); // Genera un nombre seguro y aleatorio
            $img->move(FCPATH . 'uploads/servicios', $nuevoNombre); // Mueve el archivo
            $rutaImagen = 'uploads/servicios/' . $nuevoNombre; // Esta es la ruta que guardaremos
        }

        // Preparamos los datos para la inserción
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'precio_base' => $this->request->getPost('precio_base'),
            'min_personas' => !empty($this->request->getPost('min_personas')) ? $this->request->getPost('min_personas') : null,
            'tipo_cobro' => !empty($this->request->getPost('tipo_cobro')) ? $this->request->getPost('tipo_cobro') : null,
            'descripcion' => !empty($this->request->getPost('descripcion')) ? $this->request->getPost('descripcion') : null,
            'imagen_url' => $rutaImagen,
        ]; 

        $servicioModel = new ServicioModel();
        $servicioModel->save($data);

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

        // Validación
        $reglas = [
            'nombre' => 'required|min_length[3]',
            'precio_base' => 'required|numeric',
            'min_personas' => 'permit_empty|numeric',
            'imagen' => [
                'label' => 'Imagen',
                'rules' => 'permit_empty|is_image[imagen]|mime_in[imagen,image/jpg,image/jpeg,image/png]|max_size[imagen,2048]',
            ]
        ];
        if (!$this->validate($reglas)) {
            return redirect()->to('admin/servicios/editar/'.$id)->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $servicioModel = new ServicioModel();
        $servicioActual = $servicioModel->find($id);

        // --- LÓGICA PARA MANEJAR LA IMAGEN ---
        $img = $this->request->getFile('imagen');
        $rutaImagen = $servicioActual['imagen_url']; // Por defecto, mantenemos la imagen antigua

        if ($img && $img->isValid() && !$img->hasMoved()) {
            // Si hay una imagen antigua, la borramos para no acumular basura
            if ($servicioActual['imagen_url'] && file_exists(FCPATH . $servicioActual['imagen_url'])) {
                unlink(FCPATH . $servicioActual['imagen_url']);
            }

            $nuevoNombre = $img->getRandomName();
            $img->move(FCPATH . 'uploads/servicios', $nuevoNombre);
            $rutaImagen = 'uploads/servicios/' . $nuevoNombre; // Actualizamos a la nueva ruta
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'precio_base' => $this->request->getPost('precio_base'),
            'min_personas' => !empty($this->request->getPost('min_personas')) ? $this->request->getPost('min_personas') : null,
            'tipo_cobro' => !empty($this->request->getPost('tipo_cobro')) ? $this->request->getPost('tipo_cobro') : null,
            'descripcion' => !empty($this->request->getPost('descripcion')) ? $this->request->getPost('descripcion') : null,
            'imagen_url' => $rutaImagen, // Guardamos la ruta nueva o la antigua
        ];

        $servicioModel->update($id, $data);

        return redirect()->to(site_url('admin/servicios'))->with('mensaje', 'Servicio actualizado correctamente.');
    }
}