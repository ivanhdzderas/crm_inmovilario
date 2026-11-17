<?php

namespace App\Controllers;

use App\Libraries\Tramite;

/** Seguimiento del trámite de compraventa (desde el apartado hasta la entrega). */
class TramitesController extends BaseController
{
    public function index(): string
    {
        $esAdmin = (int) session()->get('role_id') === 1;
        $asesor  = $esAdmin ? ((int) $this->request->getGet('asesor') ?: null) : (int) session()->get('user_id');
        $concluidos = (bool) $this->request->getGet('concluidos');

        $filas = Tramite::lista($asesor, $concluidos);
        $porEtapa = array_fill_keys(array_keys(Tramite::ETAPAS), 0);
        foreach ($filas as $f) {
            $porEtapa[$f['tramite_etapa']]++;
        }

        return view('tramites/index', [
            'filas'      => $filas,
            'porEtapa'   => $porEtapa,
            'esAdmin'    => $esAdmin,
            'asesores'   => $esAdmin ? db_connect()->table('users')->select('id, full_name')->where('is_active', 1)->orderBy('full_name')->get()->getResultArray() : [],
            'asesorSel'  => $asesor,
            'concluidos' => $concluidos,
        ]);
    }

    public function detalle(int $id): string
    {
        $t = $this->cargar($id);

        $docs = $t['cuenta_id'] ? db_connect()->table('client_document_checklist d')
            ->select('d.status, d.due_date, d.completed_date, d.notes, cat.name AS document_name, cat.process_stage')
            ->join('client_document_catalog cat', 'cat.id = d.client_document_catalog_id', 'left')
            ->where('d.client_account_id', $t['cuenta_id'])
            ->orderBy('d.id')->get()->getResultArray() : [];

        return view('tramites/detalle', [
            't'         => $t,
            'docs'      => $docs,
            'historial' => Tramite::historial($id),
        ]);
    }

    public function etapa(int $id)
    {
        $t = $this->cargar($id);

        if ($t['tramite_etapa'] === 'entregado') {
            return redirect()->to(site_url('tramites/' . $id))->with('error', 'Este trámite ya está concluido.');
        }

        $etapa = (string) $this->request->getPost('etapa');
        $nota  = trim((string) $this->request->getPost('nota'));
        if (! array_key_exists($etapa, Tramite::ETAPAS)) {
            return redirect()->to(site_url('tramites/' . $id))->with('error', 'Etapa no válida.');
        }
        if ($etapa === $t['tramite_etapa'] && $nota === '') {
            return redirect()->to(site_url('tramites/' . $id))->with('error', 'Elige otra etapa o escribe una nota.');
        }

        $db    = db_connect();
        $ahora = date('Y-m-d H:i:s');
        $db->transStart();

        $db->table('client_real_estate_interests')->where('id', $id)->update([
            'tramite_etapa' => $etapa, 'tramite_actualizado' => $etapa !== $t['tramite_etapa'] ? $ahora : $t['tramite_actualizado'], 'updated_at' => $ahora,
        ]);
        $db->table('tramite_historial')->insert([
            'interest_id' => $id, 'etapa' => $etapa, 'nota' => mb_substr($nota, 0, 2000) ?: null,
            'user_id' => (int) session()->get('user_id') ?: null, 'created_at' => $ahora,
        ]);

        // Entrega = venta concluida (alimenta pipeline y estadísticas existentes)
        if ($etapa === 'entregado') {
            $db->table('client_real_estate_interests')->where('id', $id)->update(['status' => 'Vendido']);
            $db->table('real_estates')->where('id', $t['real_estate_id'])->update(['status' => 'Vendido', 'sold_to_client_id' => $t['client_id'], 'updated_at' => $ahora]);
            $db->table('clients')->where('id', $t['client_id'])->update(['status' => 'Cierre', 'updated_at' => $ahora]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('tramites/' . $id))->with('error', 'No se pudo guardar el cambio.');
        }

        return redirect()->to(site_url('tramites/' . $id))->with('success', $etapa === 'entregado'
            ? '¡Venta concluida! El inmueble quedó como vendido.'
            : 'Trámite actualizado: ' . Tramite::nombre($etapa) . '.');
    }

    private function cargar(int $id): array
    {
        $t = Tramite::lista(null, true, null, $id)[0] ?? null;
        if (! $t || ((int) session()->get('role_id') !== 1 && (int) $t['user_id'] !== (int) session()->get('user_id'))) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $t;
    }
}
