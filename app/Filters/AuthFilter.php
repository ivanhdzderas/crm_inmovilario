<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        // El usuario debe seguir existiendo y activo: desactivar o borrar una cuenta la saca de inmediato.
        // El rol se toma de la base (si un admin cambia el rol, aplica sin volver a entrar).
        $user = db_connect()->table('users')
            ->select('role_id, is_active, full_name')
            ->where('id', (int) $session->get('user_id'))
            ->get()->getRowArray();

        if (! $user || (int) $user['is_active'] !== 1) {
            $session->destroy();

            return redirect()->to(base_url('login'))->with('error', 'Tu sesión terminó porque la cuenta fue desactivada.');
        }

        if ((int) $session->get('role_id') !== (int) $user['role_id']) {
            $session->set('role_id', (int) $user['role_id']);
        }

        if (is_array($arguments) && in_array('admin', $arguments, true) && (int) $user['role_id'] !== 1) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado. No tienes permisos de administrador.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
