<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Admin extends Controller
{
    public function users()
    {
        $userModel = new UserModel();
        $data['users'] = $userModel->findAll();
        echo view('admin/users', $data);
    }

    public function edit($id)
    {
        // Cargar el helper de formulario
        helper(['form']);

        $userModel = new UserModel();
        $data['user'] = $userModel->find($id);

        if (!$data['user']) {
            return redirect()->to('admin/users')->with('error', 'Usuario no encontrado.');
        }

        echo view('admin/edit_user', $data);
    }

    public function update()
    {
        $userModel = new UserModel();
        $id = $this->request->getPost('id');

        $rolId = (int) $this->request->getPost('role_id');
        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'role_id' => in_array($rolId, [1, 2], true) ? $rolId : 2,
            'is_active' => $this->request->getPost('is_active') === '0' ? 0 : 1,
        ];

        // Evita que el administrador se bloquee a sí mismo
        if ((int) $id === (int) session()->get('user_id')) {
            $data['is_active'] = 1;
            $data['role_id'] = 1;
        }

        // Validar si la contraseña es proporcionada y actualizarla
        if ($this->request->getPost('password')) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);
        return redirect()->to('admin/users')->with('success', 'Usuario actualizado correctamente.');
    }

    public function delete($id)
    {
        if ((int) $id === (int) session()->get('user_id')) {
            return redirect()->to('admin/users')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $userModel = new UserModel();
        $userModel->delete($id);
        return redirect()->to('admin/users')->with('success', 'Usuario eliminado correctamente.');
    }
}