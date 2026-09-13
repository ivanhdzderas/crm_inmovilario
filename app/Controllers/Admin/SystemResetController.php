<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\OperationalDataResetService;

class SystemResetController extends BaseController
{
    private function expectedToken(): ?string
    {
        $token = trim((string) env('SYSTEM_RESET_TOKEN', ''));

        return $token !== '' ? $token : null;
    }

    private function assertValidToken(string $token): void
    {
        $expected = $this->expectedToken();

        if ($expected === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Reinicio no configurado. Define SYSTEM_RESET_TOKEN en el archivo .env'
            );
        }

        if (! hash_equals($expected, $token)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function index(string $token): string
    {
        $this->assertValidToken($token);

        $service = new OperationalDataResetService();

        return view('admin/system_reset/index', [
            'token'         => $token,
            'targetTables'  => $service->getTargetTables(),
            'preservedNote' => $this->getPreservedItems(),
        ]);
    }

    public function execute(string $token)
    {
        $this->assertValidToken($token);

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to(site_url('admin/mantenimiento/reinicio/' . $token));
        }

        $confirmation = strtoupper(trim((string) $this->request->getPost('confirmation')));

        if ($confirmation !== 'REINICIAR DATOS') {
            return redirect()->back()->withInput()->with('error', 'Debes escribir exactamente: REINICIAR DATOS');
        }

        if (! $this->request->getPost('acknowledge')) {
            return redirect()->back()->withInput()->with('error', 'Debes confirmar que entiendes que esta acción es irreversible.');
        }

        try {
            $result = (new OperationalDataResetService())->execute();
            cache()->clean();

            log_message('warning', '[SystemReset] Reinicio operativo ejecutado por user_id=' . session()->get('user_id'));

            return redirect()->to(site_url('admin/mantenimiento/reinicio/' . $token))
                ->with('success', 'Reinicio completado.')
                ->with('reset_summary', $result);
        } catch (\Throwable $e) {
            log_message('error', '[SystemReset] Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error al reiniciar: ' . $e->getMessage());
        }
    }

    /** @return list<string> */
    private function getPreservedItems(): array
    {
        return [
            'Usuarios y roles',
            'Zonas, tipos de propiedad, configuración general',
            'Catálogos: tipos de documento, trámites, checklist por crédito, motivos de cancelación, orígenes de cliente',
        ];
    }
}
