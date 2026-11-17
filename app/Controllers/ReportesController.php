<?php

namespace App\Controllers;

use App\Libraries\Matching;
use App\Libraries\Tramite;

/** Reporte de seguimiento de un prospecto (imprimible / guardar como PDF). */
class ReportesController extends BaseController
{
    public function prospecto(int $id): string
    {
        $db = db_connect();
        $c = $db->table('clients c')
            ->select('c.*, z.name AS zone_name, u.full_name AS asesor_name, u.phone AS asesor_phone, s.name AS origen, cr.name AS motivo_baja')
            ->join('zones z', 'z.id = c.zone_id', 'left')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->join('acquisition_sources s', 's.id = c.acquisition_source_id', 'left')
            ->join('cancellation_reasons cr', 'cr.id = c.deactivation_reason_id', 'left')
            ->where('c.id', $id)->get()->getRowArray();

        if (! $c || ((int) session()->get('role_id') !== 1 && (int) $c['user_id'] !== (int) session()->get('user_id'))) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $contactos = $db->table('client_follow_ups f')
            ->select('f.contact_datetime, f.contact_method, f.discussion_detail, u.full_name AS usuario')
            ->join('users u', 'u.id = COALESCE(f.user_id, f.advisor_id)', 'left', false)
            ->where('f.client_id', $id)->orderBy('f.contact_datetime', 'DESC')->get()->getResultArray();

        $intereses = $db->table('client_real_estate_interests i')
            ->select('i.id, i.status, i.interest_level, i.notes, i.created_at, i.apartado_date, i.cancellation_notes, re.name, re.clave, re.price, re.status AS inmueble_status')
            ->join('real_estates re', 're.id = i.real_estate_id', 'left')
            ->where('i.client_id', $id)->orderBy('i.created_at', 'DESC')->get()->getResultArray();

        $funerarios = $db->table('client_funeral_interests i')
            ->select('i.status, i.interest_level, i.notes, i.created_at, p.name, p.clave, p.base_price')
            ->join('funeral_products p', 'p.id = i.funeral_product_id', 'left')
            ->where('i.client_id', $id)->orderBy('i.created_at', 'DESC')->get()->getResultArray();

        $actividades = $db->table('calendar_activities')
            ->select('title, type, status, start_time, description')
            ->where('client_id', $id)->orderBy('start_time', 'DESC')->limit(30)->get()->getResultArray();

        $ultimo = $contactos[0]['contact_datetime'] ?? null;
        $proxima = null;
        foreach ($actividades as $a) {
            if ($a['start_time'] >= date('Y-m-d H:i:s') && ! in_array(strtolower((string) $a['status']), ['cancelada', 'cancelado', 'completada', 'completado'], true)) {
                $proxima = $a; // la lista viene descendente: queda la más cercana
            }
        }

        $m = new Matching();
        $sugerencias = [];
        if (! in_array($c['status'], ['Cierre', 'Descartado', 'Cancelado'], true) && ($c['client_type'] ?? 'comprador') !== 'vendedor') {
            $sugerencias = array_slice($m->paraCliente($c, $m->inmueblesDisponibles()), 0, 3);
        }

        return view('reportes/prospecto', [
            'c'           => $c,
            'formaPago'   => $m->formaPagoCliente($c),
            'contactos'   => $contactos,
            'intereses'   => $intereses,
            'funerarios'  => $funerarios,
            'tramites'    => Tramite::lista(null, true, $id),
            'actividades' => $actividades,
            'proxima'     => $proxima,
            'sugerencias' => $sugerencias,
            'diasAlta'    => (int) floor((time() - strtotime((string) $c['created_at'])) / 86400),
            'diasSinContacto' => $ultimo ? (int) floor((time() - strtotime($ultimo)) / 86400) : null,
        ]);
    }
}
