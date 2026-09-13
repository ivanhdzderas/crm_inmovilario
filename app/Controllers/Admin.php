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

        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'role_id' => $this->request->getPost('role_id'),
        ];

        // Validar si la contraseña es proporcionada y actualizarla
        if ($this->request->getPost('password')) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);
        return redirect()->to('admin/users')->with('success', 'Usuario actualizado correctamente.');
    }

    public function delete($id)
    {
        $userModel = new UserModel();
        $userModel->delete($id);
        return redirect()->to('admin/users')->with('success', 'Usuario eliminado correctamente.');
    }
}