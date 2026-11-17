<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    /** Rol con el que se registran los usuarios nuevos (2 = asesor). El 1 es administrador. */
    private const ROL_REGISTRO = 2;

    private const MINUTOS_TOKEN = 60;

    public function register()
    {
        helper(['form']);

        echo view('register_view', []);
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

    /**
     * Paso 1: el usuario escribe su correo. Si existe y está activo, se le envía un enlace
     * de un solo uso. La respuesta es la misma exista o no (no revela qué correos hay).
     */
    public function processForgotPassword()
    {
        helper(['form']);

        if (! $this->validate(['email' => 'required|valid_email'])) {
            return view('forgot_password_view', ['validation' => $this->validator]);
        }

        if (service('throttler')->check('reset-' . md5((string) $this->request->getIPAddress()), 5, MINUTE) === false) {
            return redirect()->back()->with('error', 'Demasiados intentos. Espera un minuto.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $user  = (new UserModel())->where('email', $email)->where('is_active', 1)->first();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db    = db_connect();
            $db->table('password_resets')->where('user_id', $user['id'])->where('used_at', null)->delete();
            $db->table('password_resets')->insert([
                'user_id'    => $user['id'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::MINUTOS_TOKEN . ' minutes')),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->enviarCorreoRestablecer($user, site_url('restablecer/' . $token));
        }

        return redirect()->to('/login')->with('success', 'Si el correo está registrado, te enviamos un enlace para crear una nueva contraseña. Revisa tu bandeja (y la carpeta de spam).');
    }

    /** Paso 2: formulario de nueva contraseña (desde el enlace del correo). */
    public function resetForm(string $token)
    {
        helper(['form']);

        if (! $this->tokenValido($token)) {
            return redirect()->to('/forgot-password')->with('error', 'El enlace no es válido o ya expiró. Solicita uno nuevo.');
        }

        return view('reset_password_view', ['token' => $token]);
    }

    public function resetPassword(string $token)
    {
        helper(['form']);

        $reset = $this->tokenValido($token);
        if (! $reset) {
            return redirect()->to('/forgot-password')->with('error', 'El enlace no es válido o ya expiró. Solicita uno nuevo.');
        }

        $rules = [
            'password'         => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];
        if (! $this->validate($rules)) {
            return view('reset_password_view', ['token' => $token, 'validation' => $this->validator]);
        }

        (new UserModel())->update($reset['user_id'], [
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);
        db_connect()->table('password_resets')->where('id', $reset['id'])->update(['used_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/login')->with('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
    }

    public function store()
    {
        helper(['form']);

        $rules = [
            'full_name'        => 'required|min_length[3]|max_length[255]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return view('register_view', ['validation' => $this->validator]);
        }

        // La cuenta queda como asesor y pendiente hasta que un administrador la active
        (new UserModel())->insert([
            'full_name'     => trim((string) $this->request->getPost('full_name')),
            'email'         => strtolower(trim((string) $this->request->getPost('email'))),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'       => self::ROL_REGISTRO,
            'is_active'     => 0,
        ]);

        return redirect()->to('/login')->with('success', 'Registro recibido. Un administrador debe activar tu cuenta antes de que puedas entrar.');
    }

    public function authenticate()
    {
        helper(['form']);

        if (! $this->validate(['email' => 'required|valid_email', 'password' => 'required'])) {
            return view('login_view', ['validation' => $this->validator]);
        }

        if (service('throttler')->check('login-' . md5((string) $this->request->getIPAddress()), 10, MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $user  = (new UserModel())->where('email', $email)->first();

        // Mismo mensaje si el correo no existe o la contraseña es incorrecta
        if (! $user || ! password_verify((string) $this->request->getPost('password'), (string) $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        if ((int) $user['is_active'] !== 1) {
            return redirect()->back()->withInput()->with('error', 'Tu cuenta está pendiente de activación o fue desactivada. Contacta al administrador.');
        }

        session()->regenerate(true);
        session()->set([
            'user_id'    => $user['id'],
            'isLoggedIn' => true,
            'role_id'    => $user['role_id'],
            'full_name'  => $user['full_name'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(base_url('login'))->with('success', 'Sesión cerrada correctamente.');
    }

    // ------------------------------------------------------------------

    /** Devuelve la fila del token si existe, no se usó y no expiró. */
    private function tokenValido(string $token): ?array
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        return db_connect()->table('password_resets')
            ->where('token_hash', hash('sha256', $token))
            ->where('used_at', null)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray() ?: null;
    }

    private function enviarCorreoRestablecer(array $user, string $enlace): void
    {
        try {
            $email  = service('email');
            $config = config('Email');
            $desde  = $config->fromEmail ?: ('no-responder@' . (parse_url(base_url(), PHP_URL_HOST) ?: 'localhost'));
            $email->setFrom($desde, $config->fromName ?: 'Pacific Inmobiliaria');
            $email->setTo($user['email']);
            $email->setSubject('Restablecer tu contraseña');
            $email->setMessage(
                '<p>Hola ' . esc($user['full_name']) . ',</p>'
                . '<p>Recibimos una solicitud para restablecer tu contraseña. Usa este enlace (válido por ' . self::MINUTOS_TOKEN . ' minutos):</p>'
                . '<p><a href="' . esc($enlace, 'attr') . '">' . esc($enlace) . '</a></p>'
                . '<p>Si no fuiste tú, ignora este correo; tu contraseña no cambiará.</p>'
            );
            if (! $email->send(false)) {
                log_message('error', '[Restablecer] No se pudo enviar el correo a user_id=' . $user['id'] . '. Revisa la configuración de Email.');
            }
        } catch (\Throwable $e) {
            log_message('error', '[Restablecer] ' . $e->getMessage());
        }
    }
}
