<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Si el usuario no ha iniciado sesion
        if (! session()->isLoggedIn) {
            return redirect()->to(base_url('login'));
        }

        // Si el usuario no tiene el rol de admin, redirigir
        if (is_array($arguments) && in_array('admin', $arguments) && session()->get('role_id') != 1) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado. No tienes permisos de administrador.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}