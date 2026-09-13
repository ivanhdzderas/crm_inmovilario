<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    public function register()
    {
        // Cargar el helper de formulario para usar las funciones de formulario de CI4
        helper(['form']);

        $data = [];
        echo view('register_view', $data);
    }
    public function login()
    {
        helper(['form']);

        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        echo view('login_view');
    }

    public function forgotPassword()
    {
        helper(['form']);

        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        echo view('forgot_password_view');
    }

    public function processForgotPassword()
    {
        helper(['form']);

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            $data['validation'] = $this->validator;
            return view('forgot_password_view', $data);
        }

        $userModel = new UserModel();
        $email = trim((string) $this->request->getPost('email'));
        $newPassword = (string) $this->request->getPost('password');

        $user = $userModel->where('email', $email)->first();

        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Correo electrónico no registrado.');
        }

        $userModel->update($user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/login')->with('success', 'Contraseña actualizada correctamente. Inicia sesión.');
    }
    public function store()
    {
        // Cargar el helper de formulario y validación
        helper(['form']);

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if ($this->validate($rules)) {
            $userModel = new UserModel();

            $data = [
                'full_name' => $this->request->getVar('full_name'),
                'email' => $this->request->getVar('email'),
                'password_hash' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'role_id' => 1, // Asignar el rol de "asesor" por defecto
                'is_active' => 1,
            ];

            $userModel->insert($data);

            // Redirigir a la página de inicio de sesión con un mensaje de éxito
            return redirect()->to('/login')->with('success', 'Registro exitoso. ¡Inicia sesión ahora!');
        } else {
            // Si la validación falla, regresar al formulario con los errores
            $data['validation'] = $this->validator;
            return view('register_view', $data);
        }
    }
    public function authenticate()
    {
        helper(['form']);

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if ($this->validate($rules)) {
            $userModel = new \App\Models\UserModel();
            $email = $this->request->getVar('email');
            $password = $this->request->getVar('password');

            $user = $userModel->where('email', $email)->first();

            if ($user) {
                if (password_verify($password, $user['password_hash'])) {
                    // La contraseña es correcta
                    $session = session();
                    $session->set([
                        'user_id' => $user['id'],
                        'isLoggedIn' => true,
                        'role_id' => $user['role_id'],
                        'full_name' => $user['full_name'],
                    ]);

                    // Redirigir a la página principal o a un dashboard
                    return redirect()->to('/dashboard');
                } else {
                    // Contraseña incorrecta
                    return redirect()->back()->with('error', 'Contraseña incorrecta.');
                }
            } else {
                // El usuario no existe
                return redirect()->back()->with('error', 'Correo electrónico no registrado.');
            }
        } else {
            // Si la validación falla
            $data['validation'] = $this->validator;
            return view('login_view', $data);
        }
    }
    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Sesión cerrada correctamente.');
    }
}